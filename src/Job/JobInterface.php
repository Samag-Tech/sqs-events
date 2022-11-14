<?php

namespace SamagTech\SqsEvents\Job;

use Throwable;

interface JobInterface
{
    /**
     * Da utilizzare per leggere e manipolare i dati nel messaggio
     *
     * @param  array $data
     * @return void
     */
    public function __invoke(array $data);

    // //----------------------------------------------------------------------

    /**
     * Ritorna gli errori riscontrati durante l'esecuzione delle query
     *
     * @return Throwable
     */
    public function getErrors(): Throwable;
}
