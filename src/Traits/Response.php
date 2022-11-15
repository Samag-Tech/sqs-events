<?php

namespace SamagTech\SqsEvents\Traits;

/**
 * Formattazione delle risposte
 */
trait Response
{
    /**
     * Formatta i messaggi da ritornare
     */
    public function respond(string|array|null $res, string $type, string $status): array
    {
        return [
            "type" => $type,
            "status" => $status,
            "message" => $res
        ];
    }

    // //----------------------------------------------------------------------

    /**
     * Formatta le eccezioni per creare i log
     *
     * @param $res = Eccezione da formattare
     * @param $action = Nome dell'evento
     * @param $message = Messaggio della coda
     *
     * @return array
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
                "line"    => $res->getErrors()->getLine(),
                "trace"   => $res->getErrors()->getTraceAsString(),
                "file"    => $res->getErrors()->getFile(),
                "method"  => $res->getErrors()->getTrace()[0]['class'] . "->" . $res->getErrors()->getTrace()[0]['function'] . "()",
                "message" => $res->getErrors()->getMessage(),
            ]
        ];
    }
}
