<?php


namespace Codyas\Toolbox\Event;


use Symfony\Component\Form\FormInterface;

class CrudEntityPrePersistEvent extends CrudEntityEvent
{
    protected $entity;
    protected $form;

    public function __construct(\Codyas\Toolbox\Model\CrudOperationable $entity, FormInterface $form)
    {
        parent::__construct($entity);
        $this->form = $form;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

}