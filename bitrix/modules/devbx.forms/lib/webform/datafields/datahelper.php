<?php

namespace DevBx\Forms\WebForm\DataFields;

use Bitrix\Main;
use Bitrix\Iblock;
use Bitrix\Main\Web\MimeType;
use Bitrix\Main\Web\Uri;
use DevBx\Forms\DB\EO_FormSession;
use DevBx\Forms\DB\FormSessionDataTable;

class DataHelper {
    public static function getFieldDataType(Main\ORM\Fields\Field $field)
    {
        static $dataTypes = array(
            'Bitrix\Main\ORM\Fields\FloatField' => 'number',
            'Bitrix\Main\ORM\Fields\StringField' => 'string',
            'Bitrix\Main\ORM\Fields\TextField' => 'string',
            'Bitrix\Main\ORM\Fields\DatetimeField' => 'datetime',
            'Bitrix\Main\ORM\Fields\DateField' => 'date',
            'Bitrix\Main\ORM\Fields\IntegerField' => 'number',
            'Bitrix\Main\ORM\Fields\EnumField' => 'enum',
            'Bitrix\Main\ORM\Fields\BooleanField' => 'boolean'
        );

        foreach ($dataTypes as $class=>$type)
        {
            if (is_a($field, $class, true))
                return $type;
        }

        return false;
    }

    public static function getUserFieldType($arUserField)
    {
        static $dataTypes = array(
            'string' => 'string',
            'file' => 'boolean',
            'double' => 'number',
            'boolean' => 'boolean',
            'int' => 'number',
            'datetime' => 'datetime',
            'date' => 'date',
            'enum' => 'enum',
        );

        if (array_key_exists($arUserField['USER_TYPE_ID'], $dataTypes))
            return $dataTypes[$arUserField['USER_TYPE_ID']];

        if (array_key_exists($arUserField['USER_TYPE']['BASE_TYPE'], $dataTypes))
            return $dataTypes[$arUserField['USER_TYPE']['BASE_TYPE']];

        return false;
    }

    public static function getIblockSectionFields($iblocKId)
    {
        global $USER_FIELD_MANAGER;

        static $cache = [];

        if (isset($cache[$iblocKId]))
            return $cache[$iblocKId];

        $result = [];

        foreach (Iblock\SectionTable::getEntity()->getScalarFields() as $field)
        {
            $fieldType = false;

            switch ($field->getName())
            {
                case 'TIMESTAMP_X':
                case 'DATE_CREATE':
                    $condType = 'datetime';
                    break;
                case 'PICTURE':
                case 'DETAIL_PICTURE':
                    $condType = 'boolean';
                    $fieldType = 'file';
                    break;
                case 'DESCRIPTION':
                case 'DESCRIPTION_TYPE':
                case 'SEARCHABLE_CONTENT':
                    // не фильтрует по этим свойствам https://dev.1c-bitrix.ru/api_help/iblock/classes/ciblocksection/getlist.php
                    continue 2;
                default:
                    $condType = static::getFieldDataType($field);
            }

            if (!$condType)
                continue;

            $resultField = array(
                'NAME' => $field->getName(),
                'LABEL' => $field->getTitle(),
                'COND_TYPE' => $condType,
                'MULTIPLE' => false,
            );

            $resultField['FIELD_TYPE'] = $fieldType ?: $condType;

            if ($condType == 'enum')
            {
                /* @var Main\ORM\Fields\EnumField $field */

                $resultField['VALUES'] = array();

                foreach ($field->getValues() as $value)
                {
                    $resultField['VALUES'][] = array(
                        'VALUE' => $value,
                        'TITLE' => $value,
                    );
                }
            }

            $result[$field->getName()] = $resultField;
        }

        $arUserFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_'.$iblocKId.'_SECTION', 0, LANGUAGE_ID);

        foreach ($arUserFields as $arUserField) {
            $condType = static::getUserFieldType($arUserField);
            if (!$condType)
                continue;

            $resultField = array(
                'NAME' => $arUserField['FIELD_NAME'],
                'LABEL' => $arUserField['LIST_COLUMN_LABEL'],
                'COND_TYPE' => $condType,
                'FIELD_TYPE' => $arUserField['USER_TYPE']['BASE_TYPE'],
                'MULTIPLE' => $arUserField['MULTIPLE'] == 'Y',
            );

            if ($condType == 'enum') {

                $resultField['VALUES'] = array();

                $iterator = \CUserFieldEnum::GetList([],['USER_FIELD_ID'=>$arUserField['ID']]);

                while ($arEnum = $iterator->Fetch())
                {
                    $field['VALUES'][] = array(
                        'VALUE' => $arEnum['ID'],
                        'TITLE' => $arEnum['VALUE'],
                    );
                }
            }

            $result[$arUserField['FIELD_NAME']] = $resultField;
        }

        $cache[$iblocKId] = $result;

        return $result;
    }

    public static function makePublicFileArray(EO_FormSession $formSession, $systemId, $fileId)
    {
        $returnArray = true;

        if (!is_array($fileId)) {

            $fileId = intval($fileId);

            if ($fileId<=0)
                return false;

            $fileId = array($fileId);
            $returnArray = false;
        }

        if (empty($fileId))
            return [];

        $arFileKeys = array_fill_keys(array_values($fileId), true);

        $iterator = FormSessionDataTable::getList([
            'filter' => [
                '=SESSION_ID' => $formSession->getId(),
                '=SYSTEM_ID' => $systemId,
                '=VALUE_TYPE' => 'file',
                '=VALUE_INT' => $fileId
            ],
        ]);

        while ($data = $iterator->fetch())
        {
            unset($arFileKeys[$data['VALUE_INT']]);
        }

        $addRows = [];

        foreach ($arFileKeys as $fileKey=>$tmp)
        {
            $addRows[] = [
                'SESSION_ID' => $formSession->getId(),
                'SYSTEM_ID' => $systemId,
                'VALUE_TYPE' => 'file',
                'VALUE_INT' => $fileKey
            ];
        }

        if (!empty($addRows))
        {
            FormSessionDataTable::addMulti($addRows, true);
        }

        $result = [];

        foreach ($fileId as $singleFileId)
        {
            $arFile = \CFile::GetFileArray($singleFileId);
            if (!is_array($arFile))
                continue;

            $arData = array(
                'fileId' => $singleFileId,
                'type' => $arFile['type'],
                'name' => $arFile['name'],
                'size' => $arFile['size'],
            );

            $uri = new Uri('/bitrix/tools/devbx.forms/devbx_webform.php');
            $uri->addParams(array(
                'action' => 'download',
                'sid' => $formSession->getSid(),
                'systemId' => $systemId,
                'fileId' => $singleFileId,
            ));
            $arData['download'] = $uri->getUri();

            if (MimeType::isImage($arFile['CONTENT_TYPE']))
            {
                $uri = new Uri('/bitrix/tools/devbx.forms/devbx_webform.php');
                $uri->addParams(array(
                    'action' => 'thumbnail',
                    'sid' => $formSession->getSid(),
                    'systemId' => $systemId,
                    'fileId' => $singleFileId,
                ));

                $arData['thumbnail'] = $uri->getUri();

                $uri = new Uri('/bitrix/tools/devbx.forms/devbx_webform.php');
                $uri->addParams(array(
                    'action' => 'preview',
                    'sid' => $formSession->getSid(),
                    'systemId' => $systemId,
                    'fileId' => $singleFileId,
                ));

                $arData['preview'] = $uri->getUri();
            }

            $result[] = $arData;
        }

        if (empty($result))
            return false;

        if ($returnArray)
            return $result;

        return reset($result);
    }
}