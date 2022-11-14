<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Core\SyncHandler;
use SamagTech\SqsEvents\Exceptions\HandlerException;

final class Handler
{
    protected bool $log = true;

    private array $events = [];

    private ?array $syncEvents = null;

    public function __construct(array $events, ?array $syncEvents = null)
    {
        $this->events = $events;
        $this->syncEvents = $syncEvents;
    }

    /**
     * Esecuzione dell'evento
     */
    public function execute(string $action, array $message): array
    {
        if ($action == 'sync') {
            return (new SyncHandler($this->syncEvents))->sync($message);
        }

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
