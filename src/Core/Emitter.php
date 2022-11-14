<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;

final class Emitter
{
    use ClientSQS;

    private array $messageAttributes = [];

    public function __construct(array $credentials, string $queueUrl)
    {
        $this->clientInit($credentials, $queueUrl);
    }

    /**
     * Registra un nuovo messaggio nella coda
     *
     * @return bool
     * @throws AwsException
     */
    public function run(string $event, array $data, ?array $args = null): bool
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
            return true;
        } catch (AwsException $th) {
            return $th->getTrace();
        }
    }
}
