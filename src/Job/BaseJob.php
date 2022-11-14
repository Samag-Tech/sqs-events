<?php

namespace SamagTech\SqsEvents\Job;

use SamagTech\SqsEvents\Traits\ExceptionToArray;
use Throwable;

abstract class BaseJob implements JobInterface
{
    public bool $status = true;

    public string $type;

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
     * ritorna gli errori riscontrati durante l'esecuzione del messaggio
     *
     * @return Throwable
     */
    public function getErrors(): Throwable
    {
        return $this->errors;
    }
}
