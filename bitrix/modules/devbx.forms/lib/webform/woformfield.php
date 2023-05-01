<?php


namespace DevBx\Forms\WebForm;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Text\StringHelper;
use DevBx\Forms\WebForm\Fields;
use DevBx\Forms\Wizards\WebForm\Wizard;

/**
 * @property string $fieldId
 * @property string $props
 */
class WOFormField extends WOCollectionItem
{
    /**
     * @var Fields\Base
     */
    protected $_obField = null;
    /**
     * @var WOBase
     */
    protected $_obEntity = null;
    public function __construct()
    {
        parent::__construct(array(
            'SIZE' => '',
        ));
    }

    protected function getAllFields()
    {
        $fields = parent::getAllFields();

        return array_merge($fields, array(
            'FIELD_ID' => 'FIELD_ID',
            'CONFIG' => 'CONFIG',
        ));
    }

    protected function sysSetValue($name, $value): Result
    {
        switch ($name)
        {
            case 'FIELD_ID':
                $this->_obField = Wizard::getInstance()->getFieldManager()->getFieldClass($value);
                $this->_obEntity = $this->_obField::createObject();
                $this->_obEntity->setParent($this);
                return new Result();
            case 'ENTITY':
                return (new Result())->addError(new Error(('Cannot set ENTITY')));
            case 'CONFIG':
                if (!is_array($value))
                    return (new Result())->addError(new Error(('field CONFIG must be array')));

                if (!$this->_obEntity)
                    return (new Result())->addError(new Error(('field entity is null')));

                return $this->_obEntity->setValues($value);
            default:
                return parent::sysSetValue($name, $value);
        }
    }

    protected function sysGetValue($name)
    {
        switch ($name)
        {
            case 'FIELD_ID':
                if (!$this->_obField)
                    return '';

                return $this->_obField::getFieldId();
            case 'CONFIG':
                return $this->_obEntity;
            default:
                return parent::sysGetValue($name);
        }
    }

    public function getField()
    {
        return $this->_obField;
    }

    public function getEntity()
    {
        return $this->_obEntity;
    }

}