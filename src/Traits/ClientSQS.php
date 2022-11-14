<?php

namespace SamagTech\SqsEvents\Traits;

use Aws\Sqs\SqsClient;

/**
 * Inizializza il client SQS
 */
trait ClientSQS
{
    protected ?SqsClient $client;

    /**
     * URL della coda
     */
    protected string $queueUrl = "";

    /**
     * array contenente la configurazione della coda
     */
    protected array $SQS = [];


    // //----------------------------------------------------------------------

    /**
     *
     */
    protected function clientInit(array $credentials, string $queueUrl): void
    {
        $this->client = new SqsClient($credentials);
        $this->setQueue($queueUrl);
        $this->SQSinit();
    }

    // //----------------------------------------------------------------------

    /**
     * Configurazione coda sqs
     */
    protected function SQSinit(?string $queueUrl = null): void
    {
        $this->SQS = [
            'AttributeNames'        => ['SentTimestamp'],
            'MaxNumberOfMessages'   => 1,
            'MessageAttributeNames' => ['All'],
            'QueueUrl'              => $this->queueUrl ?? $queueUrl,
            'WaitTimeSeconds'       => 0,
        ];
    }

    // //----------------------------------------------------------------------

    /**
     * Imposta il nuovo url per la coda
     */
    protected function setQueue(string $queueUrl): void
    {
        $this->queueUrl = $queueUrl;
    }
}
