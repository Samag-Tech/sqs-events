<?php

namespace SamagTech\SqsEvents\Job;

use Throwable;

abstract class BaseJob implements JobInterface
{
    /**
     * Stato dell'esecuzione del messaggio
     */
    protected bool $status = true;

    /**
     * Tipologia di evento
     */
    protected string $type;

    /**
     * Nome dell'evento
     */
    protected string $action;

    /**
     * Eccezione catturata durante l'esecuzione del job
     */
    private Throwable $errors;

    // //----------------------------------------------------------------------

    /**
     * Salvataggio degli errori
     *
     * @param  mixed $errors
     * @return void
     */
    protected function handleError($errors): void
    {
        $this->errors = $errors;
        $this->status = false;
    }

    // //----------------------------------------------------------------------

    /**
     * Ritorna gli errori riscontrati durante l'esecuzione del messaggio
     *
     * @return Throwable
     */
    public function getErrors(): Throwable
    {
        return $this->errors;
    }

    // //----------------------------------------------------------------------

    public function getStatus(): bool
    {
        return $this->status;
    }

    // //----------------------------------------------------------------------

    public function getAction(): string
    {
        return $this->action;
    }

    // //----------------------------------------------------------------------

    public function getMsgType(): string
    {
        return $this->type;
    }
}
