<?php

namespace SamagTech\SqsEvents\Core;

use Aws\Exception\AwsException;
use SamagTech\SqsEvents\Traits\ClientSQS;

final class Cleaner
{
    use ClientSQS;

    public function __construct(array $credentials, string $queueUrl)
    {
        // Inizializzo il client SQS
        $this->clientInit($credentials, $queueUrl);
    }

    // //----------------------------------------------------------------------

    /**
     * Lettura ed esecuzione dei messaggi sulla coda
     *
     * @return void
     */
    public function clean(): void
    {
        try {
            $numMessages = $this->client->getQueueAttributes([
                'QueueUrl' => $this->queueUrl,
                'AttributeNames' => ['ApproximateNumberOfMessages'],
            ])["Attributes"]["ApproximateNumberOfMessages"];

            for ($i = 1; $i <= $numMessages; $i++) {
                $receiptHandle = $this->client->receiveMessage($this->SQS)->get("Messages")[0]['ReceiptHandle'] ?? null;
                if (!is_null($receiptHandle)) {
                    $this->client->deleteMessage([
                        'QueueUrl' => $this->queueUrl,
                        'ReceiptHandle' => $receiptHandle
                    ]);
                }
            }
        } catch (AwsException $e) {
            throw $e;
        }
    }
}
