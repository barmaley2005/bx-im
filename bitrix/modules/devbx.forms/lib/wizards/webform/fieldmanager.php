<?php

namespace DevBx\Forms\Wizards\WebForm;

use Bitrix\Main;
use DevBx\Forms\WebForm\Fields;

class FieldManager
{

    protected $arWebFormFieldsGroups = array();
    protected $arWebFormFields = array();

    public function __construct()
    {
        $event = new Main\Event('devbx.forms', 'OnWebFormWizardRegisterFields', array('manager' => $this));
        $event->send();
    }

    public function addWebFormGroup($groupId, $name, $sort = 500)
    {
        $this->arWebFormFieldsGroups[$groupId] = new WebFormFieldsGroup($this, $groupId, $name, $sort);
    }

    public function addWebFormField($className, $sort = 500)
    {
        if (!is_subclass_of($className, Fields\Base::class))
            throw new Main\SystemException('invalid WebFormField class ' . $className);

        if (isset($this->arWebFormFields[$className::getFieldId()]))
            throw new Main\SystemException('WebFormField ' . $className::getFieldId() . ' already registered');

        $this->arWebFormFields[$className::getFieldId()] = array(
            'SORT' => $sort,
            'CLASS_NAME' => $className,
        );
    }

    /* @return false|WebFormFieldsGroup|WebFormFieldsGroup[] */
    public function getWebFormFieldsGroup($groupId = false)
    {
        if ($groupId !== false) {
            if (array_key_exists($groupId, $this->arWebFormFieldsGroups))
                return $this->arWebFormFieldsGroups[$groupId];
            else
                return false;
        } else {

            $result = $this->arWebFormFieldsGroups;

            uasort($result, function ($a, $b) {
                if ($a->getSort() == $b->getSort())
                    return 0;

                return $a->getSort() > $b->getSort() ? 1 : -1;
            });

            return $result;
        }
    }

    public function getWebFormField($fieldId = false)
    {
        if ($fieldId !== false) {
            if (array_key_exists($fieldId, $this->arWebFormFields))
                return $this->arWebFormFields[$fieldId];
            else
                return false;
        } else
            return $this->arWebFormFields;
    }

    /* @return Fields\Base */
    public function getFieldClass($fieldId)
    {
        if (!isset($this->arWebFormFields[$fieldId]))
            throw new Main\SystemException('Invalid fieldId: ' . $fieldId);

        return $this->arWebFormFields[$fieldId]['CLASS_NAME'];
    }
}