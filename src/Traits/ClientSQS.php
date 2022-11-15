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
     * URL coda SQS
     *
     * @var string
     *
     * @access protected
     */
    protected string $queueUrl = "";

    /**
     * array contenente la configurazione della coda
     *
     * @var array
     *
     * @access protected
     */
    protected array $SQS = [];

    // //----------------------------------------------------------------------

    /**
     * Inizializza il client SQS
     *
     * @param string $queueUrl              Url coda SQS
     * @param array $credentials            Array contenente le credenziali per l'utente SQS,
     *                                      L'array dev'essere formattato come di seguito:
     *
     *                                      E.g
     *                                      [
     *                                          'region' => SQS_REGION,
     *                                          'version' => SQS_VERSION,
     *                                           'credentials' => [
     *                                               "key" => SQS_KEY,
     *                                               "secret" => SQS_SECRET,
     *                                           ]
     *                                      ]
     *
     * @return void
     *
     * @access protected
     */
    protected function clientInit(array $credentials, string $queueUrl): void
    {
        $this->client = new SqsClient($credentials);
        $this->setQueue($queueUrl);
        $this->SQSinit();
    }

    // //----------------------------------------------------------------------

    /**
     * Configurazione coda SQS
     *
     * @param string $queueUrl      Url coda SQS
     *
     * @access protected
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
     *
     * @param string $queueUrl      Url coda SQS
     *
     * @return void
     *
     * @access protected
     */
    protected function setQueue(string $queueUrl): void
    {
        $this->queueUrl = $queueUrl;
    }
}
