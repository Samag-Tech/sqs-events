<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;

/**
 * Classe per la cancellazione dei messaggi da una coda SQS
 */
final class Cleaner
{
    use ClientSQS;

    /**
     * Indirizzi code sqs
     */
    private array $queueUrls = [];

    /**
     * Credenziali utente SQS
     */
    private array $credentials = [];

    /**
     * Inizializzazione del client SQS
     *
     * @param array $credentials = credenziali per accedere alla coda sqs
     * @param array|string $queueUrls = url della coda sqs da cui eliminare i messaggi
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
     * Cancellazione dei messaggi dalla coda
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->queueUrls as $key => $queue) {
            $this->clientInit($this->credentials, $queue);
            $this->clean();
        }
    }

    // //----------------------------------------------------------------------

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
                    throw $e;
                }
            }
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Ritorna il numero di messaggi presenti nella coda
     */
    private function getCountMessages(): int
    {
        return $this->client->getQueueAttributes([
            'QueueUrl' => $this->queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages']
        ])["Attributes"]["ApproximateNumberOfMessages"];
    }
}
