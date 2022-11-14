<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;

final class Reader
{
    use ClientSQS;

    protected $message;

    private string $action;

    private Handler $handler;

    public function __construct(array $events, array $credentials, string $queueUrl)
    {
        // Inizializzo il client SQS
        $this->clientInit($credentials, $queueUrl);

        // Inizializzo l'handler dei messaggi
        $this->handler = (new Handler($events));
    }

    // //----------------------------------------------------------------------

    /**
     * Lettura ed esecuzione dei messaggi sulla coda
     *
     * @return void
     */
    public function run(): bool|array
    {
        try {
            // Recupero il messaggio dalla coda
            $result = $this->client->receiveMessage($this->recieve);

            if (!empty($result->get('Messages'))) {

                $this->action = $result->get('Messages')[0]['MessageAttributes']['request']['StringValue'];

                // Esecuzione del messaggio
                $res = $this->handler->execute(
                    action: $this->action,
                    message: json_decode($result->get('Messages')[0]['Body'], true)
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
            return $e->getTrace();
        }
    }
}
