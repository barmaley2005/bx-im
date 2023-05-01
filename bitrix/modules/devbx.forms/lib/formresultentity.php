<?php

namespace DevBx\Forms;

use Bitrix\Main\ORM\Entity;

class FormResultEntity extends Entity {

    protected $form;
    protected $parameters = [];

    public function getFormId()
    {
        return $this->form['ID'];
    }

    public function setForm(EO_Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return EO_Form
     */
    public function getForm()
    {
        return $this->form;
    }

    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function getParameter($name)
    {
        return $this->parameters[$name];
    }
}
