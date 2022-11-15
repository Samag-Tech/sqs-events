<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Exceptions\SyncException;
use SamagTech\SqsEvents\Traits\Response;

final class SyncHandler
{
    use Response;

    /**
     * Lista di eventi di sincronizzazione
     */
    private array $syncEvents = [];

    /**
     * Settaggio degli eventi di sincronizzazione
     *
     * @param $syncEvents = Lista di eventi lanciabili
     */
    public function __construct(array $syncEvents)
    {
        $this->syncEvents = $syncEvents;
    }

    // //----------------------------------------------------------------------

    /**
     * Handling dei messaggi di sincronizzazione
     *
     * @param $message = Messaggio contenuto nell evento
     *
     * @return array
     */
    public function sync(array $message): array
    {
        if (!isset($this->events[$message['event']])) {
            return $this->respond(null, "not_found", 404);
        }

        $event = new $this->syncEvents[$message['event']];
        $action = $event($message);

        if (!$event->getStatus() && $this->log) {
            return $this->createLog($event->getErrors(), $event->getAction(), $event->getMsgType(), $message);
        }

        return $this->respond($action, "success", 200);
    }
}
