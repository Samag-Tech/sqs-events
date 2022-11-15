<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Exceptions\SyncException;
use SamagTech\SqsEvents\Traits\ClientSQS;

final class SyncEmitter
{
    use ClientSQS;

    /**
     * Attributi custome da aggiungere al messaggio
     *
     * @var array
     * @access private
     */
    private array $messageAttributes = [];

    /**
     * Indirizzi url code SQS
     *
     * @var array
     * @access private
     */
    private array $queueUrls = [];

    /**
     * Credenziali utente SQS
     *
     * @var array
     * @access private
     */
    private array $credentials = [];

    /**
     * Inizializzazione del client SQS
     *
     * @param array $credentials            credenziali per accedere alla coda sqs
     * @param array|string $queueUrls       url o lista di url appartenenti alla coda sqs a cui inviare il messaggio
     *
     * @access public
     */
    public function __construct(array $credentials, string|array $queueUrls)
    {
        if (is_string($queueUrls)) {
            $queueUrls = [$queueUrls];
        }
        $this->queueUrls = $queueUrls;
        $this->credentials = $credentials;
    }

    // //----------------------------------------------------------------------

    /**
     * Registra un nuovo messaggio nella coda
     *
     * @param string $event         Nome da dare all'evento
     * @param array $data           Contenuto del messaggio
     * @param ?array $args          Argomenti custom da aggiungere alla creazione della coda
     *
     * @return void
     *
     * @access public
     */
    public function run(string $event, array $data, ?array $args = null): void
    {
        foreach ($this->queueUrls as $key => $value) {
            $this->clientInit($this->credentials, $value);
            $this->createEvent(
                event: $event,
                data: $data,
                args: $args
            );
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Creazione evento di sincronizzazione
     *
     * @param string $event         Nome da dare all'evento
     * @param array $data           Contenuto del messaggio
     * @param array|null $args      Argomenti custom da aggiungere alla creazione della coda
     *
     * @throws SyncException
     * @return void
     *
     * @access private
     */
    private function createEvent(string $event, array $data, ?array $args = null): void
    {
        $data['event'] = $event;
        try {
            if (is_null($args)) {
                $args = [
                    "DelaySeconds" => 0,
                    'MessageGroupId' => uniqid(),
                    'MessageDeduplicationId' => uniqid(),
                    "MessageAttributes" => $this->messageAttributes,
                    'QueueUrl' => $this->queueUrl
                ];
            }
            $args["MessageBody"] = json_encode($data);
            $args["MessageAttributes"]["request"] = [
                'DataType' => "String",
                'StringValue' => "sync",
            ];
            $this->client->sendMessage($args);
        } catch (AwsException $th) {
            throw new SyncException($th->getMessage(),$th->getStatusCode());
        }
    }
}
