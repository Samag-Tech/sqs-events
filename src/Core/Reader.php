<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;
use SamagTech\SqsEvents\Traits\Response;

final class Reader
{
    use ClientSQS;
    use Response;

    /**
     * Nome dell'evento nella coda
     */
    private string $action;

    /**
     * Handler eventi
     */
    private Handler $handler;

    /**
     * Inizializzazione Reader
     *
     * @param array $events = Lista di job lanciabili
     * @param array $credentials = Credenziali client SQS
     * @param string $queueUrl = Url coda SQS
     * @param ?array $syncEvents = Lista di job lanciabili per la sincronizzazione
     */
    public function __construct(array $events, array $credentials, string $queueUrl, ?array $syncEvents = null)
    {
        // Inizializzo il client SQS
        $this->clientInit($credentials, $queueUrl);
        // Inizializzo l'handler dei messaggi
        $this->handler = (new Handler($events, $syncEvents));
    }

    // //----------------------------------------------------------------------

    /**
     * Lettura ed esecuzione dei messaggi dalla coda
     * @return array
     */
    public function run(): array
    {
        try {
            // Recupero il messaggio dalla coda
            $result = $this->client->receiveMessage($this->SQS);
            if (!empty($result->get('Messages'))) {
                $this->action = $result->get('Messages')[0]['MessageAttributes']['request']['StringValue'];
                // Esecuzione del messaggio
                $res = $this->handler->execute(
                    action: $this->action,
                    message: json_decode($result->get('Messages')[0]['Body'], true),
                );
                // Eliminazione del messaggio dalla coda
                $this->client->deleteMessage([
                    'QueueUrl' => $this->queueUrl,
                    'ReceiptHandle' => $result->get('Messages')[0]['ReceiptHandle']
                ]);
                // Ritorno il risultato dell'esecuzione del messaggio
                return $res;
            }
        } catch (AwsException $e) {
            return $this->respond($e->getTrace(), "exception", 400);
        }
    }
}
