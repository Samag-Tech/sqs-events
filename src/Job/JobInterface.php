<?php

namespace SamagTech\SqsEvents\Job;

interface JobInterface
{
    /**
     * Da utilizzare per leggere e manipolare i dati nel messaggio
     *
     * @param  array $data
     * @return void
     */
    public function execute(array $data): array;
}
