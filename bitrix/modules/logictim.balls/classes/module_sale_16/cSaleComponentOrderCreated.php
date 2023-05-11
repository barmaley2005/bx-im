<?

//KLASS OTKLYUCHEN V INCLUDE.PHP. NAHODITSYA V RAZRABOTKE
class cLTBOnSaleComponentOrderCreated
{
	public static function OnSaleComponentOrderCreated($order, &$arUserResult, $request, &$arParams, &$arResult)
	{
		if(!empty($_POST))
			$post = 'Y';
		
		$order_fields = $order->GetFields();
		$order_values = $order_fields->GetValues();
		//echo '<pre>'; print_r($values); echo '</pre>';
		
		//Polya zakaza
		$is_new = $order->isNew();
		$user_id = $order_values["USER_ID"];
		$currency = $order_values["CURRENCY"];
		$basket = $order->getBasket();
		$order_sum = $order->getPrice(); //Stoimost zakaza
		$order_cart_sum = $basket->getPrice(); //Stoimost korzini
		$order_delivery_sum = $order->getDeliveryPrice(); //Stoimost dostavki
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
		$UserBallance = cHelper::UserBallance($user_id);
		
		
		//Svoystava zakaza
		$props = $order->getPropertyCollection();
		foreach($props as $prop) {
			$fields = $prop->GetFields();
			$values = $fields->GetValues();
			if($values["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
			{
				$payment_prop_id = $values["ORDER_PROPS_ID"];
				$input_bonus = $values["VALUE"];
				if(!is_numeric($input_bonus))
					$input_bonus = 0;
			}
			if($values["CODE"] == 'LOGICTIM_ADD_BONUS')
				$addBpnus_prop_id = $values["ORDER_PROPS_ID"];
		}
		
		//Korzina zakaza
		$basketItems = $basket->getBasketItems();
		$arItems = array();
		foreach ($basket as $basketItem):
			$arItem = array();
			$arItem["PRODUCT_ID"] = $basketItem->getProductId();
			$arItem["NAME"] = $basketItem->getField('NAME');
			$arItem["QUANTITY"] = $basketItem->getQuantity();
			$arItem["BASE_PRICE"] = $basketItem->getField('BASE_PRICE');
			$arItem["PRICE"] = $basketItem->getPrice();
			$arItem["DISCOUNT_PRICE"] = $basketItem->getField('DISCOUNT_PRICE');
			$arItems[$arItem["PRODUCT_ID"]] = $arItem;
		endforeach;
		
		
		
		//Oplata bonusami
		global $USER;
		if($USER->IsAuthorized() && $UserBonusSystemDostup == 'Y' && $UserBallance > 0):
			
			//Proveryaem dopustimie min i max oplatu bonusami
			$minBonusSum = cHelper::MinBonusSum($order_sum, $order_cart_sum, $order_delivery_sum);
			$maxBonusSum = cHelper::MaxBonusSum($order_sum, $order_cart_sum, $order_delivery_sum, $arItems);
			
			if($maxBonusSum > $UserBallance)
				$maxBonusSum = $UserBallance;
				
				
			$pay_bonus = 0;	
			if($post == 'Y')
			{
				$pay_bonus = $input_bonus;
				if(!is_numeric($pay_bonus))
					$pay_bonus = 0;
				
				if($pay_bonus > 0)
				{	
					if($pay_bonus < $minBonusSum)
						$pay_bonus = $minBonusSum;
					if($pay_bonus > $maxBonusSum)
						$pay_bonus = $maxBonusSum;
					if($maxBonusSum < $minBonusSum)
						$pay_bonus = '0';
				}
				
			}
			else
			{
				$pay_bonus = $UserBallance;
				if($pay_bonus > $maxBonusSum)
					$pay_bonus = $maxBonusSum;
				if($pay_bonus < $minBonusSum)
					$pay_bonus = 0;
				
				if(COption::GetOptionString("logictim.balls", "ORDER_PAY_BONUS_AUTO", 'Y') != 'Y')
					$pay_bonus = 0;
			}
					
		endif;
		
		
		
		//Raskidivaem oplatu bonusami v vide skidki
		$PayBonusToDiscount = COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
		if($PayBonusToDiscount == 'B' && $pay_bonus > 0):
			
			$bonusItemsInfo = cHelperCalc::CartBonus($arItems);
			foreach($arItems as $arItem):
				if(
					//Esli zapret na oplatu bonusami tovarov so skidkoy, a tovar so skidkoy, to korrektiruem summu korzini dlya rasschetov
					COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y' && $arItem["DISCOUNT_PRICE"] > 0
					||
					//Zapret oplati tovara bonusami iz svoystv
					$bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["PROPERTY_BONUS_NO_PAY"] == 'Y' || $bonusItemsInfo["ITEMS"][$arItem["PRODUCT_ID"]]["MAIN_PRODUCT"]["IBLOCK_SECTION"]["PROPERTY_BONUS_NO_PAY"] == 1
				)
				{
					$order_cart_sum = $order_cart_sum - $arItem["PRICE"]*$arItem["QUANTITY"];
				}
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
					
					//zapisivaem novie ceni v nash massiv
					$arItems[$arItem["PRODUCT_ID"]]["PRICE"] = $newPrice;
					$arItems[$arItem["PRODUCT_ID"]]["BASE_PRICE"] = $arItem["BASE_PRICE"];
					$arItems[$arItem["PRODUCT_ID"]]["DISCOUNT_PRICE"] = $newDiscount;
					
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
				
				
			//CHANGE SUM FOR PAYMENT
			//GET paymentCollection
			$paymentCollection = $order->getPaymentCollection();
			$real_pay_bonus = $order_sum - $order->getPrice();
						
			foreach($paymentCollection as $arPayment):
				$fields = $arPayment->GetFields();
				$values = $fields->GetValues();
				
				if($values["SUM"] && $real_pay_bonus > 0 && $values["SUM"] > $order->getPrice()) //bitrix c versii 17.8-18.0 stal sam pereschitivat oplatu, poetomu dobavleno uslovie  $values["SUM"] > $order->getPrice()
				{
					if($arPayment->isInner())
					{
						if($real_pay_bonus + $values["SUM"] > $order_sum)
						{
							$new_pay_sum = $values["SUM"] - ($real_pay_bonus + $values["SUM"] - $order_sum);
							$arPayment->setField("SUM", $new_pay_sum);
						}
						else
						{ continue;}
					}
					else
					{
						$new_pay_sum = $values["SUM"] - $real_pay_bonus;
						if($new_pay_sum < 0) 
							$new_pay_sum = 0;
						$arPayment->setField("SUM", $new_pay_sum);
						if($new_pay_sum <= 0) 
							$arPayment->setField("PAID", "Y");
					}
				}
			endforeach;
			
		endif;
		
		//SKOL'KO BONUSOV BUDET NACHISLENO ZA ZAKAZ
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '3') == 4)
		{
			if($UserBonusSystemDostup == 'Y')
			{
				$bonusMetod = (int)COption::GetOptionString("logictim.balls", "BONUS_METOD", 5);
				$arBonus = cHelperCalc::OrderBonus($arItems, $order_sum, $order_cart_sum, $order_delivery_sum, $pay_bonus);
			}
		}
		else
		{
			if($UserBonusSystemDostup == 'Y')
			{
				$bonusMetod = (int)COption::GetOptionString("logictim.balls", "BONUS_METOD", 5);
				$arBonus = cHelperCalc::OrderBonus($arItems, $order_sum, $order_cart_sum, $order_delivery_sum, $pay_bonus);
			}
		}
		
		
		
		$arResult["MIN_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["MIN_BONUS"] = $minBonusSum;
		$arResult["MAX_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["MAX_BONUS"] = $maxBonusSum;
		$arResult["USER_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["USER_BONUS"] = $UserBallance;
		$arResult["LOGICTIM_BONUS_USER_DOSTUP"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["LOGICTIM_BONUS_USER_DOSTUP"] = $UserBonusSystemDostup;
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["INPUT_BONUS"] = $input_bonus;
		$arResult["PAY_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS"] = $pay_bonus;
		$arResult["PAY_BONUS_FORMATED"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_FORMATED"] = SaleFormatCurrency($pay_bonus, $arResult['BASE_LANG_CURRENCY']);
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_NO_POST"] = $pay_bonus; //OLD
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_NO_POST_FORMATED"] = SaleFormatCurrency($pay_bonus, $arResult['BASE_LANG_CURRENCY']); //OLD
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_SUM"] = $order_sum;
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_SUM_FORMATED"] = SaleFormatCurrency($order_sum, $currency);
		
		$arResult["ARR_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ARR_BONUS"] = $arBonus;
		$arResult["ADD_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ADD_BONUS"] = (string)$arBonus["ALL_BONUS"];
		
		$arResult["ORDER_PROP_PAYMENT_BONUS_ID"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_PROP_PAYMENT_BONUS_ID"] = $payment_prop_id;
		$arResult["ORDER_PROP_ADD_BONUS_ID"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_PROP_ADD_BONUS_ID"] = $addBpnus_prop_id;
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["DISCOUNT_TO_PRODUCTS"] = COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
		
		//ADD_TEXT
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_BALLS"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_BALLS", 'bonus:');
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_PAY"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_PAY", 'pay from bonus:');
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_FOR_ITEM"] = COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", 'pay from bonus:');
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["MODULE_LANG"] = array(
															"HAVE_BONUS_TEXT" => COption::GetOptionString("logictim.balls", "HAVE_BONUS_TEXT", 'Have bonus'),
															"CAN_USE_BONUS_TEXT" => COption::GetOptionString("logictim.balls", "CAN_BONUS_TEXT", 'Can use bonus'),
															"MIN_BONUS_TEXT" => COption::GetOptionString("logictim.balls", "MIN_BONUS_TEXT", 'Min use bonus').$arResult["MIN_BONUS"],
															"MAX_BONUS_TEXT" => COption::GetOptionString("logictim.balls", "MAX_BONUS_TEXT", 'Max use bonus').$arResult["MAX_BONUS"],
															"PAY_BONUS_TEXT" => COption::GetOptionString("logictim.balls", "PAY_BONUS_TEXT", 'Pay from bonus'),
															"TEXT_BONUS_FOR_PAYMENT" => COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_PAYMENT", 'Pay from bonus'),
															);
		
	}
		
}


?>