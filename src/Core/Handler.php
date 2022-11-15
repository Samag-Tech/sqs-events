<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Core\SyncHandler;
use SamagTech\SqsEvents\Traits\Response;

/**
 * Classe che si occupa di eseguire gli eventi
 */
final class Handler
{
    use Response;
    /**
     * Attivazione dei log
     */
    private bool $log = true;

    /**
     * Array contenente la lista dei possibili eventi da richiamare
     */
    private array $events = [];

    /**
     * Array contenente la lista degli eventi di sincronizzazione da poter richiamare
     */
    private ?array $syncEvents = null;

    /**
     * @param array $events = Array contenente la lista dei possibili eventi da richiamare
     * @param array $syncEvents = Array contenente la lista degli eventi di sincronizzazione da poter richiamare
     */
    public function __construct(array $events, ?array $syncEvents = null)
    {
        $this->events = $events;
        $this->syncEvents = $syncEvents;
    }

    // //----------------------------------------------------------------------

    /**
     * Esecuzione dell'evento
     *
     * @param string $action = Nome dell'evento da eseguire
     * @param array $message = Dati da inviare al job
     *
     * @return array getTrace|$action = Risultato dell'esecuzione dell'evento
     */
    public function execute(string $action, array $message): array
    {
        if ($action == 'sync') {
            return (new SyncHandler($this->syncEvents))->sync($message);
        }

        if (!isset($this->events[$action])) {
            return $this->respond(null, "not_found", 404);
        }

        $event = (new $this->events[$action]);
        $action = $event($message);

        if (!$event->getStatus() && $this->log) {
            return $this->createLog($event->getErrors(), $event->getAction(), $event->getMsgType(), $message);
        }
        return $this->respond($action, "success", 200);
    }

    // //----------------------------------------------------------------------

    /**
     * TRUE per abilitare i log, FALSE per disabilitare, di default i log sono sempre attivi
     *
     * @param bool $log
     *
     * @return self
     */
    public function setLogs(bool $log): self
    {
        $this->log = $log;
        return $this;
    }
}
