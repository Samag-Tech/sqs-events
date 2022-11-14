<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Exceptions\SyncException;

final class SyncHandler
{
    private array $syncEvents = [];

    public function __construct(array $syncEvents)
    {
        $this->syncEvents = $syncEvents;
    }

    public function sync(array $message)
    {
        if (!isset($message['event'])) {
            throw new SyncException("Il messaggio di sincronizzazione deve contenere il nome dell'evento", 500);
        }

        $event = new $this->syncEvents[$message['event']];
        $action = $event($message);

        if (!$event->status && $this->log) {
            return $event->getTrace();
        }

        return $action;
    }
}
