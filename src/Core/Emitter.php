<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Exceptions\EmitterException;
use SamagTech\SqsEvents\Traits\ClientSQS;

/**
 * Classe per la creazione di messaggi su una coda SQS
 */
final class Emitter
{
    use ClientSQS;

    /**
     * Array contenente gli attributi custom da aggiungere
     * alla richiesta di creazione del messaggio
     *
     * @var array
     *
     * @access private
     */
    private array $messageAttributes = [];

    /**
     * Indirizzi code SQS
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
     * @param array $credentials            Credenziali per accedere alla coda SQS
     * @param array|string $queueUrls       Url o lista di url appartenenti alla coda SQS a cui inviare il messaggio
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
     * Richiama la funzione di creazione del messaggio
     * per ogni coda inviata al costruttore
     *
     * @param string $event             Nome da dare all'evento
     * @param array $data               Contenuto del messaggio
     * @param ?array $args              Argomenti custom da aggiungere alla creazione della coda
     *
     * @return void
     *
     * @access public
     */
    public function run(string $event, array $data, ?array $args = null): void
    {
        foreach ($this->queueUrls as $key => $queue) {
            $this->clientInit($this->credentials, $queue);
            $this->createEvent(
                event: $event,
                data: $data,
                args: $args
            );
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Funzione per il settaggio degli attributi custom da aggiungere al messaggio
     *
     * @param array $attributes         Attributi custome da aggiungere
     *
     * @return self
     *
     * @access public
     */
    public function setAttributes(array $attributes): self
    {
        $this->messageAttributes = $attributes;
        return $this;
    }

    // //----------------------------------------------------------------------

    /**
     * Creazione dell'evento nella coda
     *
     * @param string $event         Nome da dare all'evento
     * @param array $data           Contenuto del messaggio
     * @param ?array $args          Argomenti custom da aggiungere alla creazione della coda
     *
     * @return void
     *
     * @access private
     */
    private function createEvent(string $event, array $data, ?array $args = null): void
    {
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
                'StringValue' => $event,
            ];

            $this->client->sendMessage($args);
        } catch (AwsException $th) {
            throw new EmitterException($th->getMessage(), $th->getStatusCode());
        }
    }
}
