<?php

namespace Local\Lib\Controller;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Sale,
    Bitrix\Sale\Basket,
    Bitrix\Sale\BasketItem,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\ShipmentItem,
    Bitrix\Sale\ShipmentCollection,
    Bitrix\Sale\PaySystem,
    Bitrix\Sale\Payment,
    Bitrix\Sale\Services\Company;

Loc::loadMessages(__FILE__);

class Oren extends Main\Engine\Controller
{
    const GIFT_PAY_SYSTEM = 5;
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

    public function showDolyameFormAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("devbx:simple", "dolyame", array(),
            false, array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function showPromoRegistrationFormAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("devbx:simple", "promo-registration", array(),
            false, array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public static function initShipment(\Bitrix\Sale\Order $order)
    {
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        $shipment->setField('CURRENCY', $order->getCurrency());

        /** @var BasketItem $item */
        foreach ($order->getBasket() as $item)
        {
            /** @var ShipmentItem $shipmentItem */
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }

        return $shipment;
    }

    public static function initDelivery(Shipment $shipment, $deliveryId)
    {
        $deliveryId = intval($deliveryId);
        $arDeliveryServiceAll = \Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
        /** @var ShipmentCollection $shipmentCollection */
        $shipmentCollection = $shipment->getCollection();
        $order = $shipmentCollection->getOrder();

        if (!empty($arDeliveryServiceAll) && $deliveryId>0)
        {
            if (array_key_exists($deliveryId, $arDeliveryServiceAll))
            {
                $deliveryObj = $arDeliveryServiceAll[$deliveryId];
            }
            else
            {
                $deliveryObj = reset($arDeliveryServiceAll);
            }

            if ($deliveryObj->isProfile())
            {
                $name = $deliveryObj->getNameWithParent();
            }
            else
            {
                $name = $deliveryObj->getName();
            }

            $shipment->setFields(array(
                'DELIVERY_ID' => $deliveryObj->getId(),
                'DELIVERY_NAME' => $name,
                'CURRENCY' => $order->getCurrency()
            ));

            /*
            $deliveryExtraServices = $this->arUserResult['DELIVERY_EXTRA_SERVICES'];
            if (is_array($deliveryExtraServices) && !empty($deliveryExtraServices[$deliveryObj->getId()]))
            {
                $shipment->setExtraServices($deliveryExtraServices[$deliveryObj->getId()]);
                $deliveryObj->getExtraServices()->setValues($deliveryExtraServices[$deliveryObj->getId()]);
            }
            */

            $res = $shipmentCollection->calculateDelivery();
            if (!$res->isSuccess())
            {
                $errMessages = '';

                if (count($res->getErrorMessages()) > 0)
                {
                    foreach ($res->getErrorMessages() as $message)
                    {
                        $errMessages .= $message.'<br />';
                    }
                }
                else
                {
                    $errMessages = 'Ошибка расчета';
                }

                $shipment->setFields(array(
                    'MARKED' => 'Y',
                    'REASON_MARKED' => $errMessages
                ));
            }
        }
        else
        {
            $service = \Bitrix\Sale\Delivery\Services\Manager::getById(\Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());
            $shipment->setFields(array(
                'DELIVERY_ID' => $service['ID'],
                'DELIVERY_NAME' => $service['NAME'],
                'CURRENCY' => $order->getCurrency()
            ));
        }
    }

    public static function getInnerPayment(\Bitrix\Sale\Order $order)
    {
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection() as $payment)
        {
            if ($payment->getPaymentSystemId() == PaySystem\Manager::getInnerPaySystemId())
                return $payment;
        }

        return null;
    }

    public static function initPayment(\Bitrix\Sale\Order $order, $paySystemId)
    {
        $paySystemId = intval($paySystemId);
        $innerPaySystemId = PaySystem\Manager::getInnerPaySystemId();
        $paymentCollection = $order->getPaymentCollection();
        $innerPayment = null;

        $sumToSpend = 0; //это для оплаты с внутреннего счета

        $remainingSum = $order->getPrice() - $paymentCollection->getSum();
        if ($remainingSum > 0 || $order->getPrice() == 0)
        {
            /** @var Payment $innerPayment */
            $innerPayment = static::getInnerPayment($order);
            /** @var Payment $extPayment */
            $extPayment = $paymentCollection->createItem();
            $extPayment->setField('SUM', $remainingSum);
            $arPaySystemServices = PaySystem\Manager::getListWithRestrictions($extPayment);
            // we already checked restrictions for inner pay system (could be different prices by price restrictions)
            unset($arPaySystemServices[$innerPaySystemId]);

            if (array_key_exists($paySystemId, $arPaySystemServices))
            {
                $arPaySystem = $arPaySystemServices[$paySystemId];
            }
            else
            {
                reset($arPaySystemServices);

                if (key($arPaySystemServices) == $innerPaySystemId)
                {
                    if ($sumToSpend > 0)
                    {
                        if (count($arPaySystemServices) > 1)
                        {
                            next($arPaySystemServices);
                        }
                        elseif (empty($innerPayment))
                        {
                            $remainingSum = $remainingSum > $sumToSpend ? $sumToSpend : $remainingSum;
                            $extPayment->setField('SUM', $remainingSum);
                        }
                        else
                        {
                            $extPayment->delete();
                        }

                        $remainingSum = $order->getPrice() - $paymentCollection->getSum();
                        if ($remainingSum > 0)
                        {
                            $order->setFields(array(
                                'MARKED' => 'Y',
                                'REASON_MARKED' => 'Недостаточно средств'
                            ));
                        }
                    }
                    else
                    {
                        unset($arPaySystemServices[$innerPaySystemId]);
                    }
                }

                $arPaySystem = current($arPaySystemServices);
            }

            if (!empty($arPaySystem))
            {
                $extPayment->setFields(array(
                    'PAY_SYSTEM_ID' => $arPaySystem["ID"],
                    'PAY_SYSTEM_NAME' => $arPaySystem["NAME"]
                ));
            }
            else
            {
                $extPayment->delete();
            }
        }
    }

    public function orderGiftAction($offerId, $nominal, $customNominal, array $form)
    {
        global $USER;

        Main\Loader::includeModule('iblock');
        Main\Loader::includeModule('catalog');
        Main\Loader::includeModule('sale');

        if ($customNominal && $nominal<5000)
        {
            $this->addError(new Main\Error('Номинал должен быть не меньше 5000'));
            return false;
        }

        $iblockId = \Local\Lib\Utils::getIblockIdByCode('GIFTS');
        if (!$iblockId)
        {
            $this->addError(new Main\Error('Инфоблок не найден'));
            return false;
        }

        $arFilter = array(
            'IBLOCK_ID' => $iblockId,
            '=CODE' => 'gift',
            '=ACTIVE' => 'Y',
        );

        $obElement = \CIBlockElement::GetList([], $arFilter)->GetNextElement();
        if (!$obElement)
        {
            $this->addError(new Main\Error('Товар для сертификата не найден'));
            return false;
        }

        $arElement = $obElement->GetFields();
        $arElement['PROPERTIES'] = $obElement->GetProperties();

        $arOffers = \CCatalogSku::getOffersList($arElement['ID'],0,array(), array());

        $arElement['OFFERS'] = $arOffers[$arElement['ID']];

        if (!array_key_exists($offerId, $arElement['OFFERS']))
        {
            $this->addError(new Main\Error('Товар для сертификата не найден'));
            return false;
        }

        $userId = $USER->GetID() ? $USER->GetID() : \CSaleUser::GetAnonymousUserID();

        $order = \Bitrix\Sale\Order::create(SITE_ID, $userId);

        $order->isStartField();
        $order->setField('STATUS_ID', \Bitrix\Sale\OrderStatus::getInitialStatus());

        $personTypes = \Bitrix\Sale\PersonType::load(SITE_ID);

        $firstPerson = reset($personTypes);

        $order->setPersonTypeId($firstPerson['ID']);

        $rsProducts = \CCatalogProduct::GetList(
            array(),
            array('ID' => $offerId),
            false,
            false,
            array(
                'ID',
                'CAN_BUY_ZERO',
                'QUANTITY_TRACE',
                'QUANTITY',
                'WEIGHT',
                'WIDTH',
                'HEIGHT',
                'LENGTH',
                'TYPE',
                'MEASURE'
            )
        );
        if (!($arCatalogProduct = $rsProducts->Fetch()))
        {
            return false;
        }

        $arFields = array(
            "MODULE" => "catalog",
            "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
            "QUANTITY" => 1,
        );

        if (!$productProvider = \CSaleBasket::GetProductProvider(array(
            'MODULE' => $arFields["MODULE"],
            'PRODUCT_PROVIDER_CLASS' => $arFields["PRODUCT_PROVIDER_CLASS"]))
        ) return false;

        $providerParams = array(
            'PRODUCT_ID' => $offerId,
            'QUANTITY' => $arFields["QUANTITY"],
            'RENEWAL' => 'N'
        );

        $arCallbackPrice = $productProvider::GetProductData($providerParams);

        if (isset($arCallbackPrice['RESULT_PRICE'])) {
            $arCallbackPrice['BASE_PRICE'] = $arCallbackPrice['RESULT_PRICE']['BASE_PRICE'];
            $arCallbackPrice['PRICE'] = $arCallbackPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
            $arCallbackPrice['DISCOUNT_PRICE'] = $arCallbackPrice['RESULT_PRICE']['DISCOUNT'];
            $arCallbackPrice['CURRENCY'] = $arCallbackPrice['RESULT_PRICE']['CURRENCY'];
        } else {
            if (!isset($arCallbackPrice['BASE_PRICE']))
                $arCallbackPrice['BASE_PRICE'] = $arCallbackPrice['PRICE'] + $arCallbackPrice['DISCOUNT_PRICE'];
        }

        $arFields = array_merge($arFields, array(
            "PRODUCT_PRICE_ID" => $arCallbackPrice["PRODUCT_PRICE_ID"],
            "BASE_PRICE" => $arCallbackPrice["BASE_PRICE"],
            "PRICE" => $arCallbackPrice["PRICE"],
            "DISCOUNT_PRICE" => $arCallbackPrice["DISCOUNT_PRICE"],
            "CURRENCY" => $arCallbackPrice["CURRENCY"],
            "NOTES" => $arCallbackPrice["NOTES"],
            "VAT_INCLUDED" => $arCallbackPrice['VAT_INCLUDED'],
            "VAT_RATE" => $arCallbackPrice['VAT_RATE'],
            "WEIGHT" => $arCatalogProduct["WEIGHT"],
            "DIMENSIONS" => serialize(array(
                "WIDTH" => $arCatalogProduct["WIDTH"],
                "HEIGHT" => $arCatalogProduct["HEIGHT"],
                "LENGTH" => $arCatalogProduct["LENGTH"]
            )),
            "TYPE" => ($arCatalogProduct["TYPE"] == \CCatalogProduct::TYPE_SET) ? \CCatalogProductSet::TYPE_SET : NULL,
            "MEASURE_NAME" => $arCatalogProduct['MEASURE_NAME'],
            "MEASURE_CODE" => $arCatalogProduct['MEASURE_CODE'],
            "NAME" => $arElement["~NAME"],
            "DETAIL_PAGE_URL" => $arElement["~DETAIL_PAGE_URL"],
            "PRODUCT_XML_ID" => $arElement["~XML_ID"],
        ));

        $basket = \Bitrix\Sale\Basket::create($order->getSiteId());

        $order->setBasket($basket);

        $basketItem = $basket->createItem("catalog", $offerId);

        if ($customNominal)
        {
            $arFields = array_merge($arFields, array(
                'CUSTOM_PRICE' => 'Y',
                'BASE_PRICE' => $nominal,
                'PRICE' => $nominal,
                'CURRENCY' => 'RUB',
            ));
        }

        $basketItem->initFields($arFields);

        $shipment = static::initShipment($order);

        $order->doFinalAction(true);

        static::initDelivery($shipment, 0);
        static::initPayment($order, static::GIFT_PAY_SYSTEM);

        $formFields = [
            'senderName',
            'senderSecondName',
            'senderLastName',
            'senderEmail',
            'senderPhone',
            'receiverName',
            'receiverSecondName',
            'receiverLastName',
            'receiverEmail',
            'receiverPhone',
            'message'
        ];

        $propertyId = 10000;
        $propertySort = 100;

        foreach ($formFields as $fieldName)
        {
            $propertyId++;
            $propertySort+=100;

            if (empty($form[$fieldName]))
                continue;

            $sysFieldName = Main\Text\StringHelper::strtoupper(Main\Text\StringHelper::camel2snake($fieldName));

            $prop = $order->getPropertyCollection()->createItem(array(
                'NAME' => Loc::getMessage('ORDER_PROPERTY_GIFT_FIELD_'.$sysFieldName),
                'CODE' => $sysFieldName,
                'SETTINGS' => array(
                    'ID' => $propertyId,
                    'NAME' => Loc::getMessage('ORDER_PROPERTY_GIFT_FIELD_'.$sysFieldName),
                    'CODE' => $sysFieldName,
                    'TYPE' => 'STRING',
                    'PROPS_GROUP_ID' => 0,
                    'PERSON_TYPE_ID' => '',
                    'DESCRIPTION' => '',
                    'REQUIRED' => 'Y',
                    'DEFAULT_VALUE' => '',
                    'SORT' => $propertySort,
                    'USER_PROPS' => 'N',
                    'IS_LOCATION' => 'N',
                    'IS_EMAIL' => 'N',
                    'IS_PROFILE_NAME' => 'N',
                    'IS_PAYER' => 'N',
                    'IS_LOCATION4TAX' => 'N',
                    'IS_FILTERED' => 'N',
                    'IS_ZIP' => 'N',
                    'IS_PHONE' => 'N',
                    'IS_ADDRESS' => 'N',
                    'IS_ADDRESS_FROM' => 'N',
                    'IS_ADDRESS_TO' => 'N',
                    'ACTIVE' => 'Y',
                    'UTIL' => 'N',
                    'INPUT_FIELD_LOCATION' => 0,
                    'MULTIPLE' => 'N',
                    'ENTITY_TYPE' => 'ORDER',
                ),
            ));

            $prop->setField('VALUE', $form[$fieldName]);
        }

        $result = $order->save();

        if (!$result->isSuccess())
        {
            $this->addErrors($result->getErrors());
            return false;
        }

        $orderId = $result->getId();

        $order->setField('MARKED', 'N');
        $order->save();

        return array('success'=>true, 'id' => $orderId);
    }

    public function showAuthQuestionFormAction()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent("devbx:simple", "auth-question", array(),
            false, array('HIDE_ICONS'=>'Y')
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
   }

   public function showQuestionFormAction()
   {
       global $APPLICATION;

       if ($_REQUEST['AJAX_CALL'] != 'Y')
           ob_start();
       //PUBLIC_AJAX_MODE

       $APPLICATION->IncludeComponent(
           "devbx:form",
           "popup-question",
           array(
               "ACTION_VARIABLE" => "form-action",
               "AJAX_LOAD_FORM" => "N",
               "AJAX_MODE" => "Y",
               "AJAX_OPTION_ADDITIONAL" => "",
               "AJAX_OPTION_HISTORY" => "N",
               "AJAX_OPTION_JUMP" => "N",
               "AJAX_OPTION_STYLE" => "Y",
               "CHECK_AJAX_SESSID" => "N",
               "DEFAULT_FIELDS" => array("", ""),
               "FORM_ID" => "3",
               "READ_ONLY_FIELDS" => array("", ""),
           )
       );

       $html = ob_get_clean();

       $html .= $APPLICATION->EndBufferContent();

       return array(
           'content' => $html,
           'js' => Main\Page\Asset::getInstance()->getJs(),
           'css' => Main\Page\Asset::getInstance()->getCss(),
       );
   }

}