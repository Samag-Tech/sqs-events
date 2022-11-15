<?php

namespace SamagTech\SqsEvents\Job;

use SamagTech\SqsEvents\Traits\Response;
use Throwable;

/**
 * Classe da estendere per il corretto funzionamento dei job
 */
abstract class BaseJob implements JobInterface
{
    use Response;
    /**
     * Stato dell'esecuzione del messaggio
     *
     * @var bool
     *
     * @access private
     */
    private bool $status = true;

    /**
     * Tipologia di evento
     *
     * @var string
     *
     * @access private
     */
    private string $type;

    /**
     * Nome dell'evento
     *
     * @var string
     *
     * @access private
     */
    private string $action;

    /**
     * Eccezione catturata durante l'esecuzione del job
     *
     * @var Throwable
     *
     * @access private
     */
    private Throwable $errors;

    // //----------------------------------------------------------------------

    public function handle(array $data) : array
    {
        try {
            return $this->execute($data);
        } catch (\throwable $th) {
            $this->setErrors($th);
            return $this->respond($th->getMessage(),"exception",500);
        }
    }

    // //----------------------------------------------------------------------

    /**
     * Settaggio degli errori
     *
     * @return void
     *
     * @access public
     */
    public function setErrors(throwable $errors)
    {
        $this->handleError($errors);
    }

    // //----------------------------------------------------------------------

    /**
     * Salvataggio degli errori
     *
     * @param  throwable $errors
     * @return void
     *
     * @access protected
     */
    protected function handleError(throwable $errors): void
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

    /**
     * Getter per lo status
     *
     * @return bool
     *
     * @access public
     */
    public function getStatus(): bool
    {
        return $this->status;
    }

    // //----------------------------------------------------------------------

    /**
     * Setta lo status del job
     *
     * @param  bool $status
     * @return self
     *
     * @access protected
     */
    protected function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    // //----------------------------------------------------------------------

    /**
     * Ritorna il nome dell'azione svolta dal job
     *
     * @return string
     *
     * @access public
     */
    public function getAction(): string
    {
        return $this->action;
    }

    // //----------------------------------------------------------------------

    /**
     * Setta il nome dell'azione svolta dal job
     *
     * @param string $action        Nome da dare all'azione svolta dal job
     * @return self
     *
     * @access protected
     */
    protected function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    // //----------------------------------------------------------------------

    /**
     * Ritorna la tipologia di job
     *
     * @return string
     *
     * @access public
     */
    public function getMsgType(): string
    {
        return $this->type;
    }

    // //----------------------------------------------------------------------

    /**
     * Sette della tipologia di job
     *
     * @param string $type
     * @return self
     *
     * @access protected
     */
    protected function setMsgType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
