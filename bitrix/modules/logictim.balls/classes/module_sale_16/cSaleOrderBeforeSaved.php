<?php
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
IncludeModuleLangFile(__FILE__);

class cSaleOrderBeforeSaved
{
	public static function SaleOrderBeforeSaved($order)
	{
		if(COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N') == 'B')
			return;
			
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		
		//Polya zakaza
		$is_new = $order->isNew();
		$order_sum = $order->getPrice();
		$user_id = $values["USER_ID"];
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
		$UserBallance = cHelper::UserBallance($user_id);
		
		//GET ORDER PROPERTIES
		$props = $order->getPropertyCollection();
		foreach($props as $prop) {
			$fields = $prop->GetFields();
			$values = $fields->GetValues();
			//Get property LOGICTIM_PAYMENT_BONUS
			if($values["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
			{
				$pay_bonus = $values["VALUE"];
				$payBonusPropId = $values["ORDER_PROPS_ID"];
			}
		}
		
		
		if($is_new && $UserBonusSystemDostup == 'Y' && $UserBallance > 0 && $pay_bonus > 0):
		
			$basket = $order->getBasket();
			$order_sum = $order->getPrice(); //Stoimost zakaza
			$order_cart_sum = $basket->getPrice(); //Stoimost korzini
			$order_delivery_sum = $order->getDeliveryPrice(); //Stoimost dostavki
						
			//---PROVERKA NA MIN I MAX BONUS---//
				//Esli zapret na oplatu bonusami tovarov so skidkoi
				if(COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y')
				{
					//Poluchaem sostav zakaza
					$basketItems = $basket->getBasketItems();
					$arItems = array();
					foreach ($basket as $basketItem) {
						$arItem = array();
						$arItem["PRODUCT_ID"] = $basketItem->getProductId();
						$arItem["NAME"] = $basketItem->getField('NAME');
						$arItem["QUANTITY"] = $basketItem->getQuantity();
						$arItem["BASE_PRICE"] = $basketItem->getField('BASE_PRICE');
						$arItem["PRICE"] = $basketItem->getPrice();
						$arItem["DISCOUNT_PRICE"] = $basketItem->getField('DISCOUNT_PRICE');
						$arItems[$arItem["PRODUCT_ID"]] = $arItem;
					}
				}
				
				//Proveryaem dopustimie min i max oplatu bonusami
				$minBonusSum = cHelper::MinBonusSum($order_sum, $order_cart_sum, $order_delivery_sum);
				$maxBonusSum = cHelper::MaxBonusSum($order_sum, $order_cart_sum, $order_delivery_sum, $arItems);
				if($pay_bonus < $minBonusSum || $pay_bonus > $UserBallance)
				{
					$PayBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
					$PayBonusProp->setValue(0);
					return;
				}
				if($maxBonusSum != 0 && $pay_bonus > $maxBonusSum)
				{
					$PayBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
					$PayBonusProp->setValue(0);
					return;
				}
			//---PROVERKA YA MIN I MAX BONUS---//
			
			
			
			
			$PayBonusToDiscount = COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
			
			if($PayBonusToDiscount == 'Y'):
				
				
				$bonusItemsInfo = cHelperCalc::CartBonus($arItems);
			
				foreach($basket as $key => $basketItem):
					$item = $basketItem->getFields();
					$arItem = $item->getValues();
					
					if(
						//Esli zapret na oplatu bonusami tovarov so skidkoy, a tovar so skidkoy, to korrektiruem summu korzini dlya rasschetov
						COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y' && $arItem["DISCOUNT_PRICE"] > 0
						||
						//Zapret oplati tovara bonusami iz svoystv
						$bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] == 1
					)
						$order_cart_sum = $order_cart_sum - $arItem["PRICE"]*$arItem["QUANTITY"];
					
					
				endforeach;
				
				//Esli bonusami oplacheno bol'she, chem stoimost korzini, to vichitaem ih iz dostavki
				if($pay_bonus > $order_cart_sum)
				{
					$bonusPayDelivery = $pay_bonus - $order_cart_sum;
					$bonusPayCart = $order_cart_sum;
					$newDeliveryPrice = $order_delivery_sum - $bonusPayDelivery;
				}
				else
					$bonusPayCart = $pay_bonus;
					
				//Raskidivaem skidku po tovaram v korzine
				foreach($basket as $basketItem):
				
					$item = $basketItem->getFields();
					$arItem = $item->getValues();
					
					//Esli zapret na oplatu bonusami tovarov so skidkoy, a tovar so skidkoy, to propuskaem ego
					if(COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y' && $arItem["DISCOUNT_PRICE"] > 0)
						continue;
						
					//Zapret oplati tovara bonusami iz svoystv
					if($bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] == 1)
						continue;
						
					$productPart = $arItem["PRICE"] * $arItem["QUANTITY"] * 100 / $order_cart_sum; //Procentnoe sootnoshenie pozicii tovara s obshhej summoj
					$discountPlus = $bonusPayCart * $productPart / 100; //Skol'ko rublej nado pripljusovat' k skidke tovara
					$newPrice = $arItem["PRICE"] - $discountPlus / $arItem["QUANTITY"]; //Cena s uchetom raskidanooj skidki
					$newDiscount = $arItem["DISCOUNT_PRICE"] + $discountPlus / $arItem["QUANTITY"]; //Skidka s uchetom dobavlennoj novoj skidki
					
					$basketItem->setField('CUSTOM_PRICE', 'Y');
					$basketItem->setField('PRICE', $newPrice);
					$basketItem->setField('BASE_PRICE', $arItem["BASE_PRICE"]);
					$basketItem->setField('DISCOUNT_PRICE', $newDiscount);
					
				endforeach;
				
				//Esli bonusami oplacheno bol'she, chem stoimost korzini, to vichitaem ih iz dostavki
				if($pay_bonus > $order_cart_sum):
					$shipmentCollection = $order->getShipmentCollection();
					foreach($shipmentCollection as $shipment):
						if(!$shipment->isSystem()) {
							$shipment->setFields(array(
								'PRICE_DELIVERY' => $newDeliveryPrice, 'BASE_PRICE_DELIVERY' => $newDeliveryPrice
							));
						}
					endforeach;
				endif;
				
				cSaleOrderBeforeSaved::ChangeSumForPayment_1($order, $order_sum, $pay_bonus);
				
			else:
				
				cSaleOrderBeforeSaved::ChangeSumForPayment_2($order, $order_sum, $pay_bonus);
				
				//ADD PAYMENT BONUS
				if($pay_bonus > 0)
				{
					//Get ID of paysystem Bonus
					$paySystemId = cHelper::PaySystemBonusId();
					$paymentCollection = $order->getPaymentCollection();
					$paymentBonus = $paymentCollection->createItem(\Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId));
					$paymentBonus->setField("SUM", $pay_bonus);
					$paymentBonus->setField("PAID", "Y");
				}
			
			endif;
			
		endif; //if($is_new && $UserBonusSystemDostup == 'Y' && $UserBallance > 0)
		
	}
	
	public static function ChangeSumForPayment_1($order, $order_sum, $pay_bonus)
	{
		//CHANGE SUM FOR PAYMENT
		//GET paymentCollection
		$paymentCollection = $order->getPaymentCollection();
		
		if(COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N') == 'Y')
			$pay_bonus = $order_sum - $order->getPrice();
					
		foreach($paymentCollection as $arPayment):
			$fields = $arPayment->GetFields();
			$values = $fields->GetValues();
			
			if($values["SUM"] && $pay_bonus > 0 && $values["SUM"] > $order->getPrice()) //bitrix c versii 17.8-18.0 stal sam pereschitivat oplatu, poetomu dobavleno uslovie  $values["SUM"] > $order->getPrice()
			{
				if($arPayment->isInner())
				{
					if($pay_bonus + $values["SUM"] > $order_sum)
					{
						$new_pay_sum = $values["SUM"] - ($pay_bonus + $values["SUM"] - $order_sum);
						$arPayment->setField("SUM", $new_pay_sum);
					}
					else
					{ continue;}
				}
				else
				{
					$new_pay_sum = $values["SUM"] - $pay_bonus;
					if($new_pay_sum < 0) 
						$new_pay_sum = 0;
					$arPayment->setField("SUM", $new_pay_sum);
					if($new_pay_sum <= 0) 
						$arPayment->setField("PAID", "Y");
				}
			}
		endforeach;
	}
	
	public static function ChangeSumForPayment_2($order, $order_sum, $pay_bonus)
	{
		//CHANGE SUM FOR PAYMENT
		//GET paymentCollection
		$paymentCollection = $order->getPaymentCollection();
		
					
		foreach($paymentCollection as $arPayment):
			$fields = $arPayment->GetFields();
			$values = $fields->GetValues();
			
			if($values["SUM"] && $pay_bonus > 0)
			{
				if($arPayment->isInner())
				{
					if($pay_bonus + $values["SUM"] > $order_sum)
					{
						$new_pay_sum = $values["SUM"] - ($pay_bonus + $values["SUM"] - $order_sum);
						$arPayment->setField("SUM", $new_pay_sum);
					}
					else
					{ continue;}
				}
				else
				{
					$new_pay_sum = $values["SUM"] - $pay_bonus;
					if($new_pay_sum < 0) 
						$new_pay_sum = 0;
					$arPayment->setField("SUM", $new_pay_sum);
					if($new_pay_sum <= 0) 
						$arPayment->setField("PAID", "Y");
				}
			}
		endforeach;
	}
}
?>
