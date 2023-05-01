<?php

namespace DevBx\Forms;

use Bitrix\Main;

class FormActions {

    protected $arActions = array();
    protected $form;

    public function __construct(EO_Form $form)
    {
        $this->form = $form;

        $event = new Main\Event('devbx.forms', 'OnFormRegisterActions', array('actions' => $this));
        $event->send();
    }

}