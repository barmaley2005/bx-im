<?php

namespace DevBx\Forms\WebForm\Fields;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\FormTable;
use DevBx\Forms\WebForm\Types\IntegerType;
use DevBx\Forms\WebForm\WOBase;

/**
 * @property integer $systemId
 */
abstract class Base extends WOBase {

    protected $registered = false;

    public function __construct(array $values = null)
    {
        $values['SYSTEM_ID'] = new IntegerType('SYSTEM_ID');

        parent::__construct($values);
    }

    public function sysSetValue($name, $value): Result
    {
        switch ($name)
        {
            case 'FIELD_NAME':
                $value = trim($value);

                if (preg_match('#\s#', $value))
                    return (new Result())->addError(new Error('Invalid FIELD_NAME "'.$value.'"'));

                if (!preg_match('#^[a-zA-Z_]#', $value))
                    return (new Result())->addError(new Error('Invalid FIELD_NAME "'.$value.'"'));

                if (!preg_match('#^[a-zA-Z0-9_]+$#', $value))
                    return (new Result())->addError(new Error('Invalid FIELD_NAME "'.$value.'"'));

                if ($this->getParent())
                {
                    if ($this->getForm() && !empty($value))
                    {
                        $this->getForm()->registerFieldName($value, $this);
                    }
                }
                break;
        }

        return parent::sysSetValue($name, $value);
    }

    public function getUfFields(): array
    {
        return array();
    }

    public function getFormFields(): array
    {
        return array();
    }
    public function setParent($parent)
    {
        if ($this->registered)
        {
            $form = $this->getForm();
            if (!$form)
                throw new SystemException('Form objects corruption, cannot unregister object');

            $form->unRegisterFormObject($this);

            $this->registered = false;
        }

        parent::setParent($parent);

        $form = $this->getForm();
        if ($form)
        {
            $form->registerFormObject($this);
            $this->registered = true;
        }

        return $this;
    }

    public function initSystemId()
    {
        static $systemId = 0;

        if ($this->systemId>0)
            return;

        $form = $this->getForm();
        if (!$form)
            throw new SystemException('Form not found');


        while (true)
        {
            $systemId++;
            if (!$form->getFormObjectBySystemId($systemId))
                break;
        }

        $this->systemId = $systemId;
    }

    public function request(EO_FormSession $formSession, $params): Result
    {
        $result = new Result();

        if (empty($params['action']))
            return $result->addError(new Error('action is empty'));

        $action = $params['action'];
        if (!method_exists($this, $action.'Action'))
            return $result->addError(new Error('action not found '.$action));

        return $this->{$action.'Action'}($formSession, $params);
    }
    public static abstract function getFieldId();
    public static abstract function getGroupId();

    /**
     * @return Base
     */
    public static function createObject()
    {
        $className = get_called_class();

        return new $className;
    }

    public static function getFieldUntitledName()
    {
        return Loc::getMessage('DEVBX_WEB_FORM_FIELD_UNTITLED_NAME');
    }

    public static function getFieldData(): array
    {
        $obj = static::createObject();
        $obj->setDefault();

        return array(
            'FIELD_ID' => static::getFieldId(),
            'MIN_SIZE' => 3,
            'DEFAULT_SIZE' => 12,
            'DEFAULT_CONFIG' => $obj->toArray(),
        );
    }

    public static function getLangMessages()
    {
        $r = new \ReflectionClass(get_called_class());
        return Loc::loadLanguageFile($r->getFileName());
    }

    public function validateFormValue(EO_FormSession $formSession): Result
    {
        return new Result();
    }

    public function saveForDB(EO_FormSession $formSession): array
    {
        return array();
    }

    public function includePublicJS()
    {

    }
}