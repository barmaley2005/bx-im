<?
namespace Logictim\Balls\Api;

\CModule::IncludeModule("sale");

class OrderPayBonus
{
	public static function CalculateOrderPayBonus($orderId)
	{
		$order = \Bitrix\Sale\Order::load($orderId);
		$basket = $order->getBasket();
		$cartSum = $basket->getPrice();
		
		$arItems = array();
		foreach($basket as $basketItem):
			$arItem = array();
			$arItem["PRODUCT_ID"] = $basketItem->getProductId();
			$arItem["BASKET_ID"] = $basketItem->getId();
			$arItem["NAME"] = $basketItem->getField('NAME');
			$arItem["QUANTITY"] = $basketItem->getQuantity();
			$arItem["BASE_PRICE"] = $basketItem->getField('BASE_PRICE');
			$arItem["PRICE"] = $basketItem->getPrice();
			$arItem["DISCOUNT_PRICE"] = $basketItem->getField('DISCOUNT_PRICE');
			$arItem["PRICE_POSITION"] = $basketItem->getFinalPrice();
			$arItems[$arItem["BASKET_ID"]] = $arItem;
		endforeach;
		
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
		
		$arOrderParams = array(
								"USER_ID" => $order->getUserId(),
								"ORDER_SUM" => $cartSum+$order->getDeliveryPrice(),
								"CART_SUM" => $cartSum,
								"DELIVERY_SUM" => $order->getDeliveryPrice(),
								"PERSON_TYPE_ID" => $order->getPersonTypeId(),
								"CURRENCY" => $order->getCurrency(),
								"PAYMENTS" => $arPayments,
								"DELIVERY" => $arDelivery,
								"ORDER_ID" => $orderId
								);
		$arPayBonus = \Logictim\Balls\CalcBonus::OrderBonusPayment($arItems, $arOrderParams);
		
		return $arPayBonus;
	}
	
	public static function PayOrderBonus($orderId, $pay_bonus)
	{
		$order = \Bitrix\Sale\Order::load($orderId);
		$user_id = $order->getUserId();
		$is_new = $order->isNew();
		$is_new = 1;
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		$order_num = $values["ACCOUNT_NUMBER"];
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
		
		//GET ORDER PROPERTIES
		$props = $order->getPropertyCollection();
		foreach($props as $prop):
			$propFields = $prop->GetFields();
			$propValues = $propFields->GetValues();
			if($propValues["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
			{
				//$pay_bonus = $propValues["VALUE"];
				$payBonusPropId = $propValues["ORDER_PROPS_ID"];
			}
		endforeach;
		
		$discountData = $order->getDiscount()->getApplyResult();
		$arOrderParams = array(
								"ORDER_ID" => $orderId,
								"ORDER_NUM" => $values["ACCOUNT_NUMBER"],
								"SITE_ID" => $order->getSiteId(),
								"USER_ID" => $user_id,
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
		$UserBallance = \Logictim\Balls\Helpers::UserBallance($user_id);
		
		if($is_new && $UserBallance > 0 && $pay_bonus > 0):
		
			//Korzina zakaza
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
				$arItem["POSITION_FINAL_PRICE"] = $basketItem->getFinalPrice();
				$arItems[$arItem["BASKET_ID"]] = $arItem;
			endforeach;
			
			$arOrderParams["PAY_BONUS"] = $pay_bonus;
			$arPayBonus = \Logictim\Balls\CalcBonus::OrderBonusPayment($arItems, $arOrderParams);
			
			if($arPayBonus["PAY_BONUS"] > 0)
			{
				$minBonusSum = $arPayBonus["MIN_ORDER_PAY"];
				$maxBonusSum = $arPayBonus["MAX_ORDER_PAY"];
				$pay_bonus = $pay_bonus_save = $arPayBonus["PAY_BONUS"];
				$bonusPayCart = $arPayBonus["PAY_CART"];
				$bonusPayDelivery = $arPayBonus["PAY_DELIVERY"];
				$newDeliveryPrice = $arPayBonus["NEW_DELIVERY_PRICE"];
			}
			else
			{
				$PayBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
				$PayBonusProp->setValue(0);
				return;
			}
			
			$PayBonusToDiscount = \COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
			if($PayBonusToDiscount == 'Y' || $PayBonusToDiscount == 'B'):
			
				//Raskidivaem skidku po tovaram v korzine	
				if($bonusPayCart > 0):
				
					//Gotovim ceni zaranee iz-za baga bitrix
					foreach($basket as $basketItem):
						$item = $basketItem->getFields();
						$arItem = $item->getValues();
						$arItem["BASKET_ID"] = $basketItem->getId();
						
						//Esli tovar nel'zya oplatit' bonusami
						if($arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["ADD_BONUS"] > 0 || $arPayBonus["PAY_PRODUCTS"]["PROFILE"]["NO_PRODUCT_CONDITIONS"] == 'Y')
						{
							$canPayProduct = $arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["ADD_BONUS"];
							$canPayProductUnit = $arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["ADD_BONUS_UNIT"];
							$canPayQuantity = $arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["QUANTITY"];
						}
						else
							continue;
						
						//Procentnoe sootnoshenie pozicii tovara s obshhej summoj
						if($arPayBonus["PAY_PRODUCTS"]["PROFILE"]["NO_PRODUCT_CONDITIONS"] == 'Y') //Esli net usloviy po tovaram, no est' ogranichenie po summe oplati zakaza
							$productPart = $arItem["PRICE"] * $arItem["QUANTITY"] * 100 / $arPayBonus["PAY_PRODUCTS_SUM"]; 
						else
							$productPart = $canPayProductUnit * $canPayQuantity * 100 / $arPayBonus["PAY_PRODUCTS"]["ALL_BONUS"]; 
						
						$discountPlusPosition = $bonusPayCart * $productPart / 100; //Skol'ko rublej nado pripljusovat' k skidke pozicii tovara
						$discountPlusUnit = $discountPlusPosition / $arItem["QUANTITY"];
						$newPrice = $arItem["PRICE"] - $discountPlusUnit; //Cena s uchetom raskidanooj skidki
						$newPriceRound = \Bitrix\Catalog\Product\Price::roundPrice($arItem["PRICE_TYPE_ID"], $newPrice, $arItem["CURRENCY"]);
						$payBonusUnit = $arItem["PRICE"] - $newPriceRound;
						$payBonusPosition = $payBonusUnit * $arItem["QUANTITY"];
						$newDiscount = $arItem["BASE_PRICE"] - $newPriceRound; //Skidka s uchetom dobavlennoj novoj skidki
						
						$arNewPrices[$arItem["BASKET_ID"]] = array(
																	"NEW_PRICE" => $newPriceRound,
																	"NEW_DISCOUNT" => $newDiscount,
																	"BASE_PRICE" => $arItem["BASE_PRICE"],
																	"BITRIX_DISCOUNT_PRICE" => $arItem["PRICE"],
																	"PAY_BONUS_QUANTITY" => $payBonusUnit,
																	"PAY_BONUS_POSITION" => $payBonusPosition
																	);
					endforeach;
				
					//Menyaem ceni
					$customPriceNewApi = \Logictim\Balls\Helpers::CheckVersionModuleSale('20.0.1200');
					foreach($basket as $basketItem):
						$item = $basketItem->getFields();
						$arItem = $item->getValues();
						$arItem["BASKET_ID"] = $basketItem->getId();
						
						//Esli tovar nel'zya oplatit' bonusami
						if($arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["ADD_BONUS"] > 0 || $arPayBonus["PAY_PRODUCTS"]["PROFILE"]["NO_PRODUCT_CONDITIONS"] == 'Y')
							$canPayProduct = $arPayBonus["PAY_PRODUCTS"]["ITEMS"][$arItem["BASKET_ID"]]["ADD_BONUS"];
						else
							continue;
						
						
						if(isset($arNewPrices[$arItem["BASKET_ID"]]))
						{
							if($customPriceNewApi == 'Y')
								$basketItem->markFieldCustom('PRICE');
							else
								$basketItem->setField('CUSTOM_PRICE', 'Y');
							$basketItem->setField('PRICE', $arNewPrices[$arItem["BASKET_ID"]]["NEW_PRICE"]);
							$basketItem->setField('BASE_PRICE', $arNewPrices[$arItem["BASKET_ID"]]["BASE_PRICE"]);
							$basketItem->setField('DISCOUNT_PRICE', $arNewPrices[$arItem["BASKET_ID"]]["NEW_DISCOUNT"]);
							
							//zapisivaem novie ceni v nash massiv
							$arItems[$arItem["BASKET_ID"]]["PRICE"] = $arNewPrices[$arItem["BASKET_ID"]]["NEW_PRICE"];
							$arItems[$arItem["BASKET_ID"]]["BASE_PRICE"] = $arNewPrices[$arItem["BASKET_ID"]]["BASE_PRICE"];
							$arItems[$arItem["BASKET_ID"]]["DISCOUNT_PRICE"] = $arNewPrices[$arItem["BASKET_ID"]]["NEW_DISCOUNT"];
							
						}
					endforeach;
				endif;
				
				//Esli bonusami oplacheno bol'she, chem stoimost korzini, to vichitaem ih iz dostavki
				if($bonusPayDelivery > 0):
					$shipmentCollection = $order->getShipmentCollection();
					foreach($shipmentCollection as $shipment):
						if(!$shipment->isSystem()) {
							$basePrice = $shipment->getField('BASE_PRICE_DELIVERY');
							$shipment->setFields(array(
								'PRICE_DELIVERY' => $newDeliveryPrice, 'BASE_PRICE_DELIVERY' => $basePrice, 'DISCOUNT_PRICE' => $basePrice-$newDeliveryPrice, 'CUSTOM_PRICE_DELIVERY' => 'Y'
							));
							//iz-za baga bitrix prihoditsya ustanovit cenu dostavki snachala kak custom, a potom pomenyat na ne custom. Inache ne pereschitivaet zakaz
							$shipment->setFields(array(
								'CUSTOM_PRICE_DELIVERY' => 'N'
							));
						}
					endforeach;
				endif;
				
				
				//Menyaem summu k oplate
				$pay_bonus = $arOrderParams["ORDER_SUM"] - $order->getPrice();
				foreach($paymentCollection as $arPayment):
					$fields = $arPayment->GetFields();
					$values = $fields->GetValues();
					
					if($values["SUM"] && $pay_bonus > 0 && $values["SUM"] > $order->getPrice()) //bitrix c versii 17.8-18.0 stal sam pereschitivat oplatu, poetomu dobavleno uslovie  $values["SUM"] > $order->getPrice()
					{
						if($arPayment->isInner())
						{
							if($pay_bonus + $values["SUM"] > $arOrderParams["ORDER_SUM"])
							{
								$new_pay_sum = $values["SUM"] - ($pay_bonus + $values["SUM"] - $arOrderParams["ORDER_SUM"]);
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
			
			else:
			
				//Menyaem summu k oplate
				foreach($paymentCollection as $arPayment):
					$fields = $arPayment->GetFields();
					$values = $fields->GetValues();
					
					if($values["SUM"] && $pay_bonus > 0)
					{
						if($arPayment->isInner())
						{
							if($pay_bonus + $values["SUM"] > $arOrderParams["ORDER_SUM"])
							{
								$new_pay_sum = $values["SUM"] - ($pay_bonus + $values["SUM"] - $arOrderParams["ORDER_SUM"]);
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
				
				//ADD PAYMENT BONUS
				if($pay_bonus > 0)
				{
					//Get ID of paysystem Bonus
					$paySystemId = \cHelper::PaySystemBonusId();
					$paymentCollection = $order->getPaymentCollection();
					$paymentBonus = $paymentCollection->createItem(\Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId));
					$paymentBonus->setField("SUM", $pay_bonus);
					$paymentBonus->setField("PAID", "Y");
				}
			
			endif;
		
		endif;
		$order->save();
		
		// from /lib/paybonus/orderaftersaved.php
		$pay_bonus = $pay_bonus_save;
		if($is_new && $UserBallance > 0 && $pay_bonus > 0 && $UserBallance >= $pay_bonus):
			
			//Poluchaem ID platega bonusami po zakazu
			$paymentCollection = $order->getPaymentCollection();
			foreach($paymentCollection as $arPayment):
				$fields = $arPayment->GetFields();
				$values = $fields->GetValues();
				$paySystemId = \cHelper::PaySystemBonusId();
				if($values["PAY_SYSTEM_ID"] == $paySystemId)
					$paymentId = $values["ID"];
			endforeach;
			
			//Sozdaem operaciyu spisaniya
			$arFields = array(
			  "MINUS_BONUS" => $pay_bonus,
			  "USER_ID" => $user_id,
			  "OPERATION_TYPE" => 'MINUS_FROM_ORDER',
			  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num,
			  "ORDER_ID" => $orderId,
			  "PAYMENT_ID" => $paymentId,
			  //"DETAIL_TEXT" => GetMessage("logictim.balls_BONUS_FROM_ORDER"),
			);
			\logictimBonusApi::MinusBonus($arFields);
			
			
			//Shitaem obshuyu summu oplati zakaza bonusami
			$iblokOperationsId = \cHelper::IblokOperationsId();
			$operationsType = \cHelper::OperationsType();
			$dbOperations = \CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $orderId, 
									"PROPERTY_USER" => $user_id,
									"PROPERTY_OPERATION_TYPE" => array($operationsType['MINUS_FROM_ORDER'])
								), 
								false, 
								false
							);
			$allBonusPayOrder = 0;
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationFields = $Op->GetFields();
				 $operationProps = $Op->GetProperties();
				 $allBonusPayOrder += $operationProps["OPERATION_SUM"]["VALUE"];
			}
			
			$payBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
			$payBonusProp->setValue($allBonusPayOrder);
			$payBonusProp->save();
			
			
		endif;
		
		
	}
}

?>