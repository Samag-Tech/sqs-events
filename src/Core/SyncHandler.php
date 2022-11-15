<?php

namespace SamagTech\SqsEvents\Core;

use SamagTech\SqsEvents\Traits\Response;

final class SyncHandler
{
    use Response;

    /**
     * Lista di eventi di sincronizzazione
     *
     * @var array
     *
     * @access private
     */
    private array $syncEvents = [];

    /**
     * Settaggio degli eventi di sincronizzazione
     *
     * @param $syncEvents           Lista di eventi lanciabili
     *
     * @access public
     */
    public function __construct(array $syncEvents)
    {
        $this->syncEvents = $syncEvents;
    }

    // //----------------------------------------------------------------------

    /**
     * Handling dei messaggi di sincronizzazione
     *
     * @param $message              Messaggio contenuto nell evento
     *
     * @return array
     *
     * @access public
     */
    public function sync(array $message): array
    {
        if (!isset($this->events[$message['event']])) {
            return $this->respond(null, "not_found", 404);
        }

        $event = new $this->syncEvents[$message['event']];
        $action = $event->handle($message);

        if (!$event->getStatus() && $this->log) {
            return $this->createLog($event->getErrors(), $event->getAction(), $event->getMsgType(), $message);
        }

        return $this->respond($action, "success", 200);
    }
}
