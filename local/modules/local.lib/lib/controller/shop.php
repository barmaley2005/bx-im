<?php

namespace Local\Lib\Controller;

use Bitrix\Catalog\MeasureRatioTable;
use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Sale;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\BasketPropertyItemBase;
use DevBx\Core\Assert;
use Local\Lib\DB\FavoriteTable;
use Local\Lib\DB\SMSCodeTable;
use Bitrix\Main\Localization\Loc;
use Local\Lib\Utils;

class Shop extends Main\Engine\Controller
{
    const EVENT_SEND_SMS_AUTH_CODE = 'SEND_SMS_AUTH_CODE';
    const EVENT_SEND_SMS_REGISTER_CODE = 'SEND_SMS_REGISTER_CODE';

    const NEW_USER_GROUP = array(2);


    protected function getDefaultPreFilters()
    {
        return [
            //new ActionFilter\Authentication(),
            new ActionFilter\HttpMethod(
                [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
            ),
            new ActionFilter\Csrf(),
        ];
    }

    public static function getBasketCountAction()
    {
        Main\Loader::includeModule('sale');

        $result = [];

        $readyCnt = 0;
        $delayCnt = 0;

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), SITE_ID);

        /* @var \Bitrix\Sale\BasketItem $basketItem */

        foreach ($basket->getBasketItems() as $basketItem) {

            $delay = $basketItem->getField('DELAY');
            $canBuy = $basketItem->getField('CAN_BUY');

            if ($delay == 'N' && $canBuy == 'Y') {
                $readyCnt++;
            } elseif ($delay == 'Y' && $canBuy == 'Y') {
                $delayCnt++;
            }
        }

        $result['ready'] = $readyCnt;
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

                if (!empty($getProperties)) {
                    \CIBlockElement::GetPropertyValuesArray($values, $arProductInfo['IBLOCK_ID'], array('ID' => $arProductInfo['ID']), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

                    if (is_array($values)) {
                        $values = $values[$arProductInfo['ID']];

                        if (is_array($offerValues))
                            $values = array_merge($values, $offerValues);
                    } else {
                        $values = $offerValues;
                    }
                } else {
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

            if (!empty($getProperties)) {
                \CIBlockElement::GetPropertyValuesArray($values, $iblockId, array('ID' => $productId), array('CODE' => $getProperties), array('GET_RAW_DATA' => 'Y'));

                if (is_array($values))
                    $values = $values[$productId];
            }
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
        //static $basketProperties = ['OFFERS' => ['LOCATION', 'EVENT_DATE']];
        static $basketProperties = [
            'ARTNUMBER',
            'OFFERS' => ['COLOR_REF', 'SIZES_SHOES', 'SIZES_CLOTHES']
        ];

        $productId = Assert::expectIntegerPositive($productId, 'productId');
        $quantity = Assert::expectIntegerPositive($quantity, 'quantity');
        $extra = Assert::expectArray($extra, 'extra');

        $iblockId = \CIBlockElement::GetIBlockByID($productId);

        if (!$iblockId) {
            throw new Main\SystemException(Loc::getMessage('LOCAL_LIB_SHOP_PRODUCT_NOT_FOUND'));
        }

        $arProduct = array(
            'PRODUCT_ID' => $productId,
            'QUANTITY' => $quantity,
        );

        $getProperties = array_merge($basketProperties, array_keys($extra));

        $values = self::getBasketProductProperties($productId, $getProperties);

        if (is_array($values)) {
            foreach ($values as $prop) {
                if (empty($prop['VALUE']))
                    continue;

                if ($prop['PROPERTY_TYPE'] == 'S' && $prop['USER_TYPE'] == 'DateTime') {
                    try {

                        $d = new \Bitrix\Main\Type\DateTime($prop['VALUE']);
                        $format = \Bitrix\Main\Type\Date::convertFormatToPhp('YYYY-MM-DD HH:MI');
                        $productProperties[] = array(
                            'NAME' => $prop['NAME'],
                            'CODE' => $prop['CODE'],
                            'VALUE' => $d->format($format),
                            'SORT' => $prop['SORT'],
                        );

                    } catch (\Exception $e) {

                    }
                } else {
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

    public static function addBasketAction(int $productId, float $quantity)
    {
        Main\Loader::includeModule('iblock');
        Main\Loader::includeModule('catalog');
        Main\Loader::includeModule('sale');

        if ($quantity <= 0)
            $quantity = 1;

        $ratio = 1;

        $arRatio = MeasureRatioTable::getCurrentRatio($productId);
        if (is_array($arRatio) && !empty($arRatio))
            $ratio = reset($arRatio);

        if ($ratio <= 0)
            $ratio = 1;

        $quantity = ceil($quantity / $ratio) * $ratio;


        $result = self::__addBasket($productId, $quantity);

        if ($result->isSuccess()) {
            return ['success' => true, 'message' => Loc::getMessage('LOCAL_LIB_SHOP_SUCCESS_ADD_TO_BASKET')];
        } else {
            return ['error' => $result->getErrorMessages()];
        }
    }

    public static function deleteBasketAction($basketId)
    {
        Main\Loader::includeModule('sale');

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        /** @var Sale\Basket $basketClassName */
        $basketClassName = $registry->getBasketClassName();
        $basket = $basketClassName::loadItemsForFUser(
            Sale\Fuser::getId(),
            Main\Application::getInstance()->getContext()->getSite()
        );

        $basketItem = $basket->getItemById($basketId);

        if (!$basketItem) {
            return ['error' => Loc::getMessage('LOCAL_LIB_SHOP_PRODUCT_NOT_FOUND_IN_BASKET')];
        }

        $result = $basketItem->delete();
        if (!$result->isSuccess())
            return $result;

        $result = $basket->save();

        if ($result->isSuccess()) {
            $result->setData(['id' => $basketId]);
        }

        return $result;
    }


    public static function changeBasketExtraAction(int $id, array $params)
    {
        if (!isset($params['EXTRA_SERVICE']))
            throw new Main\SystemException('required value');

        $extraService = Assert::expectNotEmptyArray($params['EXTRA_SERVICE']);

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), SITE_ID);

        $basketItem = $basket->getItemByBasketCode($id);
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

        return ['values' => $params->getValues()];
    }

    public static function getBasketAction()
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

            $measureRatio = 1;

            $arMeasureRatio = MeasureRatioTable::getCurrentRatio($basketItem->getProductId());
            if (is_array($arMeasureRatio) && !empty($arMeasureRatio)) {
                $measureRatio = reset($arMeasureRatio);
            }

            if ($measureRatio <= 0)
                $measureRatio = 1;

            $arItem['MEASURE_RATIO'] = $measureRatio;
            $arItem['QUANTITY_MEASURE'] = $arItem['QUANTITY'] / $measureRatio;

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

    public static function getCompareListAction()
    {
        $result = array(
            'items' => array()
        );

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

    public function clearCompareAction()
    {
        unset($_SESSION[self::SESSION_COMPARE_LIST]);

        return array('success' => true);
    }

    public static function compareAction($productId, $mode)
    {
        Main\Loader::includeModule('iblock');

        $IBLOCK_ID = \CIBlockElement::GetIBlockByID($productId);

        if (!$IBLOCK_ID) {
            return ['error' => Loc::getMessage('LOCAL_LIB_SHOP_PRODUCT_NOT_FOUND')];
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
                $result["error"] = Loc::getMessage('LOCAL_LIB_SHOP_ERR_ADD_TO_BASKET');
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

    public static function addCompareAction(int $id)
    {
        return self::compareAction($id, self::COMPARE_ADD);
    }

    public function removeCompareAction(int $id)
    {
        return self::compareAction($id, self::COMPARE_DELETE);
    }

    public function toggleCompareAction(int $id)
    {
        return self::compareAction($id, self::COMPARE_TOGGLE);
    }

    public function makeOrderItemsAction(array $itemsId, array $params)
    {
        global $USER;

        Main\Loader::includeModule('sale');

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

        foreach ($itemsId as $itemId) {
            $basketItem = $userBasket->getItemById($itemId);

            if (!$basketItem) {
                $this->addError(new Main\Error('Basket item id ' . $itemId . ' not found'));
                return false;
            }


            $basketItem->setCollection($orderBasket);
            $orderBasket->addItem($basketItem);
        }

        $arPersonType = Sale\PersonType::load($siteId);
        if (empty($arPersonType)) {
            $this->addError(new Main\Error('Sale person type is empty, for site ' . $siteId));
            return false;
        }

        $arPersonTypeId = array_keys($arPersonType);

        if (isset($params['PERSON_TYPE_ID'])) {
            $personTypeId = $params['PERSON_TYPE_ID'];
            if (!in_array($personTypeId, $arPersonTypeId)) {
                $this->addError(new Main\Error('Person type ' . $personTypeId . ' not found'));
                return false;
            }
        } else {
            $personTypeId = reset($arPersonTypeId);
        }

        $order->setPersonTypeId($personTypeId);

        foreach ($params as $k => $v) {
            if (preg_match('/^ORDER_PROP_(\d+)$/', $k, $m)) {
                $property = $order->getPropertyCollection()->getItemByOrderPropertyId($m[1]);
                if ($property) {
                    $property->setValue($v);
                }
            } else {

                switch ($k) {
                    case 'USER_NAME':
                        $property = $order->getPropertyCollection()->getPayerName();
                        if ($property) {
                            $property->setValue($v);
                        }

                        $property = $order->getPropertyCollection()->getProfileName();
                        if ($property) {
                            $property->setValue($v);
                        }
                        break;
                    case 'USER_PHONE':
                        $property = $order->getPropertyCollection()->getPhone();
                        if ($property) {
                            $property->setValue($v);
                        }
                        break;
                    case 'USER_EMAIL':
                        $property = $order->getPropertyCollection()->getUserEmail();
                        if ($property) {
                            $property->setValue($v);
                        }
                        break;
                }
            }
        }

        $order->doFinalAction(true);

        $orderResult = $order->save();
        if (!$orderResult->isSuccess()) {
            $this->addErrors($orderResult->getErrors());
            return false;
        }

        return ['id' => $order->getId()];
    }

    public static function recalculateBasketAction()
    {
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
        if (!empty($showPrices['BASKET'])) {
            foreach ($showPrices['BASKET'] as $basketCode => $data) {
                $basketItem = $basket->getItemByBasketCode($basketCode);
                if ($basketItem instanceof Sale\BasketItemBase) {
                    $basketItem->setFieldNoDemand('BASE_PRICE', $data['SHOW_BASE_PRICE']);
                    $basketItem->setFieldNoDemand('PRICE', $data['SHOW_PRICE']);
                    $basketItem->setFieldNoDemand('DISCOUNT_PRICE', $data['SHOW_DISCOUNT']);
                }
            }
        }

        $data = [
            'ITEMS' => []
        ];

        foreach ($basket as $basketItem) {
            /* @var \Bitrix\Sale\BasketItemBase $basketItem */
            $data['ITEMS'][] = $basketItem->getFieldValues();
        }

        $data['BASE_PRICE'] = $basket->getBasePrice();
        $data['PRICE'] = $basket->getPrice();
        $data['VAT_RATE'] = $basket->getVatRate();
        $data['WEIGHT'] = $basket->getWeight();

        $userBasket->save();

        unset($_SESSION['SALE_USER_BASKET_PRICE']);

        return $data;
    }

    public function getFavoriteListAction()
    {
        return ['items' => FavoriteTable::getUserFavoriteArray(), 'success' => true];
    }

    public function addFavoriteAction(int $id)
    {
        FavoriteTable::saveProductId($id);

        return ['success' => true];
    }

    public function removeFavoriteAction(int $id)
    {
        FavoriteTable::removeProductId($id);

        return ['success' => true];
    }

    public function toggleFavoriteAction(int $id)
    {
        $productId = Assert::expectIntegerPositive($id);

        if (in_array($productId, FavoriteTable::getUserFavoriteArray())) {
            FavoriteTable::removeProductId($productId);
        } else {
            FavoriteTable::saveProductId($productId);
        }

        return ['success' => true];
    }

    public function quickViewAction(int $productId)
    {
        global $APPLICATION;

        ob_start();

        $iblockId = Utils::getCatalogIblockId();
        $arIblock = \CIBlock::GetByID($iblockId)->Fetch();

        $APPLICATION->IncludeComponent(
            "bitrix:catalog.element",
            "main",
            array(
                "ACTION_VARIABLE" => "action",
                "ADDITIONAL_FILTER_NAME" => "elementFilter",
                "ADD_ELEMENT_CHAIN" => "N",
                "ADD_PROPERTIES_TO_BASKET" => "Y",
                "ADD_SECTIONS_CHAIN" => "N",
                "BACKGROUND_IMAGE" => "-",
                "BASKET_URL" => "/personal/basket.php",
                "BROWSER_TITLE" => "-",
                "CACHE_GROUPS" => "Y",
                "CACHE_TIME" => "36000000",
                "CACHE_TYPE" => "A",
                "CHECK_SECTION_ID_VARIABLE" => "N",
                "COMPATIBLE_MODE" => "N",
                "CONVERT_CURRENCY" => "N",
                "DETAIL_URL" => "",
                "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                "DISPLAY_COMPARE" => "N",
                "ELEMENT_CODE" => "",
                "ELEMENT_ID" => $productId,
                "HIDE_NOT_AVAILABLE_OFFERS" => "N",
                "IBLOCK_ID" => $iblockId,
                "IBLOCK_TYPE" => $arIblock['IBLOCK_TYPE_ID'],
                "LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
                "LINK_IBLOCK_ID" => "",
                "LINK_IBLOCK_TYPE" => "",
                "LINK_PROPERTY_SID" => "",
                "MESSAGE_404" => "",
                "META_DESCRIPTION" => "-",
                "META_KEYWORDS" => "-",
                "OFFERS_FIELD_CODE" => array("", ""),
                "OFFERS_LIMIT" => "0",
                "OFFERS_SORT_FIELD" => "sort",
                "OFFERS_SORT_FIELD2" => "id",
                "OFFERS_SORT_ORDER" => "asc",
                "OFFERS_SORT_ORDER2" => "desc",
                "PARTIAL_PRODUCT_PROPERTIES" => "N",
                "PRICE_CODE" => array("BASE"),
                "PRICE_VAT_INCLUDE" => "Y",
                "PRICE_VAT_SHOW_VALUE" => "N",
                "PRODUCT_ID_VARIABLE" => "id",
                "PRODUCT_PROPS_VARIABLE" => "prop",
                "PRODUCT_QUANTITY_VARIABLE" => "quantity",
                "SECTION_CODE" => "",
                "SECTION_ID" => "",
                "SECTION_ID_VARIABLE" => "SECTION_ID",
                "SECTION_URL" => "",
                "SEF_MODE" => "N",
                "SET_BROWSER_TITLE" => "N",
                "SET_CANONICAL_URL" => "N",
                "SET_LAST_MODIFIED" => "N",
                "SET_META_DESCRIPTION" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_STATUS_404" => "N",
                "SET_TITLE" => "N",
                "SET_VIEWED_IN_COMPONENT" => "N",
                "SHOW_404" => "N",
                "SHOW_DEACTIVATED" => "N",
                "SHOW_PRICE_COUNT" => "1",
                "SHOW_SKU_DESCRIPTION" => "N",
                "STRICT_SECTION_CHECK" => "N",
                "USE_ELEMENT_COUNTER" => "N",
                "USE_GIFTS_DETAIL" => "Y",
                "USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
                "USE_MAIN_ELEMENT_SECTION" => "N",
                "USE_PRICE_COUNT" => "N",
                "USE_PRODUCT_QUANTITY" => "N",
                "QUICK_VIEW" => "Y",
            ),false,
            array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function basketViewAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("bitrix:sale.basket.basket", "quick", array(
            "COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
            "COLUMNS_LIST" => array(
                0 => "NAME",
                1 => "DISCOUNT",
                2 => "PRICE",
                3 => "QUANTITY",
                4 => "SUM",
                5 => "PROPS",
                6 => "DELETE",
                7 => "DELAY",
            ),
            "AJAX_MODE" => "N",
            "AJAX_OPTION_JUMP" => "N",
            "AJAX_OPTION_STYLE" => "Y",
            "AJAX_OPTION_HISTORY" => "N",
            "PATH_TO_ORDER" => "/personal/order/make/",
            "HIDE_COUPON" => "N",
            "QUANTITY_FLOAT" => "N",
            "PRICE_VAT_SHOW_VALUE" => "Y",
            "TEMPLATE_THEME" => "site",
            "SET_TITLE" => "Y",
            "AJAX_OPTION_ADDITIONAL" => "",
            "OFFERS_PROPS" => array(
                0 => "SIZES_SHOES",
                1 => "SIZES_CLOTHES",
                2 => "COLOR_REF",
            ),
        ),
            false
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function basketItemCommentAction(int $basketId, $comment)
    {
        Main\Loader::includeModule('sale');

        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(true), SITE_ID);

        $basketItem = $basket->getItemById($basketId);
        if (!$basketItem)
        {
            $this->addError(new Main\Error('Basket item not found'));
            return false;
        }

        $fields = [
            'NAME' => Loc::getMessage('LOCAL_LIB_SHOP_BASKET_PROP_COMMENT'),
            'CODE' => 'COMMENT',
            'VALUE' => $comment,
            'SORT' => 1000,
        ];

        $found = false;

        foreach ($basketItem->getPropertyCollection() as $basketProperty) {
            /* @var BasketPropertyItem $basketProperty */

            if ($basketProperty->getField('CODE') == 'COMMENT')
            {
                $basketProperty->setFields($fields);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $basketItem->getPropertyCollection()->createItem()->setFields($fields);
        }

        $basket->save();

        return array('success'=>true);
    }

    public function showAuthFormAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("devbx:simple", "auth", array(),
            false, array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function showRegistrationFormAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("devbx:simple", "registration", array(),
            false, array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function sendAuthSMSAction($phone, $siteId)
    {
        global $USER;

        if ($USER->IsAuthorized())
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_YOU_ALREADY_LOGGED')));
            return false;
        }

        $phone = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

        if (empty($phone))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_IS_EMPTY')));
            return false;
        }

        $arRow = Main\UserPhoneAuthTable::getList(array(
            'filter' => [
                '=PHONE_NUMBER' => $phone,
            ],
            'select' => array(
                'USER_ACTIVE' => 'USER.ACTIVE'
            ),
        ))->fetch();

        if (!$arRow)
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_NOT_FOUND_BY_PHONE')));
            return false;
        }

        if ($arRow['USER_ACTIVE'] != 'Y')
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_BLOCKED')));
            return false;
        }

        $remoteResult = SMSCodeTable::sendPhoneCode($phone, static::EVENT_SEND_SMS_AUTH_CODE, $siteId);
        if (!$remoteResult->isSuccess())
        {
            $this->addErrors($remoteResult->getErrors());
            return false;
        }

        return array('success'=>true,'countdown'=>SMSCodeTable::TIME_LIMIT);
    }

    public function sendRegistrationSMSAction($phone, $email, $siteId)
    {
        global $USER;

        if ($USER->IsAuthorized())
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_YOU_ALREADY_LOGGED')));
            return false;
        }

        $phone = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

        if (empty($phone))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_IS_EMPTY'), 'PHONE'));
            return false;
        }

        $arRow = Main\UserPhoneAuthTable::getList(array(
            'filter' => [
                '=PHONE_NUMBER' => $phone,
            ],
            'select' => array(
                'USER_ACTIVE' => 'USER.ACTIVE'
            ),
        ))->fetch();

        if ($arRow)
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_ALREADY_REGISTERED'), 'PHONE'));
            return false;
        }

        if (!check_email($email))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_INVALID_EMAIL'), 'EMAIL'));
            return false;
        }

        $remoteResult = SMSCodeTable::sendPhoneCode($phone, static::EVENT_SEND_SMS_REGISTER_CODE, $siteId);
        if (!$remoteResult->isSuccess())
        {
            $this->addErrors($remoteResult->getErrors());
            return false;
        }

        return array('success'=>true,'countdown'=>SMSCodeTable::TIME_LIMIT);
    }

    public function verifyRegistrationCodeAction($phone, $email, $code, $siteId)
    {
        global $USER;

        if ($USER->IsAuthorized())
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_YOU_ALREADY_LOGGED')));
            return false;
        }

        $phone = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

        if (empty($phone))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_IS_EMPTY'), 'PHONE'));
            return false;
        }

        $arRow = Main\UserPhoneAuthTable::getList(array(
            'filter' => [
                '=PHONE_NUMBER' => $phone,
            ],
            'select' => array(
                'USER_ACTIVE' => 'USER.ACTIVE'
            ),
        ))->fetch();

        if ($arRow)
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_ALREADY_REGISTERED'), 'PHONE'));
            return false;
        }

        if (!check_email($email))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_INVALID_EMAIL'), 'EMAIL'));
            return false;
        }

        if (!SMSCodeTable::checkCode($phone, $code))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_INVALID_SMS_CODE'), 'CODE'));
            return false;
        }

        $newUser = new \CUser();

        $USER_PASSWORD = $USER_CONFIRM_PASSWORD = \CAllUser::GeneratePasswordByPolicy(static::NEW_USER_GROUP);

        $arFields = array(
            'LOGIN' => $phone,
            'NAME' => '',
            "PASSWORD" => $USER_PASSWORD,
            "CONFIRM_PASSWORD" => $USER_CONFIRM_PASSWORD,
            'EMAIL' => $email,
            'PHONE_NUMBER' => $phone,
            'ACTIVE' => 'Y',
            'SITE_ID' => SITE_ID,
            'LANGUAGE_ID' => LANGUAGE_ID,
            "USER_IP" => $_SERVER["REMOTE_ADDR"],
            "USER_HOST" => @gethostbyaddr($_SERVER["REMOTE_ADDR"]),
            'GROUP_ID' => static::NEW_USER_GROUP,
        );

        $ID = $newUser->Add($arFields);
        if (!$ID) {
            $this->addError(new Main\Error($newUser->LAST_ERROR));
            return false;
        }

        $USER->Authorize($ID, true);

        return array('success'=>true);
    }

    public function verifyAuthCodeAction($phone, $code)
    {
        global $USER;

        if ($USER->IsAuthorized())
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_YOU_ALREADY_LOGGED')));
            return false;
        }

        $phone = Main\UserPhoneAuthTable::normalizePhoneNumber($phone);

        if (empty($phone))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_PHONE_IS_EMPTY'), 'PHONE'));
            return false;
        }

        $arRow = Main\UserPhoneAuthTable::getList(array(
            'filter' => [
                '=PHONE_NUMBER' => $phone,
            ],
            'select' => array(
                'USER_ID',
                'USER_ACTIVE' => 'USER.ACTIVE'
            ),
        ))->fetch();

        if (!$arRow)
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_NOT_FOUND_BY_PHONE')));
            return false;
        }

        if ($arRow['USER_ACTIVE'] != 'Y')
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_USER_BLOCKED')));
            return false;
        }

        if (!SMSCodeTable::checkCode($phone, $code))
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_INVALID_SMS_CODE'), 'CODE'));
            return false;
        }

        $arGroup = Main\UserGroupTable::getList([
            'filter' => [
                '=USER_ID' => $arRow['USER_ID'],
                '=GROUP_ID' => 1,
            ],
        ])->fetch();
        if ($arGroup)
        {
            $this->addError(new Main\Error(Main\Localization\Loc::getMessage('LOCAL_LIB_SHOP_ADMIN_NOW_ALLOWED_SMS_AUTHORIZATION')));
            return false;
        }

        $USER->Authorize($arRow['USER_ID']);

        return array('success'=>true);
    }
}