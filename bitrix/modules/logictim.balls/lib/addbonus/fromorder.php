<?
namespace Logictim\Balls\AddBonus;


use Bitrix\Sale;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FromOrder {
	public static function BonusFromOrder($order, $params = array())
	{
		
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		
		$basket = $order->getBasket();
		
		//Payments
		$paymentCollection = $order->getPaymentCollection();
		$arPayments = array();
		foreach($paymentCollection as $payment):
			$paymentId = $payment->getPaymentSystemId();
			$arPayments[$paymentId] = array("ID"=>$paymentId, "SUM"=>$payment->getSum(), "NAME"=>$payment->getPaymentSystemName(), "IS_PAYED"=>$payment->isPaid());
		endforeach;
		
		//Delivery
		$deliveryCollection = $order->getDeliverySystemId();
		$arDelivery = array();
		foreach($deliveryCollection as $delivery):
			$arDelivery[$delivery] = array("ID"=>$delivery);
		endforeach;
		
		$discountData = $order->getDiscount()->getApplyResult();
		$arOrderParams = array(
								"ORDER_ID" => $order->getId(),
								"ORDER_NUM" => $values["ACCOUNT_NUMBER"],
								"SITE_ID" => $order->getSiteId(),
								"USER_ID" => $order->getUserId(),
								"ORDER_SUM" => $order->getPrice(),
								"CART_SUM" => $basket->getPrice(),
								"DELIVERY_SUM" => $order->getDeliveryPrice(),
								"PERSON_TYPE_ID" => $order->getPersonTypeId(),
								"CURRENCY" => $order->getCurrency(),
								"DISCOUNT" => $order->getDiscountPrice(),
								"DISCOUNT_DATA" => $discountData["DISCOUNT_LIST"],
								"PAYMENTS" => $arPayments,
								"DELIVERY" => $arDelivery,
								);
								
		
		//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = \Logictim\Balls\Helpers::IblokOperationsId();
		//Opredelyaem ID ibfobloka s operaciyami ojidaniya
			$iblokWaitId = \Logictim\Balls\Helpers::IblokWaitId();
			
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = \Logictim\Balls\Helpers::OperationsType();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE" ibfobloka s operaciyami ojidaniya
			$operationsTypeWait = \Logictim\Balls\Helpers::OperationsTypeWait();
			
		//Proveryaem, net li uzhe operacii nachisleniya po etomu zakazu
		$dbOperations = \CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokOperationsId, $iblokWaitId), "ACTIVE"=>"Y", "PROPERTY_ORDER_ID" => $arOrderParams["ORDER_ID"], "PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_ORDER'], $operationsTypeWait['ADD_FROM_ORDER'], $operationsType['MINUS_FROM_ORDER_CANCEL'])), false, Array("nPageSize"=>PHP_INT_MAX), array());
		while($Op = $dbOperations->GetNextElement())
		{
			 $OperationAddFields = $Op->GetFields();
			 $operationAddProps = $Op->GetProperties();
			 $lastOperationType = $operationAddProps["OPERATION_TYPE"]["VALUE_XML_ID"];
		}
		
		if(!empty($OperationAddFields) && $lastOperationType != 'MINUS_FROM_ORDER_CANCEL')
			return;
			
		//Poluchaem sostav zakaza
		$arItems = array();
		foreach ($basket as $basketItem):
			$arItem = array();
			$arItem["PRODUCT_ID"] = $basketItem->getProductId();
			$arItem["BASKET_ID"] = $basketItem->getId();
			$arItem["NAME"] = $basketItem->getField('NAME');
			$arItem["QUANTITY"] = $basketItem->getQuantity();
			$arItem["BASE_PRICE"] = $basketItem->getField('BASE_PRICE');
			$arItem["PRICE"] = $basketItem->getPrice();
			$arItem["DISCOUNT_PRICE"] = $basketItem->getField('DISCOUNT_PRICE');
			$arItems[$arItem["BASKET_ID"]] = $arItem;
		endforeach;
		
		$arBonus = \Logictim\Balls\CalcBonus::getBonus($arItems, array("TYPE"=>'cart', "PROFILE_TYPE"=>'order', "EVENT_ORDER_PAID"=>$params["EVENT_ORDER_PAID"], "ORDER"=>$arOrderParams));
		
		$detailInfo = '';
		foreach($arItems as $arItem):
			$item = $arBonus["ITEMS"][$arItem["BASKET_ID"]];
			if($item["BONUS_TYPE"] == 'percent')
			{
				if($item["ROUND_TYPE"] == 'UNIT')
					$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = ('.$item["BONUS"].'% x '.$item["PRICE"]["DISCOUNT_PRICE"].') x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
				else //POSITION
					$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].'% x '.$item["PRICE"]["DISCOUNT_PRICE"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
			}
			if($item["BONUS_TYPE"] == 'bonus')
				$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
		endforeach;
		if(isset($arBonus["FIX_ORDER_BONUS"]) && $arBonus["FIX_ORDER_BONUS"] > 0)
			$detailInfo .= 'Bonus from order = '.$arBonus["FIX_ORDER_BONUS"];
		
		if($arBonus["ALL_BONUS"] > 0):
			$arFields = array(
								"ADD_BONUS" => $arBonus["ALL_BONUS"],
								"USER_ID" => $arOrderParams["USER_ID"],
								"OPERATION_TYPE" => 'ADD_FROM_ORDER',
								"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_ADD_USER").$arOrderParams["ORDER_NUM"],
								"ORDER_ID" => $arOrderParams["ORDER_ID"],
								"DETAIL_TEXT" => $detailInfo,
								"ACTIVE_AFTER" => $arBonus["PROFILE"]["ACTIVE_AFTER"],
								"ACTIVE_AFTER_TYPE" => $arBonus["PROFILE"]["ACTIVE_AFTER_TYPE"],
								"DEACTIVE_AFTER" => $arBonus["PROFILE"]["DEACTIVE_AFTER"],
								"DEACTIVE_AFTER_TYPE" => $arBonus["PROFILE"]["DEACTIVE_AFTER_TYPE"],
								"MAIL_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_ORDER_ADD",
                                      "CUSTOM_FIELDS" => array(
                                                              "ORDER_NUM" => $values["ACCOUNT_NUMBER"],
                                                              )
                                        ),
								"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_ORDER_ADD_SMS",
                                      "CUSTOM_FIELDS" => array(
                                                              "ORDER_NUM" => $values["ACCOUNT_NUMBER"],
                                                              )
                                        ),
							);
			\logictimBonusApi::AddBonus($arFields);
			
			
		endif;
		
		\Logictim\Balls\AddBonus\FromReferalOrder::BonusFromReferalOrder($arOrderParams, $arItems);
	}
}


?>