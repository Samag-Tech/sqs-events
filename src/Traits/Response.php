<?php

namespace SamagTech\SqsEvents\Traits;

/**
 * Formattazione delle risposte
 */
trait Response
{

    /**
     * Funzione per la formattazione delle risposte
     *
     * @param  string|array|null $res
     * @param  string $type
     * @param  int $status
     *
     * @return array
     *
     * @access public
     */
    public function respond(string|array|null $res, string $type, int $status): array
    {
        return [
            "type" => $type,
            "status" => $status,
            "message" => $res
        ];
    }

    // //----------------------------------------------------------------------

    /**
     * Funzione per il settaggio dei messaggi di errore
     *
     * @param  string|array|null $res
     * @param  int $status
     *
     * @return array
     *
     * @access public
     */
    public function respondError(string|array|null $res, int $status = 500): array
    {
        return $this->respond($res, "exception", $status);
    }

    // //----------------------------------------------------------------------

    /**
     * Funzione per il settaggio dei messaggi di successo
     *
     * @param  string|array|null $res
     * @param  int $status
     *
     * @return array
     *
     * @access public
     */
    public function respondSuccess(string|array|null $res, int $status = 200): array
    {
        return $this->respond($res, "success", $status);
    }

    // //----------------------------------------------------------------------

    /**
     * Messaggio da ritornare quando non si sta effettuando nessuna azione
     *
     * @return array
     *
     * @access public
     */
    public function respondNoAction(): array
    {
        return [
            "type" => "no_action",
            "status" => 200,
            "message"=> "No messages to execute"
        ];
    }

    // //----------------------------------------------------------------------

    /**
     * Formatta le eccezioni per creare i log
     *
     * @param $res              Eccezione da formattare
     * @param $action           Nome dell'evento
     * @param $type             Tipologia di evento
     * @param $message          Messaggio della coda
     *
     * @return array
     *
     * @access public
     */
    public function createLog(Object $res, string $action, string $type, array $message): array
    {
        return [
            "type" => "exception",
            "status" => 500,
            "message" => [
                "name" => $action,
                "message" => json_encode($message),
                "failed"  => true,
                "type"    => $type,
                "line"    => $res->getLine(),
                "trace"   => $res->getTraceAsString(),
                "file"    => $res->getFile(),
                "method"  => $res->getTrace()[0]['class'] . "->" . $res->getTrace()[0]['function'] . "()",
                "message" => $res->getMessage(),
            ]
        ];
    }
}
