<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web;

class DevBxDeliveryCalc extends CBitrixComponent {
    var $originalParameters;
    var $ajaxAction;

    const CALC_DELIVERY_NO = 'N';
    const CALC_DELIVERY_ALL = 'A';
    const CALC_DELIVERY_CALCULABLE = 'C';
    const CALC_DELIVERY_IMMEDIATELY = 'I';

    public function onPrepareComponentParams($arParams)
    {
        $arParams['ACTION_VARIABLE'] = isset($arParams['ACTION_VARIABLE']) ? trim($arParams['ACTION_VARIABLE']) : '';
        if ($arParams['ACTION_VARIABLE'] == '') {
            $arParams['ACTION_VARIABLE'] = 'delivery-calc-action';
        }

        $this->ajaxAction = $arParams['DEVBX_AJAX_ACTION'] == 'Y';
        unset($arParams['DEVBX_AJAX_ACTION']);

        if (!in_array($arParams['CALC_DELIVERY'], [
            static::CALC_DELIVERY_NO,
            static::CALC_DELIVERY_ALL,
            static::CALC_DELIVERY_CALCULABLE,
            static::CALC_DELIVERY_IMMEDIATELY
        ])) {
            $arParams['CALC_DELIVERY'] = static::CALC_DELIVERY_NO;
        }

        if (!isset($arParams['ALLOW_DELIVERY_ID']) || !is_array($arParams['ALLOW_DELIVERY_ID']))
        {
            $arParams['ALLOW_DELIVERY_ID'] = array('all');
        }

        $this->originalParameters = $arParams;

        $arParams['PRODUCT_ID'] = intval($arParams['PRODUCT_ID']);
        if ($arParams['PRODUCT_ID']<=0)
            unset($arParams['PRODUCT_ID']);

        return parent::onPrepareComponentParams($arParams);
    }

    protected function getLocationCode()
    {
        if ($this->ajaxAction && $this->request['user_location_code'])
        {
            $locationCode = $this->request['user_location_code'];
        } else {
            $locationCode = $this->request->getCookie('user_location_code');
        }

        if ($locationCode)
        {
            $arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
                'select' => array('ID','CODE'),
                'filter' => array(
                    'TYPE.CODE' => "CITY",
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    'CODE' => $locationCode,
                ),
            ))->fetch();

            if ($arLocation)
            {
                if ($this->ajaxAction)
                {
                    $cookie = (new Web\Cookie('user_location_code', $locationCode, time() + 60 * 60 * 24 * 30 * 12))
                        ->setPath('/')
                        ->setSecure(false)
                        ->setHttpOnly(false);

                    Bitrix\Main\Context::getCurrent()->getResponse()->addCookie($cookie);
                }

                return $arLocation['CODE'];
            }
        }

        $cityName = $this->request->getCookie('user_city');
        if (empty($cityName))
        {
            $cityName = Loc::getMessage('DEVBX_DELIVERY_DEFAULT_CITY_NAME');
        }

        $arLocation = \Bitrix\Sale\Location\LocationTable::getList(array(
            'select' => array('ID','CODE'),
            'filter' => array(
                'TYPE.CODE' => "CITY",
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
                'NAME.NAME' => $cityName,
            ),
        ))->fetch();

        if (!$arLocation)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_CALC_LOCATION_NOT_FOUND',array('#NAME#'=>$cityName));
            return false;
        }

        return $arLocation['CODE'];
    }

    protected function getPersonTypeId()
    {
        $arPerson = \Bitrix\Sale\PersonTypeTable::getList([
            'filter' => [
                '=LID' => $this->getSiteId(),
                '=ACTIVE' => 'Y',
            ],
            'order' => array('SORT'=>'ASC')
        ])->fetch();

        if (!$arPerson)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_PERSON_TYPE_NOT_FOUND');
            return false;
        }

        return $arPerson['ID'];
    }

    protected function initBasket(\Bitrix\Sale\BasketBase $basket)
    {
        $arProduct = \Bitrix\Catalog\ProductTable::getById($this->arParams['PRODUCT_ID'])->fetch();
        if (!$arProduct)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_PRODUCT_NOT_FOUND');
            return false;
        }

        //$arProduct['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_OFFER

        $catalogBase = \Bitrix\Catalog\GroupTable::getBasePriceType();
        if (!$catalogBase)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_BASE_PRICE_NOT_FOUND');
            return false;
        }

        $arPrice = \Bitrix\Catalog\Model\Price::getList([
            'filter' => [
                '=PRODUCT_ID' => $arProduct['ID'],
                '=CATALOG_GROUP_ID' => $catalogBase['ID'],
                '=QUANTITY_FROM' => null,
                '=QUANTITY_TO' => null,
            ],

        ])->fetch();

        if (!$arPrice)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_PRODUCT_PRICE_NOT_FOUND');
            return false;
        }

        /*
        $arProduct['WIDTH'] = 1000;
        $arProduct['HEIGHT'] = 100;
        $arProduct['LENGTH'] = 100;
        $arProduct['WEIGHT'] = 100;
        */

        $basketFields = array(
            'PRODUCT_ID' => $arProduct['ID'],
            'QUANTITY' => 1,
            'MODULE' => 'catalog',
            //'PRODUCT_PROVIDER_CLASS' => \Bitrix\Catalog\Product\Basket::getDefaultProviderName(),
            'CURRENCY' => $arPrice['CURRENCY'],
            'BASE_PRICE' => $arPrice['PRICE'],
            'PRODUCT_PRICE_ID' => $arPrice['ID'],
            'PRICE' => $arPrice['PRICE'],
            'DISCOUNT_PRICE' => 0,
            'WEIGHT' => $arProduct['WEIGHT'],
            'DIMENSIONS' => array(
                'WIDTH' => $arProduct['WIDTH'],
                'HEIGHT' => $arProduct['HEIGHT'],
                'LENGTH' => $arProduct['LENGTH'],
            ),
            'NOTES' => $catalogBase['NAME_LANG'],
            'PRICE_TYPE_ID' => $catalogBase['ID'],
        );

        if ($arProduct['MEASURE']<=0)
        {
            $measure = \CCatalogMeasure::getDefaultMeasure(true, true);
            $basketFields['MEASURE_NAME'] = $measure['~SYMBOL_RUS'];
            $basketFields['MEASURE_CODE'] = $measure['CODE'];
            unset($measure);
        } else {
            $measureIterator = \CCatalogMeasure::getList(
                [],
                ['ID' => $arProduct['MEASURE']],
                false,
                false,
                ['ID', 'SYMBOL_RUS', 'CODE']
            );
            $measure = $measureIterator->Fetch();
            unset($measureIterator);
            if (!empty($measure))
            {
                $basketFields['MEASURE_NAME'] = $measure['SYMBOL_RUS'];
                $basketFields['MEASURE_CODE'] = $measure['CODE'];
            }
        }

        $basketItem = $basket->createItem($basketFields['MODULE'], $basketFields['PRODUCT_ID']);
        if (!$basketItem)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_FAILED_CREATE_BASKET_ITEM');
            return false;
        }

        unset($basketFields['MODULE']);

        $result = $basketItem->setFields($basketFields);
        if (!$result->isSuccess())
        {
            $this->arResult['ERROR'] = implode(', ', $result->getErrorMessages());
            return false;
        }

        return true;
    }

    const CACHE_TTL = 60*60;
    public function prepareData($calcType)
    {
        if ($calcType == static::CALC_DELIVERY_NO)
            return true;

        $locationCode = $this->getLocationCode();
        if ($locationCode === false)
            return false;

        $cacheId = 'devbx_delivery_calc_'.md5(serialize(array($this->arParams, $locationCode, func_get_args())));

        $cache = new \CPHPCache();

        if ($cache->InitCache(static::CACHE_TTL, $cacheId, "/devbx"))
        {
            $data = $cache->GetVars();
            if (!empty($data) && is_array($data))
            {
                $this->arResult = array_merge($this->arResult, $data);
                return true;
            }
        }

        $data = array();

        $order = \Bitrix\Sale\Order::create($this->getSiteId(), \Bitrix\Sale\Fuser::getId());

        $personTypeId = $this->getPersonTypeId();
        if ($personTypeId === false)
            return false;

        $order->setPersonTypeId($personTypeId);

        $data['LOCATION_PATH'] = [];

        $iterator = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode($locationCode, [
            'select' => [
                'TYPE_CODE' => 'TYPE.CODE',
                'LANG_NAME' => 'NAME.NAME',
            ],
            'filter' => [
                '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
            ]
        ]);

        while ($arLoc = $iterator->fetch())
        {
            $data['LOCATION_PATH'][] = $arLoc;
        }

        $property = $order->getPropertyCollection()->getDeliveryLocation();
        if (!$property)
        {
            $this->arResult['ERROR'] = Loc::getMessage('DEVBX_DELIVERY_ERR_DELIVERY_LOCATION_PROPERTY_NOT_FOUND');
            return false;
        }

        $property->setValue($locationCode);

        $order->setBasket(Bitrix\Sale\Basket::create($this->getSiteId()));

        if (!$this->initBasket($order->getBasket()))
            return false;

        $shipment = $order->getShipmentCollection()->createItem();
        $shipment->setField('CURRENCY', $order->getCurrency());

        foreach ($order->getBasket() as $basketItem)
        {
            /* @var \Bitrix\Sale\BasketItem $basketItem */

            $shipmentItem = $shipment->getShipmentItemCollection()->createItem($basketItem);
            $shipmentItem->setQuantity($basketItem->getQuantity());
        }

        $services = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);

        $storeId = array();
        $data['DELIVERY'] = array();
        $data['STORE_LIST'] = array();

        foreach ($services as $deliveryId => $deliveryObj)
        {
            $check = true;

            switch ($calcType)
            {
                case 'I':
                    $check = $deliveryObj->isCalculatePriceImmediately();
                    break;
                case 'C':
                    $check = !$deliveryObj->isCalculatePriceImmediately();
            }

            $check |= in_array('all', $this->arParams['ALLOW_DELIVERY_ID']) || in_array($deliveryId, $this->arParams['ALLOW_DELIVERY_ID']);

            if (!$check)
                continue;

            $shipment->setField('DELIVERY_ID', $deliveryId);
            $calcResult = $deliveryObj->calculate($shipment);
            if ($calcResult->isSuccess())
            {
                $arItem = array(
                    'ID' => $deliveryObj->getId(),
                    'NAME' => $deliveryObj->getName(),
                    'PRICE' => $calcResult->getPrice(),
                    'PRICE_FORMATTED' => CCurrencyLang::CurrencyFormat($calcResult->getPrice(), $deliveryObj->getCurrency()),
                    'DELIVERY_PRICE' => $calcResult->getDeliveryPrice(),
                    'DELIVERY_PRICE_FORMATTED' => CCurrencyLang::CurrencyFormat($calcResult->getDeliveryPrice(), $deliveryObj->getCurrency()),
                    'PERIOD_TYPE' => $calcResult->getPeriodType(),
                    'PERIOD_FROM' => $calcResult->getPeriodFrom(),
                    'PERIOD_TO' => $calcResult->getPeriodTo(),
                    'PERIOD_DESCRIPTION' => $calcResult->getPeriodDescription(),
                    'DESCRIPTION' => $calcResult->getDescription(),
                    'STORE_LIST' => \Bitrix\Sale\Delivery\ExtraServices\Manager::getStoresList($deliveryId),
                );

                if (!empty($arItem['STORE_LIST']))
                {
                    $storeId = array_unique(array_merge($storeId, $arItem['STORE_LIST']));
                }

                if ($deliveryObj->isProfile())
                {
                    if (!isset($data['DELIVERY'][$deliveryObj->getParentId()]))
                    {
                        $data['DELIVERY'][$deliveryObj->getParentId()] = array(
                            'ID' => $deliveryObj->getParentId(),
                            'HAS_PROFILE' => true,
                            'NAME' => $deliveryObj->getParentService()->getName(),
                            'ITEMS' => array(),
                        );
                    }

                    $data['DELIVERY'][$deliveryObj->getParentId()]['ITEMS'][$deliveryObj->getId()] = $arItem;
                } else {
                    if (!isset($data['DELIVERY'][$deliveryObj->getId()]))
                    {
                        $data['DELIVERY'][$deliveryObj->getId()] = array(
                            'ID' => $deliveryObj->getId(),
                            'HAS_PROFILE' => false,
                            'NAME' => $deliveryObj->getName(),
                            'ITEMS' => array(),
                        );
                    }

                    $data['DELIVERY'][$deliveryObj->getId()]['ITEMS'][$deliveryObj->getId()] = $arItem;
                }
            }
        }

        if (!empty($storeId))
        {
            $data['STORE_LIST'] = \Bitrix\Catalog\StoreTable::getList([
                'filter' => [
                    '=ID' => $storeId,
                    '=ACTIVE' => 'Y',
                ],
                'select' => [
                    '*',
                    'UF_*',
                ],
            ])->fetchAll();
        }

        $cache->StartDataCache();
        $cache->EndDataCache($data);

        $this->arResult = array_merge($this->arResult, $data);

        return true;
    }

    protected function deliveryCalcAction()
    {
        $this->prepareData($this->request['type']);
    }

    public static function prepareArrayJS($data)
    {
        $result = [];

        foreach ($data as $k=>$v)
        {
            $k = lcfirst(\Bitrix\Main\Text\StringHelper::snake2camel($k));

            if (isset($result[$k]))
                throw new \Bitrix\Main\SystemException('camel key already exists "'.$k.'"');

            if (is_array($v))
            {
                $v = static::prepareArrayJS($v);
            }

            if ($v instanceof \Bitrix\Main\Type\Date)
            {
                $v = $v->getTimestamp();
            }

            if (is_object($v))
            {
                if (is_callable(array($v, 'toString')))
                {
                    $v = call_user_func(array($v, 'toString'));
                } else {
                    $v = 'object ('.get_class($v).')';
                }
            }

            $result[$k] = $v;
        }

        return $result;
    }

    public function sendJsonAnswer($data)
    {
        $response = \Bitrix\Main\Context::getCurrent()->getResponse();
        $response->addHeader("Content-Type", "application/json; charset=UTF-8");

        $response->flush(Bitrix\Main\Web\Json::encode($data));
        die();
    }

    public function executeComponent()
    {
        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        Loader::includeModule('iblock');
        Loader::includeModule('catalog');
        Loader::includeModule('currency');
        Loader::includeModule('sale');

        if ($this->ajaxAction)
        {
            $action = $this->request->getPost($arParams['ACTION_VARIABLE']);

            if (!empty($action)) {
                if (is_callable(array($this, $action . 'Action'))) {
                    try {
                        $this->{$action . 'Action'}();
                    } catch (\Exception $e) {
                        $arResult['ERROR'] = $e->getMessage();
                    }
                }

                $this->sendJsonAnswer($arResult);
            }

        } else {
            $arResult['AJAX_PATH'] = $this->getPath() . '/ajax.php';
            $arResult['ACTION_VARIABLE'] = $arParams['ACTION_VARIABLE'];

            $signer = new \Bitrix\Main\Security\Sign\Signer;
            $arResult['SIGNED_PARAMS'] = $signer->sign(base64_encode(serialize($this->originalParameters)), 'devbx.delivery.calc');
            $arResult['SIGNED_TEMPLATE'] = $signer->sign($this->getTemplateName(), 'devbx.delivery.calc');

            $this->prepareData($arParams['CALC_DELIVERY']);

            $arResult['JS_DATA'] = static::prepareArrayJS($arResult);

            $this->includeComponentTemplate();
        }
    }

}