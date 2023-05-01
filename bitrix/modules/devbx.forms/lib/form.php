<?php
namespace DevBx\Forms;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

class FormTable extends DataManager
{
    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'b_devbx_form';
    }

    /**
     * @return array
     * @throws ArgumentException
     * @throws SystemException
     */
    public static function getMap()      
    {                                    
                                         
        $arFields = array(               
            new Fields\IntegerField('ID', array("primary" => true, "autocomplete" => true, "title" => "ID")),
            new Fields\StringField('CODE', array('title'=>Loc::getMessage('DEVBX_FORMS_FORM_CODE'), 'validation' => array(static::class, 'getCodeValidation'))),
            (new Fields\ArrayField('VIEW_GROUPS', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_VIEW_GROUPS"))))->configureSerializationPhp(),
            (new Fields\ArrayField('WRITE_GROUPS', array("title" => Loc::getMessage("DEVBX_FORMS_FORM_WRITE_GROUPS"))))->configureSerializationPhp(),
            new Fields\BooleanField('CREATE_ACTIVATED', array("values" => array("N", "Y"), "default_value" => "Y", "title" => Loc::getMessage('DEVBX_FORMS_FORM_CREATE_ACTIVATED'))),
            new Fields\StringField('FORM_TYPE', array("required"=>true,"title"=>Loc::getMessage("DEVBX_FORMS_FORM_TYPE"))),
            (new Fields\ArrayField('SETTINGS', array("title"=>Loc::getMessage("DEVBX_FORMS_FORM_SETTINGS"),"db_type" => "mediumblob")))
                ->configureUnserializeCallback(array(__CLASS__, 'decodeSettings'))
                ->configureSerializeCallback(array(__CLASS__, 'encodeSettings')),
            (new Fields\Relations\Reference(
                'LANG_NAME',
                FormLangNameTable::class,
                ['ref.FORM_ID'=>'this.ID','ref.LANGUAGE_ID'=>new SqlExpression('?s', LANGUAGE_ID)],
                ['join_type'=>'LEFT']
            )),
        );

        return $arFields;
    }

    public static function decodeSettings($value)
    {
        $firstChar = substr($value, 0, 1);
        if ($firstChar == '{' || $firstChar == '[')
            return Json::decode($value);

        return unserialize($value);
    }

    public static function encodeSettings($value)
    {
        return Json::encode($value);
    }

    public static function getCodeValidation()
    {
        return array(
            array(static::class, 'validateCode')
        );
    }

    public static function validateCode($value, $primary, array $row, Fields\Field $field)
    {
        if (!strlen($value))
            return true;

        if (preg_match('#\s#', $value))
            return Loc::getMessage('DEVBX_FORMS_FORM_INVALID_CODE');

        if (!preg_match('#^[a-zA-Z]#', $value))
            return Loc::getMessage('DEVBX_FORMS_FORM_INVALID_CODE');

        if (!preg_match('#^[a-zA-Z0-9]+$#', $value))
            return Loc::getMessage('DEVBX_FORMS_FORM_INVALID_CODE');

        $filter = array(
            '=CODE' => $value,
        );

        if (!empty($primary))
        {
            $filter['!=ID'] = $primary['ID'];
        }

        if (static::getList([
            'filter' => $filter
        ])->fetch())
        {
            return Loc::getMessage('DEVBX_FORMS_FORM_CODE_ALREADY_EXISTS');
        }

        return true;
    }

    public static function onBeforeAdd(Event $event)
    {
        $result = new EventResult();

        $fields = $event->getParameter('fields');

        if (!FormManager::getInstance()->getFormType($fields['FORM_TYPE']))
        {
            $result->addError(new Fields\FieldError(
                static::getEntity()->getField('FORM_TYPE'),
                Loc::getMessage('DEVBX_FORMS_FORM_UNKNOWN_FORM_TYPE', array('#FORM_TYPE#'=>$fields['FORM_TYPE'])))
            );
        }

        return $result;
    }

    /**
     * @param Event $event
     * @throws ArgumentException
     * @throws ObjectException
     * @throws SystemException
     */
    public static function onAfterAdd(Event $event)
    {
        $arPrimary = $event->getParameter("primary");
        $id = $arPrimary["ID"];

        /**@var EO_Form $obForm */

        $obForm = $event->getParameter('object');

        $formType = FormManager::getInstance()->getFormType($obForm->getFormType());

        //$formType::onAddForm($formType::compileEntity($obForm));
        $formType::onAddForm(FormManager::getInstance()->compileFormEntity($obForm));
    }

    public static function OnAfterUpdate(Event $event)
    {
        /**@var EO_Form $obForm */

        $obForm = $event->getParameter("object");
        $obForm->fill();

        $formEntity = FormManager::getInstance()->getFormInstance($obForm->getId());

        if ($formEntity)
        {
            $formEntity->setForm($obForm);

            $formType = FormManager::getInstance()->getFormType($obForm->getFormType());
            $formType::onUpdateForm($formEntity);
        }

    }

    public static function onBeforeDelete(Event $event)
    {
        $primary = $event->getParameter("primary");

        $obForm = static::getByPrimary($primary)->fetchObject();
        if ($obForm)
        {
            FormManager::getInstance()->getFormInstance($primary['ID']);
        }
    }

    /**
     * @param Event $event
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public static function onAfterDelete(Event $event)
    {
        $primary = $event->getParameter("primary");

        if (is_array($primary))
            $primary = $primary["ID"];

        $entity = FormManager::getInstance()->getFormInstance($primary);

        $formType = FormManager::getInstance()->getFormType($entity->getForm()->getFormType());
        $formType::onDeleteForm($entity);
    }
}

