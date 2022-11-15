<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;
use SamagTech\SqsEvents\Exceptions\CleanerException;

/**
 * Classe per la cancellazione dei messaggi da una coda SQS
 */
final class Cleaner
{
    use ClientSQS;

    /**
     * Indirizzi code sqs
     *
     * @var array
     *
     * @access private
     */
    private array $queueUrls = [];

    /**
     * Credenziali utente SQS
     *
     * @var array
     *
     * @access private
     */
    private array $credentials = [];

    /**
     * Inizializzazione del client SQS
     *
     * @param array $credentials            Credenziali per accedere alla coda sqs
     * @param array|string $queueUrls       Url della coda sqs da cui eliminare i messaggi
     *
     * @access public
     */
    public function __construct(array $credentials, array|string $queueUrls)
    {
        if (is_string($queueUrls)) {
            $queueUrls = [$queueUrls];
        }
        $this->queueUrls = $queueUrls;
        $this->credentials = $credentials;
    }

    // //----------------------------------------------------------------------

    /**
     * Richiama la funzione di cancellazione per ogni
     * coda inviata al costruttore
     *
     * @return void
     *
     * @access public
     */
    public function run(): void
    {
        foreach ($this->queueUrls as $key => $queue) {
            $this->clientInit($this->credentials, $queue);
            $this->clean();
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Pulisce la coda da tutti i messaggi che contiene
     *
     * @return void
     *
     * @access private
     */
    private function clean(): void
    {
        for ($i = 1; $i <= $this->getCountMessages(); $i++) {
            $receiptHandle = $this->client->receiveMessage($this->SQS)->get("Messages")[0]['ReceiptHandle'] ?? null;

            if (!is_null($receiptHandle)) {

                try {

                    $this->client->deleteMessage([
                        'QueueUrl' => $this->queueUrl,
                        'ReceiptHandle' => $receiptHandle
                    ]);

                } catch (AwsException $e) {
                    throw new CleanerException($e->getMessage(),$e->getStatusCode());
                }
            }
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Ritorna il numero di messaggi presenti nella coda
     *
     * @return int
     *
     * @access private
     */
    private function getCountMessages(): int
    {
        return $this->client->getQueueAttributes([
            'QueueUrl' => $this->queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages']
        ])["Attributes"]["ApproximateNumberOfMessages"];
    }
}
