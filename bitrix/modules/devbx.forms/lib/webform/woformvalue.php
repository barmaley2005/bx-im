<?php

namespace DevBx\Forms\WebForm;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use DevBx\Forms\WebForm\Fields;

/**
 * @property string $name
 * @property string $type
 * @property mixed $value
 */

class WOFormValue extends WOBase {
    /** @var Fields\Base */
    protected $field;

    /** @var callback[] */
    protected $setValueModification = array();

    /** @var callback[] */
    protected $getValueModification = array();

    public function __construct(Fields\Base $field, $name, $type, $value = null, $virtual = false)
    {
        $this->field = $field;

        parent::__construct(array(
            'NAME' => $name,
            'TYPE' => $type,
            'VALUE' => $value,
            'VIRTUAL' => $virtual,
        ));
    }

    protected function getAllFields()
    {
        return array_merge(array('FIELD_ID'), parent::getAllFields());
    }

    /**
     * @param \callable $modifier
     * @return $this
     */
    public function addSetValueModifier($modifier)
    {
        $this->setValueModification[] = $modifier;

        return $this;
    }

    /**
     * @param \callable $modifier
     * @return $this
     */
    public function addGetValueModifier($modifier)
    {
        $this->getValueModification[] = $modifier;

        return $this;
    }

    protected function sysSetValue($name, $value): Result
    {
        switch ($name)
        {
            case 'FIELD_ID':
                return (new Result())->addError(new Error('cannot set FIELD_ID, is virtual'));
            case 'VALUE':
            foreach ($this->setValueModification as $callback) {
                $result = call_user_func($callback, $this, $value);
                if ($result instanceof Result) {
                    if (!$result->isSuccess())
                        return $result;

                    $data = $result->getData();
                    if (isset($data['VALUE']))
                        $value = $data['VALUE'];
                }
            }
        }

        return parent::sysSetValue($name, $value);
    }

    protected function sysGetValue($name)
    {
        switch ($name)
        {
            case 'FIELD_ID':
                if ($this->field)
                    return $this->field->systemId;

                break;
            case 'VALUE':
                $value = parent::sysGetValue($name);

                foreach ($this->getValueModification as $callback)
                {
                    $result = call_user_func($callback, $this, $value);
                    if ($result instanceof Result) {
                        if (!$result->isSuccess())
                            return null;

                        $data = $result->getData();
                        if (isset($data['VALUE']))
                            $value = $data['VALUE'];
                    }
                }
            break;
            default:
                $value = parent::sysGetValue($name);
        }

        return $value;
    }

}