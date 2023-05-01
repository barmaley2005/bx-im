<?php

namespace DevBx\Forms;

use Bitrix\Main;
use DevBx\Core\Assert;

class FormManager {
    private static $instance;

    /**
     * @var FormResultEntity[]
     */
    private $formsEntity = [];

    protected $formTypes = false;

    public static function getInstance(): FormManager
    {
        if (!isset(self::$instance))
        {
            self::$instance = new FormManager();
        }

        return self::$instance;
    }

    function registerFormType($formType)
    {
        if (!is_subclass_of($formType, BaseFormType::class))
            throw new Main\SystemException('invalid registerFormType class '.$formType);

        $this->formTypes[$formType::getType()] = $formType;
    }

    /**
     * @param false $formType
     * @return BaseFormType[]|false|BaseFormType
     */
    function getFormType($formType = false)
    {
        if (!is_array($this->formTypes))
        {
            $this->formTypes = array();

            $event = new Main\Event('devbx.forms', 'OnFormTypeBuildList', array('manager'=>$this));
            $event->send();
        }

        if($formType !== false)
        {
            if(array_key_exists($formType, $this->formTypes))
                return $this->formTypes[$formType];
            else
                return false;
        }
        else
            return $this->formTypes;
    }

    /**
     * @param $form
     * @return FormResultEntity
     * @throws Main\ArgumentException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    function compileFormEntity($form)
    {
        if ($form instanceof EO_Form)
        {
            $formObj = $form;
        } else {
            if (is_array($form))
            {
                $formObj = FormTable::getByPrimary($form['ID'])->fetchObject();
                if (!$formObj)
                    throw new Main\SystemException('form not found '.$form['ID']);
            } else {
                $formObj = Assert::expectIntegerPositive($form, 'form');

                $formObj = FormTable::getByPrimary($formObj)->fetchObject();
                if (!$formObj)
                    throw new Main\SystemException('form not found '.$form);
            }
        }

        if (isset($this->formsEntity[$formObj->getId()]))
            return $this->formsEntity[$formObj->getId()];

        $formType = $this->getFormType($formObj->getFormType());
        if (!$formType)
            throw new Main\SystemException('unknown form type '.$formObj->getFormType());


        $entity = $formType::compileEntity($formObj);

        $this->formsEntity[$formObj->getId()] = $entity;

        return $entity;
    }

    /**
     * @param $formId
     * @return FormResultEntity|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFormInstance($formId)
    {
        if (empty($formId))
            throw new Main\SystemException('formId is empty');

        if (isset($this->formsEntity[$formId]))
            return $this->formsEntity[$formId];

        if (is_numeric($formId))
        {
            return $this->compileFormEntity($formId);
        } elseif (is_string($formId))
        {
            foreach ($this->formsEntity as $entity)
            {
                if ($entity->getForm()->getCode() == $formId)
                    return $entity;
            }

            $obForm = FormTable::getList([
                'filter' => [
                    '=CODE' => $formId
                ],
            ])->fetchObject();

            if ($obForm)
                return $this->compileFormEntity($obForm);
        }

        throw new Main\SystemException('Form not found '.$formId);
    }

    public function loadAllForms()
    {
        $iterator = FormTable::getList();
        while ($obForm = $iterator->fetchObject())
        {
            if (isset($this->formsEntity[$obForm->getId()]))
                continue;

            $this->compileFormEntity($obForm);
        }
    }
}