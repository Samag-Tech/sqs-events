<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Exceptions\HandlerException;

final class Handler
{
    protected bool $log = true;

    private array $events = [];

    public function __construct(array $events)
    {
        $this->events = $events;
    }

    /**
     * Esecuzione dell'evento
     */
    public function execute(string $action, array $message): array
    {
        if (!isset($this->events[$action])) {
            return (new HandlerException("Il servizio $action non esiste"))->getTrace();
        }

        $event = (new $this->events[$action]);

        $action = $event($message);

        if (!$event->status && $this->log) {
            return $event->getTrace();
        }

        return $action;
    }
}
