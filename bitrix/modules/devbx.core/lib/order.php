<?
namespace DevBx\Core;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main,
    Bitrix\Main\Result,
    Bitrix\Sale,
    Bitrix\Sale\Basket,
    Bitrix\Sale\BasketItem,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\ShipmentItem,
    Bitrix\Sale\ShipmentCollection,
    Bitrix\Sale\PaySystem,
    Bitrix\Sale\Payment,
    Bitrix\Sale\Services\Company;

Loader::includeModule("iblock");
Loader::includeModule("catalog");

if (!Loader::includeModule("sale"))
    return;

class Order
{
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
                    $errMessages = Loc::getMessage("DEVBX_ORDER_DELIVERY_CALCULATE_ERROR");
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
                                'REASON_MARKED' => Loc::getMessage("DEVBX_INNER_PAYMENT_BALANCE_ERROR")
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

    public static function initEntityCompanyIds(\Bitrix\Sale\Order $order)
    {
        $paymentCollection = $order->getPaymentCollection();
        if ($paymentCollection)
        {
            /** @var Payment $payment */
            foreach ($paymentCollection as $payment)
            {
                if ($payment->isInner())
                    continue;

                $payment->setField('COMPANY_ID', \Bitrix\Sale\Services\Company\Manager::getAvailableCompanyIdByEntity($payment));
                if ($payment->getField('COMPANY_ID') > 0)
                {
                    $responsibleGroups = \Bitrix\Sale\Internals\CompanyResponsibleGroupTable::getCompanyGroups($payment->getField('COMPANY_ID'));
                    if (!empty($responsibleGroups) && is_array($responsibleGroups))
                    {
                        $usersList = array();
                        foreach ($responsibleGroups as $groupId)
                        {
                            $usersList = array_merge($usersList, \CGroup::GetGroupUser($groupId));
                        }

                        if (!empty($usersList) && is_array($usersList))
                        {
                            $usersList = array_unique($usersList);
                            $responsibleUserId = $usersList[array_rand($usersList)];

                            /** @var Main\Entity\Event $event */
                            $event = new Main\Event('sale', 'OnSaleComponentBeforePaymentSetResponsibleUserId', array(
                                'ENTITY' => $payment,
                                'VALUE' => $responsibleUserId,
                            ));
                            $event->send();

                            if ($event->getResults())
                            {
                                $result = new Result();
                                /** @var Main\EventResult $eventResult */
                                foreach($event->getResults() as $eventResult)
                                {
                                    if($eventResult->getType() == Main\EventResult::SUCCESS)
                                    {
                                        if ($eventResultData = $eventResult->getParameters())
                                        {
                                            if (isset($eventResultData['VALUE']) && $eventResultData['VALUE'] != $responsibleUserId)
                                            {
                                                $responsibleUserId = $eventResultData['VALUE'];
                                            }
                                        }
                                    }
                                }
                            }

                            $payment->setField('RESPONSIBLE_ID', $responsibleUserId);
                        }
                    }
                }
            }
        }

        $shipmentCollection = $order->getShipmentCollection();
        if ($shipmentCollection)
        {
            /** @var Shipment $shipment */
            foreach ($shipmentCollection as $shipment)
            {
                if ($shipment->isSystem())
                    continue;

                $shipment->setField('COMPANY_ID', Company\Manager::getAvailableCompanyIdByEntity($shipment));

                if ($shipment->getField('COMPANY_ID') > 0)
                {
                    $responsibleGroups = Sale\Internals\CompanyResponsibleGroupTable::getCompanyGroups($shipment->getField('COMPANY_ID'));
                    if (!empty($responsibleGroups) && is_array($responsibleGroups))
                    {
                        $usersList = array();
                        foreach ($responsibleGroups as $groupId)
                        {
                            $usersList = array_merge($usersList, \CGroup::GetGroupUser($groupId));
                        }

                        if (!empty($usersList) && is_array($usersList))
                        {
                            $usersList = array_unique($usersList);
                            $responsibleUserId = $usersList[array_rand($usersList)];

                            /** @var Main\Entity\Event $event */
                            $event = new Main\Event('sale', 'OnSaleComponentBeforeShipmentSetResponsibleUserId', array(
                                'ENTITY' => $shipment,
                                'VALUE' => $responsibleUserId,
                            ));
                            $event->send();

                            if ($event->getResults())
                            {
                                $result = new Result();
                                /** @var Main\EventResult $eventResult */
                                foreach($event->getResults() as $eventResult)
                                {
                                    if($eventResult->getType() == Main\EventResult::SUCCESS)
                                    {
                                        if ($eventResultData = $eventResult->getParameters())
                                        {
                                            if (isset($eventResultData['VALUE']) && $eventResultData['VALUE'] != $responsibleUserId)
                                            {
                                                $responsibleUserId = $eventResultData['VALUE'];
                                            }
                                        }
                                    }
                                }
                            }

                            $shipment->setField('RESPONSIBLE_ID', $responsibleUserId);
                        }
                    }
                }
            }
        }
    }

    public static function getProfileValues($profileId, $userId = false)
    {
        global $USER;

        if (!$userId)
            $userId = $USER->GetID();

        $arFilter = array(
            "USER_ID"=>$userId,
            "ID"=>$profileId
        );

        $arResult = \Bitrix\Sale\Internals\UserPropsTable::getList(array("filter"=>$arFilter))->fetch();
        if (!$arResult)
            return false;

        $dbRes = \Bitrix\Sale\Internals\UserPropsValueTable::getList(array("filter"=>array("USER_PROPS_ID"=>$arResult["ID"])));
        while ($arRes = $dbRes->fetch())
        {
            $arResult["VALUES"][$arRes["ORDER_PROPS_ID"]] = $arRes;
        }

        return $arResult;
    }

    public static function orderLoadProfile(\Bitrix\Sale\Order $order, $arProfile)
    {
        $propertyCollection = $order->getPropertyCollection();
        /** @var \Bitrix\Sale\PropertyValue $property */
        foreach ($propertyCollection as $property) {
            if ($property->isUtil())
                continue;

            $arProperty = $property->getProperty();

            if (array_key_exists($arProperty["ID"], $arProfile["VALUES"]))
            {
                $property->setValue($arProfile["VALUES"][$arProperty["ID"]]["VALUE"]);
            }
        }

        return true;
    }

    public static function orderLoadProfileFromRequest(\Bitrix\Sale\Order $order, $request = false)
    {
        if (!$request)
        {
            $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        }

        $propertyCollection = $order->getPropertyCollection();
        /** @var \Bitrix\Sale\PropertyValue $property */
        foreach ($propertyCollection as $property) {
            if ($property->isUtil())
                continue;

            $arProperty = $property->getProperty();

            if (isset($request["properties"][$arProperty["ID"]])) {
                $val = trim($request["properties"][$arProperty["ID"]]);
                $property->setValue($val);
                continue;
            }

            if ($arProperty["IS_PROFILE_NAME"] == "Y") {
                $val = trim($request["properties"]["NAME"]);
                if ($val) {
                    $property->setValue($val);
                    continue;
                }
            }

            if ($arProperty["IS_EMAIL"] == "Y") {
                $val = trim($request["properties"]["EMAIL"]);
                if ($val) {
                    $property->setValue($val);
                    continue;
                }
            }

            if ($arProperty["IS_PHONE"] == "Y") {
                $val = trim($request["properties"]["PHONE"]);
                if ($val) {
                    $property->setValue($val);
                    continue;
                }
            }

            if ($arProperty["IS_LOCATION"] == "Y") {
                $val = trim($request["properties"]["LOCATION"]);
                if ($val) {
                    $property->setValue($val);
                    continue;
                }
            }

            if (strlen($property->getValue()) == 0)
                $property->setValue($arProperty["DEFAULT_VALUE"]);
        }
    }

    public static function saveOrderProfile(\Bitrix\Sale\Order $order)
    {
        $arProfile = array(
            "USER_ID" => $order->getUserId(),
            "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
            "PERSON_TYPE_ID" => $order->getPersonTypeId(),
        );

        $propertyCollection = $order->getPropertyCollection();
        /** @var \Bitrix\Sale\PropertyValue $property */
        foreach ($propertyCollection as $property) {
            if ($property->isUtil())
                continue;

            $arProperty = $property->getProperty();

            if ($arProperty["IS_PROFILE_NAME"] == "Y") {
                $arProfile["NAME"] = $property->getValue();
            }
        }

        $res = \Bitrix\Sale\Internals\UserPropsTable::add($arProfile);
        if (!$res->isSuccess())
            return false;

        $profileId = $res->getId();

        foreach ($propertyCollection as $property) {
            if ($property->isUtil())
                continue;

            $value = $property->getValue();

            if (strlen($value) == 0)
                continue;

            $arProperty = $property->getProperty();

            $arFields = array(
                "USER_PROPS_ID" => $profileId,
                "ORDER_PROPS_ID" => $arProperty["ID"],
                "NAME" => $arProperty["NAME"],
                "VALUE" => $value,
            );

            $res = \Bitrix\Sale\Internals\UserPropsValueTable::add($arFields);
            if (!$res->isSuccess())
                return false;
        }

        return true;
    }

    public static function updateOrderProfile($profileId, \Bitrix\Sale\Order $order)
    {
        $arFilter = array(
            "=ID" => $profileId,
            "=USER_ID" => $order->getUserId(),
            "=PERSON_TYPE_ID" => $order->getPersonTypeId(),
        );

        $profile = \Bitrix\Sale\Internals\UserPropsTable::getList(array("filter"=>$arFilter))->fetch();
        if (!$profile)
            return false;

        $propertyCollection = $order->getPropertyCollection();
        /** @var \Bitrix\Sale\PropertyValue $property */

        foreach ($propertyCollection as $property) {
            if ($property->isUtil())
                continue;

            $value = $property->getValue();

            if (strlen($value) == 0)
                continue;

            $arProperty = $property->getProperty();

            $arFilter = array(
                "USER_PROPS_ID" => $profileId,
                "ORDER_PROPS_ID" => $arProperty["ID"],
            );

            $dbValue = \Bitrix\Sale\Internals\UserPropsValueTable::getList(array("filter"=>$arFilter))->fetch();
            if ($dbValue)
            {
                $arFields = array(
                    "NAME" => $arProperty["NAME"],
                    "VALUE" => $value,
                );

                \Bitrix\Sale\Internals\UserPropsValueTable::update($dbValue["ID"], $arFields);
            } else
            {
                $arFields = array(
                    "USER_PROPS_ID" => $profileId,
                    "ORDER_PROPS_ID" => $arProperty["ID"],
                    "NAME" => $arProperty["NAME"],
                    "VALUE" => $value,
                );

                $res = \Bitrix\Sale\Internals\UserPropsValueTable::add($arFields);
                if (!$res->isSuccess())
                    return false;
            }
        }

        return true;
    }

}