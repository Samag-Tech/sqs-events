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
     * Array contenente la lista dei possibili eventi da richiamare
     *
     * @var array
     *
     * @access private
     */
    private array $events = [];

    /**
     * Array contenente la lista degli eventi di sincronizzazione da poter richiamare
     *
     * @var array|null
     *
     * @access private
     */
    private ?array $syncEvents = null;

    /**
     * @param array $events             Array contenente la lista dei possibili eventi da richiamare
     * @param array $syncEvents         Array contenente la lista degli eventi di sincronizzazione da poter richiamare
     *
     * @access public
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
     * @param string $action            Nome dell'evento da eseguire
     * @param array $message            Dati da inviare al job
     *
     * @return array getTrace|$action   Risultato dell'esecuzione dell'evento
     *
     * @access public
     */
    public function execute(string $action, array $message): array
    {
        if ($action == 'sync') {
            return (new SyncHandler($this->syncEvents))->sync($message);
        }

        if (!isset($this->events[$action])) {
            return $this->respondError("Evento $action non settato", 404);
        }

        $event = (new $this->events[$action]);
        $action = $event->handle($message);

        if (!$event->getStatus()) {
            return $this->createLog($event->getErrors(), $event->getAction(), $event->getMsgType(), $message);
        }
        return $this->respondSuccess($action);
    }
}
