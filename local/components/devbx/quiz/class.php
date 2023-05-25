<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CDevBxQuiz extends CBitrixComponent
{
    protected $originalParameters;

    public function onPrepareComponentParams($arParams)
    {
        $arParams['ACTION_VARIABLE'] = isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : '';
        if ($arParams['ACTION_VARIABLE'] == '') {
            $arParams['ACTION_VARIABLE'] = 'quiz-action';
        }

        $this->originalParameters = $arParams;

        return parent::onPrepareComponentParams($arParams);
    }

    public function getHLValues($propId)
    {
        $arProperty = \CIBlockProperty::GetByID($propId, $this->arParams['IBLOCK_ID'])->Fetch();
        if (!$arProperty)
            return false;

        $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            'filter' => array(
                '=TABLE_NAME' => $arProperty['USER_TYPE_SETTINGS']['TABLE_NAME'],
            )
        ])->fetch();
        if (!$hlblock)
            return false;

        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);

        $iterator = $entity->getDataClass()::getList([
            'order' => array(
                'UF_SORT' => 'ASC',
                'ID' => 'ASC',
            ),
        ]);

        $result = [];

        while ($ar = $iterator->fetch()) {
            $value = array();

            foreach ($ar as $k => $v) {
                if (substr($k, 0, 3) == 'UF_') {
                    $k = substr($k, 3);
                }

                if ($k == 'FILE') {
                    $v = \CFile::GetFileArray($v);
                }

                $value[$k] = $v;
            }

            $result[] = $value;
        }

        return $result;
    }


    public function initHLValues()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        \Bitrix\Main\Loader::includeModule('highloadblock');

        $arParams = $this->arParams;
        $arResult = &$this->arResult;

        $arResult['WRAP_TYPE'] = $this->getHLValues($arParams['PROPERTY_WRAP_TYPE']);
        $arResult['WRAP_FORM'] = $this->getHLValues($arParams['PROPERTY_WRAP_FORM']);
        $arResult['WRAP_SIZE'] = $this->getHLValues($arParams['PROPERTY_WRAP_SIZE']);
        $arResult['WRAP_COLOR'] = $this->getHLValues($arParams['PROPERTY_WRAP_COLOR']);
    }

    public function getConfigAction()
    {
        $this->initHLValues();
    }

    public function getPriceRangeAction()
    {
        \Bitrix\Main\Loader::includeModule('iblock');

        $arParams = $this->arParams;
        $values = $this->request->get('values');

        $arFilter = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_TYPE'] => $values['wrapType'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_FORM'] => $values['wrapForm'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_SIZE'] => $values['wrapSize'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_COLOR'] => $values['wrapColor'],
        );

        $arMinimum = \CIBlockElement::GetList(
            array('PROPERTY_MINIMUM_PRICE' => 'ASC'),
            $arFilter + array('!=PROPERTY_MINIMUM_PRICE' => false),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_MINIMUM_PRICE'))->Fetch();

        $arMaximum = \CIBlockElement::GetList(
            array('PROPERTY_MAXIMUM_PRICE' => 'DESC'),
            $arFilter + array('!=PROPERTY_MAXIMUM_PRICE' => false),
            false,
            array('nTopCount' => 1),
            array('PROPERTY_MAXIMUM_PRICE'))->Fetch();

        $this->arResult['minimumPrice'] = doubleval($arMinimum['PROPERTY_MINIMUM_PRICE_VALUE']);
        $this->arResult['maximumPrice'] = doubleval($arMaximum['PROPERTY_MAXIMUM_PRICE_VALUE']);
    }

    public function getQuizItemsAction()
    {
        global $APPLICATION;

        \Bitrix\Main\Loader::includeModule('catalog');

        $arParams = $this->arParams;
        $values = $this->request->get('values');

        /*
        $this->arResult['FILTER'] = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_TYPE'] => $values['wrapType'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_FORM'] => $values['wrapForm'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_SIZE'] => $values['wrapSize'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_COLOR'] => $values['wrapColor'],
            '>=PROPERTY_MINIMUM_PRICE' => $values['minimumPrice'],
            '<=PROPERTY_MAXIMUM_PRICE' => $values['maximumPrice'],
        );
        */

        $arBasePrice = \Bitrix\Catalog\GroupTable::getBasePriceType();

        $this->arResult['FILTER'] = array(
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y',
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_TYPE'] => $values['wrapType'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_FORM'] => $values['wrapForm'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_SIZE'] => $values['wrapSize'],
            '=PROPERTY_' . $arParams['PROPERTY_WRAP_COLOR'] => $values['wrapColor'],
            '><CATALOG_PRICE_'.$arBasePrice['ID'] => array($values['minimumPrice'], $values['maximumPrice'])
        );

        $APPLICATION->RestartBuffer();
        $this->includeComponentTemplate('result');
        die();
    }

    public function executeComponent()
    {
        global $APPLICATION;

        $arParams = $this->arParams;
        $arResult = &$this->arResult;

        if ($arParams['AJAX_CALL'] == 'Y')
        {
            $action = $this->request->getPost($this->arParams['ACTION_VARIABLE']);

            if (!empty($action))
            {
                if (is_callable(array($this, $action.'Action')))
                {
                    $this->{$action.'Action'}();

                    $APPLICATION->RestartBuffer();

                    echo \Bitrix\Main\Web\Json::encode($arResult);
                    die();
                }
            }
        }

        $salt = str_replace(':','.', $this->getName());

        $signer = new \Bitrix\Main\Security\Sign\Signer;
        $arResult['SIGNED_PARAMS'] = $signer->sign(base64_encode(serialize($this->originalParameters)), $salt);
        $arResult['SIGNED_TEMPLATE'] = $signer->sign($this->getTemplateName(), $salt);

        $this->includeComponentTemplate();
    }
}
