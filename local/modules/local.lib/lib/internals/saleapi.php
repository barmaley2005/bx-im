<?php

namespace Local\Lib\Internals;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\BasketPropertyItemBase;
use Local\Lib\Api;
use DevBx\Core\Assert;

class SaleApi
{
    public static function getBasketCount(Api $context, Main\Type\ParameterDictionary $params)
    {
        global $USER;

        Main\Loader::includeModule('sale');

        $result = [];

        $basketCnt = 0;
        $delayCnt = 0;

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID);

        /* @var \Bitrix\Sale\BasketItem $basketItem */

        foreach ($basket->getBasketItems() as $basketItem) {

            $delay = $basketItem->getField('DELAY');
            $canBuy = $basketItem->getField('CAN_BUY');

            if ($delay == 'N' && $canBuy == 'Y') {
                $basketCnt++;
            } elseif ($delay == 'Y' && $canBuy == 'Y') {
                $delayCnt++;
            }
        }

        $result['basket'] = $basketCnt;
        $result['delay'] = $delayCnt;

        return $result;
    }

    public static function getPublicViewPropertyValue($property)
    {
        $value = $property['VALUE'];

        if (empty($value))
            return '';

        if (!is_array($value)) {
            $value = [$value];
        }

        if ($property['PROPERTY_TYPE'] == 'E') {
            $dbRes = \CIBlockElement::GetList([], ['=ID' => $value], false, false, ['ID', 'NAME']);

            $res = [];

            while ($arRes = $dbRes->Fetch()) {
                if (!empty($arRes['NAME']))
                    $res[] = $arRes['NAME'];
            }

            return implode(', ', $res);
        } else {
            return implode(', ', $value);
        }
    }

    public static function getBasketProductProperties($productId, $getProperties = [])
    {
        $iblockId = \CIBlockElement::GetIBlockByID($productId);

        $arInfo = \CCatalogSku::GetInfoByIBlock($iblockId);

        $offerValues = false;
        $values = false;

        $getOfferProperties = [];

        if (isset($getProperties['OFFERS'])) {
            $getOfferProperties = $getProperties['OFFERS'];
            unset($getProperties['OFFERS']);
        }

        if ($arInfo['CATALOG_TYPE'] == 'O') {
            $ar = \Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
                $iblockId,
                ['CODE' => 'Y']
            );

            if (!is_array($ar))
                $ar = [];

            $ar = array_merge($ar, $getOfferProperties);

            if (!empty($ar)) {
                \CIBlockElement::GetPropertyValuesArray($offerValues, $iblockId, array('ID' => $productId), array('CODE' => $ar), array('GET_RAW_DATA' => 'Y'));
                if (is_array($offerValues))
                    $offerValues = $offerValues[$productId];
            }

            $arProductInfo = \CCatalogSku::GetProductInfo($productId, $iblockId);
            if ($arProductInfo) {

                $ar = \Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
                    $arProductInfo['IBLOCK_ID'],
                    ['CODE' => 'Y']
                );

                if (is_array($ar)) {
                    $getProperties = array_merge($getProperties, $ar);
                }

                if (!empty($getProperties))
                {
                    \CIBlockElement::GetPropertyValuesArray($values, $arProductInfo['IBLOCK_ID'], array('ID' => $arProductInfo['ID']), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

                    if (is_array($values)) {
                        $values = $values[$arProductInfo['ID']];

                        if (is_array($offerValues))
                            $values = array_merge($values, $offerValues);
                    } else
                    {
                        $values = $offerValues;
                    }
                } else
                {
                    $values = $offerValues;
                }
            }

        } else {
            $ar = \Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes(
                $iblockId,
                ['CODE' => 'Y']
            );

            if (is_array($ar) && !empty($ar)) {
                $getProperties = array_merge($getProperties, $ar);
            }

            \CIBlockElement::GetPropertyValuesArray($values, $iblockId, array('ID' => $productId), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

            if (is_array($values))
                $values = $values[$productId];
        }

        return $values;
    }

    private static function getProductProperties($productId, $getProperties)
    {
        $iblockId = \CIBlockElement::GetIBlockByID($productId);

        $arInfo = \CCatalogSku::GetInfoByIBlock($iblockId);

        $values = false;

        if ($arInfo['CATALOG_TYPE'] == 'O') {
            $arProductInfo = \CCatalogSku::GetProductInfo($productId, $iblockId);
            if ($arProductInfo) {
                \CIBlockElement::GetPropertyValuesArray($values, $arProductInfo['IBLOCK_ID'], array('ID' => $arProductInfo['ID']), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

                if (is_array($values))
                    $values = $values[$arProductInfo['ID']];
            }

        } else {
            \CIBlockElement::GetPropertyValuesArray($values, $iblockId, array('ID' => $productId), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

            if (is_array($values))
                $values = $values[$productId];
        }

        return $values;
    }

    private static function __addBasket($productId, $quantity, $extra = [], $delay = false)
    {
        static $basketProperties = ['OFFERS' => ['LOCATION', 'EVENT_DATE']];

        $productId = Assert::expectIntegerPositive($productId, 'productId');
        $quantity = Assert::expectIntegerPositive($quantity, 'quantity');
        $extra = Assert::expectArray($extra, 'extra');

        $iblockId = \CIBlockElement::GetIBlockByID($productId);

        if (!$iblockId) {
            throw new Main\SystemException('Товар не найден');
        }

        $arProduct = array(
            'PRODUCT_ID' => $productId,
            'QUANTITY' => $quantity,
        );

        $getProperties = array_merge($basketProperties, array_keys($extra));

        $values = self::getBasketProductProperties($productId, $getProperties);

        if (is_array($values))
        {
            foreach ($values as $prop)
            {
                if (empty($prop['VALUE']))
                    continue;

                if ($prop['PROPERTY_TYPE'] == 'S' && $prop['USER_TYPE'] == 'DateTime')
                {
                    try {

                        $d = new \Bitrix\Main\Type\DateTime($prop['VALUE']);
                        $format = \Bitrix\Main\Type\Date::convertFormatToPhp('YYYY-MM-DD HH:MI');
                        $productProperties[] = array(
                            'NAME' => $prop['NAME'],
                            'CODE' => $prop['CODE'],
                            'VALUE' => $d->format($format),
                            'SORT' => $prop['SORT'],
                        );

                    } catch (\Exception $e)
                    {

                    }
                } else
                {
                    $productProperties[] = array(
                        'NAME' => $prop['NAME'],
                        'CODE' => $prop['CODE'],
                        'VALUE' => self::getPublicViewPropertyValue($prop),
                        'SORT' => $prop['SORT'],
                    );
                }
            }
        }

        /*
        if (is_array($values)) {
            foreach ($values as $prop) {

                if (array_key_exists($prop['CODE'], $extra)) {
                    foreach ($extra[$prop['CODE']] as $valueId) {
                        $index = array_search($valueId, $prop['PROPERTY_VALUE_ID']);
                        if ($index !== false) {
                            $productProperties[] = array(
                                'NAME' => $prop['NAME'],
                                'CODE' => $prop['CODE'] . ':' . $valueId . ':' . $prop['DESCRIPTION'][$index],
                                'VALUE' => $prop['VALUE'][$index] . ' (' . $prop['DESCRIPTION'][$index] . ')',
                                'SORT' => $prop['SORT'],
                            );
                        }
                    }
                } else {
                    if ($prop['VALUE']) {
                        $productProperties[] = array(
                            'NAME' => $prop['NAME'],
                            'CODE' => $prop['CODE'],
                            'VALUE' => self::getPublicViewPropertyValue($prop),
                            'SORT' => $prop['SORT'],
                        );
                    }
                }
            }
        }
        */

        if (!empty($productProperties))
            $arProduct['PROPS'] = $productProperties;

        $basketFields = array(
            'DELAY' => $delay ? 'Y' : 'N',
        );

        return \Bitrix\Catalog\Product\Basket::addProduct($arProduct, $basketFields);
    }

    public static function addBasket(Api $context, Main\Type\ParameterDictionary $params)
    {
        Main\Loader::includeModule('iblock');
        Main\Loader::includeModule('catalog');
        Main\Loader::includeModule('sale');

        $productId = Assert::expectIntegerPositive($params['id'], 'id');

        $quantity = doubleval($params['quantity']);

        if ($quantity <= 0)
            $quantity = 1;

        $result = self::__addBasket($productId, $quantity);

        if ($result->isSuccess()) {
            return ['success' => true, 'message'=>'Добавлено в корзину'];
        } else {
            return ['error' => $result->getErrorMessages()];
        }
    }

    public static function deleteBasket(Api $context, Main\Type\ParameterDictionary $params)
    {
        Main\Loader::includeModule('sale');

        $basketId = Assert::expectIntegerPositive($params['id'], 'id');

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        /** @var Sale\Basket $basketClassName */
        $basketClassName = $registry->getBasketClassName();
        $basket = $basketClassName::loadItemsForFUser(
            Sale\Fuser::getId(),
            Main\Application::getInstance()->getContext()->getSite()
        );

        $basketItem = $basket->getItemById($basketId);

        if (!$basketItem)
        {
            return ['error' => 'Товар не найден в корзине'];
        }

        $result = $basketItem->delete();
        if (!$result->isSuccess())
            return $result;

        $result = $basket->save();

        if ($result->isSuccess())
        {
            $result->setData(['id'=>$basketId]);
        }

        return $result;
    }


    public static function changeBasketExtra(Api $context, Main\Type\ParameterDictionary $params)
    {
        if (!$params->offsetExists('EXTRA_SERVICE'))
            throw new Main\SystemException('required value');

        $extraService = Assert::expectNotEmptyArray($params['EXTRA_SERVICE']);

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID);

        $basketItem = $basket->getItemByBasketCode($params['id']);
        if (!$basketItem)
            throw new Main\SystemException('basket item not found');

        $productId = $basketItem->getProductId();

        $propertyValues = self::getBasketProductProperties($productId);

        if (is_array($propertyValues)) {
            foreach ($extraService as $code => $value) {
                if (!array_key_exists($code, $propertyValues)) //если нету свойства у элемента пропускаем
                    continue;

                $prop = $propertyValues[$code];

                if ($prop['MULTIPLE'] != 'Y')
                    continue;

                $data = explode(':', $value);

                $basketValue = false;

                if (count($data) === 3) {
                    $index = array_search($data[1], $prop['PROPERTY_VALUE_ID']);

                    if ($index !== false) {
                        $basketValue = [
                            'NAME' => $prop['NAME'],
                            'CODE' => $prop['CODE'] . ':' . $data[1] . ':' . $prop['DESCRIPTION'][$index],
                            'VALUE' => $prop['VALUE'][$index] . ' (' . $prop['DESCRIPTION'][$index] . ')',
                            'SORT' => $prop['SORT'],
                        ];
                    }
                }

                foreach ($basketItem->getPropertyCollection() as $basketProperty) {
                    /* @var BasketPropertyItem $basketProperty */

                    $ar = explode(':', $basketProperty->getField('CODE'));
                    if (count($ar) == 3 && $ar[0] == $code) {
                        $basketProperty->delete();
                    }
                }

                if ($basketValue) {
                    $basketItem->getPropertyCollection()->createItem()->setFields($basketValue);
                }
            }
        }

        $basket->save();

        return ['values'=>$params->getValues()];
    }

    public static function getBasket(Api $context, Main\Type\ParameterDictionary $params)
    {
        global $USER;

        $items = [];

        Main\Loader::includeModule('iblock');
        Main\Loader::includeModule('sale');

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID);

        $userId = is_object($USER) && $USER->IsAuthorized() ? $USER->GetID() : \CSaleUser::GetAnonymousUserID();

        $registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
        /** @var \Bitrix\Sale\Order $orderClass */
        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::create(SITE_ID, $userId);

        $result = $order->appendBasket($basket);

        $discounts = $order->getDiscount();
        $showPrices = $discounts->getShowPrices();
        if (!empty($showPrices['BASKET'])) {
            foreach ($showPrices['BASKET'] as $basketCode => $data) {
                $basketItem = $basket->getItemByBasketCode($basketCode);
                if ($basketItem instanceof \Bitrix\Sale\BasketItemBase) {
                    $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                    $basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
                    $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                }
            }
        }


        /* @var \Bitrix\Sale\BasketItem $basketItem */

        foreach ($basket->getBasketItems() as $basketItem) {

            $arItem = [
                'ID' => $basketItem->getId(),
                'IBLOCK_ID' => \CIBlockElement::GetIBlockByID($basketItem->getProductId()),
                'PRODUCT_ID' => $basketItem->getProductId(),
                'NAME' => $basketItem->getField('NAME'),
                'CAN_BUY' => $basketItem->canBuy(),
                'DELAY' => $basketItem->isDelay(),
                'PRICE' => $basketItem->getPrice(),
                'DISCOUNT_PRICE' => $basketItem->getDiscountPrice(),
                'DETAIL_PAGE_URL' => $basketItem->getField('DETAIL_PAGE_URL'),
                'MEASURE_NAME' => $basketItem->getField('MEASURE_NAME'),
                'QUANTITY' => $basketItem->getQuantity(),
                'PROPS' => [],
            ];

            foreach ($basketItem->getPropertyCollection() as $prop) {
                /* @var BasketPropertyItemBase $prop */

                $arItem['PROPS'][$prop->getField('CODE')] = [
                    'NAME' => $prop->getField('NAME'),
                    'VALUE' => $prop->getField('VALUE'),
                ];
            }

            $productId = $basketItem->getProductId();


            $arElement = \CIBlockElement::GetList([], ['=ID' => $productId], false, false, ['ID', 'IBLOCK_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE'])->Fetch();
            if ($arElement) {
                $arFile = \CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
                if (!is_array($arFile))
                    $arFile = \CFile::GetFileArray($arElement['DETAIL_PICTURE']);

                if (is_array($arFile)) {
                    $arItem['PICTURE'] = $arFile['SRC'];
                }

                if (!$arItem['PICTURE']) {
                    $arInfo = \CCatalogSku::GetInfoByIBlock($arElement['IBLOCK_ID']);

                    $values = false;

                    if ($arInfo['CATALOG_TYPE'] == 'O') {

                        $arProductInfo = \CCatalogSku::GetProductInfo($productId, $arElement['IBLOCK_ID']);
                        if ($arProductInfo) {
                            \CIBlockElement::GetPropertyValuesArray($values, $arProductInfo['IBLOCK_ID'], array('ID' => $arProductInfo['ID']), array('CODE' => ['PHOTO_URL']), array('GET_RAW_DATA' => 'Y'));

                            if (is_array($values) && isset($values[$arProductInfo['ID']])) {
                                $values = $values[$arProductInfo['ID']];
                            }
                        }
                    } else {
                        \CIBlockElement::GetPropertyValuesArray($values, $arElement['IBLOCK_ID'], array('ID' => $productId), array('CODE' => ['PHOTO_URL']), array('GET_RAW_DATA' => 'Y'));
                        if (is_array($values) && isset($values[$productId])) {
                            $values = $values[$productId];
                        }
                    }

                    if (is_array($values)) {
                        foreach ($values as $prop) {
                            if (!empty($prop['VALUE'])) {
                                $arItem['PICTURE'] = reset($prop['VALUE']);
                                break;
                            }
                        }
                    }
                }
            }

            $items[] = $arItem;
        }

        return ['items' => $items, 'success' => true];
    }


    const SESSION_COMPARE_LIST = 'CATALOG_COMPARE_LIST';
    const COMPARE_ADD = 1;
    const COMPARE_TOGGLE = 2;
    const COMPARE_DELETE = 3;

    public static function getCompareList()
    {
        $result['items'] = [];

        if (isset($_SESSION[self::SESSION_COMPARE_LIST]) && is_array($_SESSION[self::SESSION_COMPARE_LIST])) {
            foreach ($_SESSION[self::SESSION_COMPARE_LIST] as $iblockId => $ar) {
                if (is_array($ar['ITEMS'])) {
                    $result['items'] = array_merge($result['items'], array_keys($ar['ITEMS']));
                }
            }
        }

        $result['success'] = true;

        return $result;
    }

    public static function clearCompare()
    {
        unset($_SESSION[self::SESSION_COMPARE_LIST]);

        $result['success'] = true;

        return $result;
    }

    public static function compareAction($productId, $mode)
    {
        Main\Loader::includeModule('iblock');

        $IBLOCK_ID = \CIBlockElement::GetIBlockByID($productId);

        if (!$IBLOCK_ID) {
            return ['error' => 'Товар не найден'];
        }

        if (!isset($_SESSION[self::SESSION_COMPARE_LIST][$IBLOCK_ID]["ITEMS"][$productId])) {

            if ($mode != self::COMPARE_ADD && $mode != self::COMPARE_TOGGLE)
                return [];

            $found = true;
            $arOffers = \CIBlockPriceTools::GetOffersIBlock($IBLOCK_ID);
            $OFFERS_IBLOCK_ID = $arOffers ? $arOffers["OFFERS_IBLOCK_ID"] : 0;

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "IBLOCK_SECTION_ID",
                "NAME",
                "DETAIL_PAGE_URL",
            );
            $arFilter = array(
                "ID" => $productId,
                "IBLOCK_LID" => SITE_ID,
                "IBLOCK_ACTIVE" => "Y",
                "ACTIVE_DATE" => "Y",
                "ACTIVE" => "Y",
                "CHECK_PERMISSIONS" => "Y",
                "MIN_PERMISSION" => "R"
            );
            $arFilter["IBLOCK_ID"] = ($OFFERS_IBLOCK_ID > 0 ? array($IBLOCK_ID, $OFFERS_IBLOCK_ID) : $IBLOCK_ID);

            $rsElement = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
            //$rsElement->SetUrlTemplates($arParams["DETAIL_URL"]);
            $arElement = $rsElement->GetNext();
            unset($rsElement);
            if (empty($arElement))
                $found = false;

            if ($found) {
                if ($arElement['IBLOCK_ID'] == $OFFERS_IBLOCK_ID) {
                    $rsMasterProperty = \CIBlockElement::GetProperty($arElement["IBLOCK_ID"], $arElement["ID"], array(), array("ID" => $arOffers["OFFERS_PROPERTY_ID"], "EMPTY" => "N"));
                    $arMasterProperty = $rsMasterProperty->Fetch();
                    unset($rsMasterProperty);
                    if (empty($arMasterProperty))
                        $found = false;
                    if ($found) {
                        $arMasterProperty['VALUE'] = (int)$arMasterProperty['VALUE'];
                        if ($arMasterProperty['VALUE'] <= 0)
                            $found = false;
                    }
                    if ($found) {
                        $rsMaster = \CIBlockElement::GetList(
                            array(),
                            array(
                                'ID' => $arMasterProperty['VALUE'],
                                'IBLOCK_ID' => $arMasterProperty['LINK_IBLOCK_ID'],
                                'ACTIVE' => 'Y',
                            ),
                            false,
                            false,
                            $arSelect
                        );
                        //$rsMaster->SetUrlTemplates($arParams['DETAIL_URL']);
                        $arMaster = $rsMaster->GetNext();
                        unset($rsMaster);
                        if (empty($arMaster)) {
                            $found = false;
                        } else {
                            $arMaster['NAME'] = $arElement['NAME'];
                            $arElement = $arMaster;
                        }
                        unset($arMaster);
                    }
                }
            }
            if ($found) {
                $sectionsList = array();
                $sectionsIterator = \Bitrix\Iblock\SectionElementTable::getList(array(
                    'select' => array('IBLOCK_SECTION_ID'),
                    'filter' => array('=IBLOCK_ELEMENT_ID' => $arElement['ID'], '=ADDITIONAL_PROPERTY_ID' => null)
                ));
                while ($section = $sectionsIterator->fetch()) {
                    $sectionId = (int)$section['IBLOCK_SECTION_ID'];
                    $sectionsList[$sectionId] = $sectionId;
                }
                unset($section, $sectionsIterator);
                $_SESSION[self::SESSION_COMPARE_LIST][$IBLOCK_ID]['ITEMS'][$productId] = array(
                    'ID' => $arElement['ID'],
                    '~ID' => $arElement['~ID'],
                    'IBLOCK_ID' => $arElement['IBLOCK_ID'],
                    '~IBLOCK_ID' => $arElement['~IBLOCK_ID'],
                    'IBLOCK_SECTION_ID' => $arElement['IBLOCK_SECTION_ID'],
                    '~IBLOCK_SECTION_ID' => $arElement['~IBLOCK_SECTION_ID'],
                    'NAME' => $arElement['NAME'],
                    '~NAME' => $arElement['~NAME'],
                    'DETAIL_PAGE_URL' => $arElement['DETAIL_PAGE_URL'],
                    '~DETAIL_PAGE_URL' => $arElement['~DETAIL_PAGE_URL'],
                    'SECTIONS_LIST' => $sectionsList,
                    'PARENT_ID' => $productId,
                    /*'DELETE_URL' => htmlspecialcharsbx($APPLICATION->GetCurPageParam(
                        $arParams['ACTION_VARIABLE']."=DELETE_FROM_COMPARE_LIST&".$arParams['PRODUCT_ID_VARIABLE']."=".$productID,
                        array($arParams['ACTION_VARIABLE'], $arParams['PRODUCT_ID_VARIABLE'])
                    ))*/
                );
                unset($sectionsList, $arElement);
            } else {
                $result["error"] = "Ошибка добавления товара";
            }
        } else {
            if ($mode == self::COMPARE_DELETE || $mode == self::COMPARE_TOGGLE) {
                $result["remove"] = true;
                unset($_SESSION[self::SESSION_COMPARE_LIST][$IBLOCK_ID]["ITEMS"][$productId]);
            }
        }

        $result['count'] = count($_SESSION[self::SESSION_COMPARE_LIST][$IBLOCK_ID]['ITEMS']);

        if (!isset($result['error'])) {
            $result['success'] = true;
        }

        return $result;
    }

    public static function addCompare(Api $context, Main\Type\ParameterDictionary $params)
    {
        return self::compareAction(Assert::expectIntegerPositive($params['id'], 'id'), self::COMPARE_ADD);
    }

    public static function removeCompare(Api $context, Main\Type\ParameterDictionary $params)
    {
        return self::compareAction(Assert::expectIntegerPositive($params['id'], 'id'), self::COMPARE_DELETE);
    }

    public static function toggleCompare(Api $context, Main\Type\ParameterDictionary $params)
    {
        return self::compareAction(Assert::expectIntegerPositive($params['id'], 'id'), self::COMPARE_TOGGLE);
    }

    public static function makeOrderItems(Api $context, Main\Type\ParameterDictionary $params)
    {
        global $USER;

        $result = new Main\Result();

        Main\Loader::includeModule('sale');

        $itemsId = Assert::expectNotEmptyArray($params['ITEMS'], 'ITEMS');

        $siteId = Main\Application::getInstance()->getContext()->getSite();

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        /** @var Sale\Basket $basketClassName */
        $basketClassName = $registry->getBasketClassName();
        $userBasket = $basketClassName::loadItemsForFUser(
            Sale\Fuser::getId(),
            $siteId
        );

        $orderClassName = $registry->getOrderClassName();
        /** @var Sale\Order $order */

        $order = $orderClassName::create($siteId, $USER->GetID() ? $USER->GetID() : \CSaleUser::GetAnonymousUserID());

        $order->isStartField();

        $orderBasket = \Bitrix\Sale\Basket::create($order->getSiteId());
        $order->setBasket($orderBasket);

        foreach ($itemsId as $itemId)
        {
            $basketItem = $userBasket->getItemById($itemId);

            if (!$basketItem)
            {
                return $result->addError(new Main\Error('Basket item id '.$itemId.' not found'));
            }


            $basketItem->setCollection($orderBasket);
            $orderBasket->addItem($basketItem);
        }

        $arPersonType = Sale\PersonType::load($siteId);
        if (empty($arPersonType))
        {
            return $result->addError(new Main\Error('Sale person type is empty, for site '.$siteId));
        }

        $arPersonTypeId = array_keys($arPersonType);

        if ($params->offsetExists('PERSON_TYPE_ID'))
        {
            $personTypeId = $params->offsetExists('PERSON_TYPE_ID');
            if (!in_array($personTypeId, $arPersonTypeId))
            {
                return $result->addError(new Main\Error('Person type '.$personTypeId.' not found'));
            }
        } else {
            $personTypeId = reset($arPersonTypeId);
        }

        $order->setPersonTypeId($personTypeId);

        foreach ($params->toArray() as $k=>$v)
        {
            if (preg_match('/^ORDER_PROP_(\d+)$/',$k,$m))
            {
                $property = $order->getPropertyCollection()->getItemByOrderPropertyId($m[1]);
                if ($property)
                {
                    $property->setValue($v);
                }
            } else {

                switch ($k)
                {
                    case 'USER_NAME':
                        $property = $order->getPropertyCollection()->getPayerName();
                        if ($property)
                        {
                            $property->setValue($v);
                        }

                        $property = $order->getPropertyCollection()->getProfileName();
                        if ($property)
                        {
                            $property->setValue($v);
                        }
                        break;
                    case 'USER_PHONE':
                        $property = $order->getPropertyCollection()->getPhone();
                        if ($property)
                        {
                            $property->setValue($v);
                        }
                        break;
                    case 'USER_EMAIL':
                        $property = $order->getPropertyCollection()->getUserEmail();
                        if ($property)
                        {
                            $property->setValue($v);
                        }
                        break;
                }
            }
        }

        $order->doFinalAction(true);

        $orderResult = $order->save();
        if (!$orderResult->isSuccess())
        {
            $result->addErrors($orderResult->getErrors());
        }

        $result->setData(['id'=>$order->getId()]);

        return $result;
    }

    public static function recalculateBasket(Api $context, Main\Type\ParameterDictionary $params)
    {
        $result = new Main\Result();

        Main\Loader::includeModule('sale');

        $siteId = Main\Application::getInstance()->getContext()->getSite();

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        /** @var Sale\Basket $basketClassName */
        $basketClassName = $registry->getBasketClassName();
        $userBasket = $basketClassName::loadItemsForFUser(
            Sale\Fuser::getId(),
            $siteId
        );

        $refreshStrategy = Sale\Basket\RefreshFactory::create(Sale\Basket\RefreshFactory::TYPE_FULL);
        $userBasket->refresh($refreshStrategy);

        $basket = $userBasket->getOrderableItems();

        /** @var Sale\Order $orderClass */
        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::create($siteId, Sale\Fuser::getId());
        $order->appendBasket($basket);

        $discounts = $order->getDiscount();
        $showPrices = $discounts->getShowPrices();
        if (!empty($showPrices['BASKET']))
        {
            foreach ($showPrices['BASKET'] as $basketCode => $data)
            {
                $basketItem = $basket->getItemByBasketCode($basketCode);
                if ($basketItem instanceof Sale\BasketItemBase)
                {
                    $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                    $basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
                    $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                }
            }
        }

        $data = [
            'ITEMS' => []
        ];

        foreach ($basket as $basketItem)
        {
            /* @var \Bitrix\Sale\BasketItemBase $basketItem */
            $data['ITEMS'][] = $basketItem->getFieldValues();
        }

        $data['BASE_PRICE'] = $basket->getBasePrice();
        $data['PRICE'] = $basket->getPrice();
        $data['VAT_RATE'] = $basket->getVatRate();
        $data['WEIGHT'] = $basket->getWeight();

        $userBasket->save();

        $result->setData($data);

        unset($_SESSION['SALE_USER_BASKET_PRICE']);

        return $result;
    }

    public static function registerApi(Api $api)
    {
        $api->registerApi('getBasketCount', array(__CLASS__, 'getBasketCount'));
        $api->registerApi('addBasket', array(__CLASS__, 'addBasket'));
        $api->registerApi('deleteBasket', array(__CLASS__, 'deleteBasket'));
        $api->registerApi('changeBasketExtra', array(__CLASS__, 'changeBasketExtra'));
        $api->registerApi('getBasket', array(__CLASS__, 'getBasket'));
        $api->registerApi('getCompareList', array(__CLASS__, 'getCompareList'));
        $api->registerApi('clearCompare', array(__CLASS__, 'clearCompare'));
        $api->registerApi('addCompare', array(__CLASS__, 'addCompare'));
        $api->registerApi('removeCompare', array(__CLASS__, 'removeCompare'));
        $api->registerApi('toggleCompare', array(__CLASS__, 'toggleCompare'));
        $api->registerApi('makeOrderItems', array(__CLASS__, 'makeOrderItems'));
        $api->registerApi('recalculateBasket', array(__CLASS__, 'recalculateBasket'));

    }


}