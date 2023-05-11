<?
namespace Logictim\Balls\Ajax;

\Bitrix\Main\Loader::includeModule("logictim.balls");

class SaleOrderAjax {
	
	public static function OnSaleComponentOrderCreated($order, &$arUserResult, $request, &$arParams, &$arResult)
	{
	}
	
	public static function OnSaleComponentOrderResultPrepared($order, &$arUserResult, $request, &$arParams, &$arResult)
	{
		
		if(!empty($_POST))
			$post = 'Y';
			
		$basket = $order->getBasket();
		
		//Korzina zakaza
		$cartSum = 0;
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
			//ceni so skidkami, bez ucheta skidki bonusami
			if(isset($resultDiscounts["PRICES"]["BASKET"][$arItem["BASKET_ID"]]))
			{
				$arItem["BASE_PRICE"] = $resultDiscounts["PRICES"]["BASKET"][$arItem["BASKET_ID"]]["BASE_PRICE"];
				$arItem["PRICE"] = $resultDiscounts["PRICES"]["BASKET"][$arItem["BASKET_ID"]]["PRICE"];
				$arItem["DISCOUNT_PRICE"] = $resultDiscounts["PRICES"]["BASKET"][$arItem["BASKET_ID"]]["DISCOUNT"];
				$arItem["PRICE_POSITION"] = $arItem["QUANTITY"] * $arItem["PRICE"];
			}
			$arItems[$arItem["BASKET_ID"]] = $arItem;
			$cartSum = $cartSum + $arItem["PRICE_POSITION"];
		endforeach;
		/* Polucaem dannie po zakazu s uchetom oravil korzini */
		
		//Payments
		$paymentCollection = $order->getPaymentCollection();
		$arPayments = array();
		foreach($paymentCollection as $payment):
			$paymentId = $payment->getPaymentSystemId();
			$arPayments[$paymentId] = array("ID"=>$paymentId, "SUM"=>$payment->getSum(), "NAME"=>$payment->getPaymentSystemName(), "IS_PAYED"=>$payment->isPaid(), "IS_INNER"=>$payment->isInner());
			
			if($payment->isInner() == true)
				$paySumFromInner = $payment->getSum();
		endforeach;
		
		//Delivery
		$deliveryCollection = $order->getDeliverySystemId();
		$arDelivery = array();
		foreach($deliveryCollection as $delivery):
			$arDelivery[$delivery] = array("ID"=>$delivery);
		endforeach;
		
		//Polya zakaza
		$discountData = $order->getDiscount()->getApplyResult();
		$arOrderParams = array(
								"USER_ID" => $order->getUserId(),
								"ORDER_SUM" => $cartSum+$order->getDeliveryPrice(),
								"CART_SUM" => $cartSum,
								"DELIVERY_SUM" => $order->getDeliveryPrice(),
								"PERSON_TYPE_ID" => $order->getPersonTypeId(),
								"CURRENCY" => $order->getCurrency(),
								"PAYMENTS" => $arPayments,
								"DELIVERY" => $arDelivery,
								);
		
		global $USER;
		if($USER->IsAuthorized())
			$UserBallance = \Logictim\Balls\Helpers::UserBallance($arOrderParams["USER_ID"]);
		else
			$UserBallance = 0;
		
		//bonusov k olate
		$props = $order->getPropertyCollection();
		foreach($props as $prop) {
			$fields = $prop->GetFields();
			$values = $fields->GetValues();
			if($values["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
			{
				$payment_prop_id = $values["ORDER_PROPS_ID"];
				$input_bonus = str_replace(',', '.', $values["VALUE"]);
				if(!is_numeric($input_bonus))
					$input_bonus = 0;
			}
			if($values["CODE"] == 'LOGICTIM_ADD_BONUS')
				$addBpnus_prop_id = $values["ORDER_PROPS_ID"];
		}
		
		if($USER->IsAuthorized()):
			if($post == 'Y')
			{
				$pay_bonus = $input_bonus;
				if(!is_numeric($pay_bonus))
					$pay_bonus = 0;
			}
			else
			{
				$pay_bonus = 'MAX';
				if(\COption::GetOptionString("logictim.balls", "ORDER_PAY_BONUS_AUTO", 'Y') != 'Y')
					$pay_bonus = 0;
			}
		
			$arOrderParams["PAY_BONUS"] = $pay_bonus;
			$arPayBonus = \Logictim\Balls\CalcBonus::OrderBonusPayment($arItems, $arOrderParams);
			$minBonusSum = $arPayBonus["MIN_ORDER_PAY"];
			$maxBonusSum = $arPayBonus["MAX_ORDER_PAY"];
			$pay_bonus = $arOrderParams["PAY_BONUS"] = $arPayBonus["PAY_BONUS"];
			$bonusPayCart = $arPayBonus["PAY_CART"];
			$bonusPayDelivery = $arPayBonus["PAY_DELIVERY"];
			$newDeliveryPrice = $arPayBonus["NEW_DELIVERY_PRICE"];
			
			//Raskidivaem oplatu bonusami v vide skidki
			$PayBonusToDiscount = \COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
			if($PayBonusToDiscount == 'B' && $pay_bonus >= 0 || $PayBonusToDiscount == 'Y' && $pay_bonus >= 0):
			
				if($bonusPayCart >= 0):
				
					foreach($basket as $basketItem):
						$item = $basketItem->getFields();
						$arBasketItem = $item->getValues();
						$arItem = $arItems[$arBasketItem["ID"]];
						
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
						$newPriceRound = \Bitrix\Catalog\Product\Price::roundPrice($arBasketItem["PRICE_TYPE_ID"], $newPrice, $arBasketItem["CURRENCY"]);
						$payBonusUnit = $arItem["PRICE"] - $newPriceRound;
						$payBonusPosition = $payBonusUnit * $arItem["QUANTITY"];
						$newDiscount = $arItem["BASE_PRICE"] - $newPriceRound; //Skidka s uchetom dobavlennoj novoj skidki
						
						//zapisivaem novie ceni v nash massiv
						$arItems[$arItem["BASKET_ID"]]["PRICE"] = $newPriceRound;
						$arItems[$arItem["BASKET_ID"]]["PRICE_FORMAT"] = SaleFormatCurrency($newPriceRound, $arBasketItem["CURRENCY"]);
						$arItems[$arItem["BASKET_ID"]]["PRICE_POSITION"] = $newPriceRound * $arItem["QUANTITY"];
						$arItems[$arItem["BASKET_ID"]]["PRICE_POSITION_FORMAT"] = SaleFormatCurrency($arItems[$arItem["BASKET_ID"]]["PRICE_POSITION"], $arBasketItem["CURRENCY"]);
						$arItems[$arItem["BASKET_ID"]]["BASE_PRICE"] = $arItem["BASE_PRICE"];
						$arItems[$arItem["BASKET_ID"]]["BASE_PRICE_FORMAT"] = SaleFormatCurrency($arItem["BASE_PRICE"], $arBasketItem["CURRENCY"]);
						$arItems[$arItem["BASKET_ID"]]["DISCOUNT_PRICE"] = $newDiscount;
						
						$arItems[$arItem["BASKET_ID"]]["BITRIX_DISCOUNT_PRICE"] = $arItem["PRICE"];
						$arItems[$arItem["BASKET_ID"]]["PAY_BONUS_QUANTITY"] = $payBonusUnit;
						$arItems[$arItem["BASKET_ID"]]["PAY_BONUS_POSITION"] = $payBonusPosition;
						
						//zapisivaem novie ceni arresult komponenta
						if($PayBonusToDiscount == 'B')
						{
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["PRICE_FORMATED"] = $arItems[$arItem["BASKET_ID"]]["PRICE_FORMAT"];
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["PRICE"] = $arItems[$arItem["BASKET_ID"]]["PRICE"];
							
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["SUM_NUM"] = $arItems[$arItem["BASKET_ID"]]["PRICE_POSITION"];
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["SUM"] = $arItems[$arItem["BASKET_ID"]]["PRICE_POSITION_FORMAT"];
							
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["DISCOUNT_PRICE"] = $arItems[$arItem["BASKET_ID"]]["DISCOUNT_PRICE"];
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["SUM_DISCOUNT_DIFF"] = $arItems[$arItem["BASKET_ID"]]["DISCOUNT_PRICE"]*$arItems[$arItem["BASKET_ID"]]["QUANTITY"];
							$arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["SUM_DISCOUNT_DIFF_FORMATED"] = SaleFormatCurrency($arResult["JS_DATA"]["GRID"]["ROWS"][$arItem["BASKET_ID"]]["data"]["SUM_DISCOUNT_DIFF"], $arBasketItem["CURRENCY"]);
						}
					endforeach;
				endif;
				
				$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ARR_PAY_BONUS"]["ITEMS"] = $arItems;
				
				
				//New basket and order sum
				$currency = $order->getCurrency();
				$newCartSum = $newOrderSum = 0;
				foreach($arItems as $arItem):
					$newCartSum += $arItem["PRICE_POSITION"];
				endforeach;
				
				/* if pay from bill */
				if($paySumFromInner > 0)
				{
					$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_LEFT_TO_PAY_FORMATED'] = \SaleFormatCurrency($arOrderParams['ORDER_SUM'] - $paySumFromInner - $pay_bonus, $currency);
					
					if($paySumFromInner > $arOrderParams['ORDER_SUM'] - $pay_bonus)
					{
						$newPaySumFromInner = $arOrderParams['ORDER_SUM'] - $pay_bonus;
						$arResult['JS_DATA']['TOTAL']['PAYED_FROM_ACCOUNT_FORMATED'] = \SaleFormatCurrency($newPaySumFromInner, $currency);
						$arResult['JS_DATA']['TOTAL']['ORDER_TOTAL_LEFT_TO_PAY_FORMATED'] = \SaleFormatCurrency(0, $currency);
					}
				}
				/* if pay from bill */
				
				if($bonusPayDelivery > 0)
				{
					$newDeliveryPrice = $newDeliveryPrice;
					$oldDeliveryPrice = $arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"];
				}
				else
				{
					$newDeliveryPrice = $arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"];
					$oldDeliveryPrice = $arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"];
				}
				$newOrderSum = $newCartSum + $newDeliveryPrice;
				
				
				if($PayBonusToDiscount == 'B')
				{
					$arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE"] = $newCartSum;
					$arResult["JS_DATA"]["TOTAL"]["ORDER_PRICE_FORMATED"] = \SaleFormatCurrency($newCartSum, $currency);
					$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"] = $newOrderSum;
					$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = \SaleFormatCurrency($newOrderSum, $currency);
					
					$arResult["JS_DATA"]["TOTAL"]["DISCOUNT_PRICE"] = $arResult["JS_DATA"]["TOTAL"]["PRICE_WITHOUT_DISCOUNT_VALUE"] + $oldDeliveryPrice - $newOrderSum;
					$arResult["JS_DATA"]["TOTAL"]["DISCOUNT_PRICE_FORMATED"] = \SaleFormatCurrency($arResult["JS_DATA"]["TOTAL"]["DISCOUNT_PRICE"], $currency);
					
					//Esli bonusami oplacheno bol'she, chem stoimost korzini, to vichitaem ih iz dostavki
					if($bonusPayDelivery > 0):
						foreach($arDelivery as $shipment):
							$arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE"] = $newDeliveryPrice;
							$arResult["JS_DATA"]["TOTAL"]["DELIVERY_PRICE_FORMATED"] = \SaleFormatCurrency($newDeliveryPrice, $currency);
							$arResult["JS_DATA"]["DELIVERY"][$shipment["ID"]]["DELIVERY_DISCOUNT_PRICE"] = $newDeliveryPrice;
							$arResult["JS_DATA"]["DELIVERY"][$shipment["ID"]]["DELIVERY_DISCOUNT_PRICE_FORMATED"] = \SaleFormatCurrency($newDeliveryPrice, $currency);
						endforeach;
					endif;
					
				}
				if($PayBonusToDiscount == 'Y')
				{
					$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"] = $newOrderSum;
					$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = \SaleFormatCurrency($newOrderSum, $currency);
				}
			
			endif;
			
			//Vichitaem oplatu bonusami iz summi zazaka, esli stoit v nastroykax modulya
			if($PayBonusToDiscount == 'N' && \COption::GetOptionString("logictim.balls", "ORDER_TOTAL_BONUS", 'Y') == 'Y' && $pay_bonus > 0) 
			{
				$order_new_sum = $arOrderParams["ORDER_SUM"] - $pay_bonus;
				$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE"] = $order_new_sum;
				$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = $arResult["ORDER_TOTAL_PRICE_FORMATED"] = \SaleFormatCurrency($order_new_sum, $arResult['BASE_LANG_CURRENCY']);
				
				if(strlen($arResult["PAYED_FROM_ACCOUNT_FORMATED"]) > 0)
				{
					$payFromAccaunt = (float)str_replace(" ", "", $arResult["PAYED_FROM_ACCOUNT_FORMATED"]);
					if($payFromAccaunt + $pay_bonus > $arOrderParams["ORDER_SUM"])
					{
						$payFromAccaunt = $payFromAccaunt - ($pay_bonus + $payFromAccaunt - $arOrderParams["ORDER_SUM"]);
						$arResult["PAYED_FROM_ACCOUNT_FORMATED"] = \SaleFormatCurrency($payFromAccaunt, $arResult['BASE_LANG_CURRENCY']);
					}
					$arResult["ORDER_TOTAL_LEFT_TO_PAY_FORMATED"] = \SaleFormatCurrency($arOrderParams["ORDER_SUM"] - $pay_bonus - $payFromAccaunt, $arResult['BASE_LANG_CURRENCY']);
				}
			}
			
		endif;
		
		
		//SKOL'KO BONUSOV BUDET NACHISLENO ZA ZAKAZ
		//echo '<pre>'; print_r($arItems); echo '</pre>';
		$arBonus = \Logictim\Balls\CalcBonus::getBonus($arItems, array("TYPE"=>'cart', "PROFILE_TYPE" => 'order', "ORDER"=>$arOrderParams));
		//echo '<pre>'; print_r($arBonus); echo '</pre>';
		
		$UserBonusSystemDostup = 'Y';
		$arResult["MIN_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["MIN_BONUS"] = $minBonusSum;
		$arResult["MAX_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["MAX_BONUS"] = $maxBonusSum;
		$arResult["USER_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["USER_BONUS"] = $UserBallance;
		$arResult["LOGICTIM_BONUS_USER_DOSTUP"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["LOGICTIM_BONUS_USER_DOSTUP"] = $UserBonusSystemDostup;
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["INPUT_BONUS"] = $input_bonus;
		$arResult["PAY_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS"] = $pay_bonus;
		$arResult["PAY_BONUS_FORMATED"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_FORMATED"] = \SaleFormatCurrency($pay_bonus, $arOrderParams['CURRENCY']);
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_NO_POST"] = $pay_bonus; //OLD
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS_NO_POST_FORMATED"] = SaleFormatCurrency($pay_bonus, $arOrderParams['CURRENCY']); //OLD
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_SUM"] = $arOrderParams["ORDER_SUM"];
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_SUM_FORMATED"] = SaleFormatCurrency($arOrderParams["ORDER_SUM"], $arOrderParams['CURRENCY']);
		
		$arResult["ARR_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ARR_BONUS"] = $arBonus;
		$arResult["ADD_BONUS"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ADD_BONUS"] = (string)$arBonus["ALL_BONUS"];
		
		$formatAll = \COption::GetOptionString("logictim.balls", "TEMPLATE_BONUS_FOR_ORDER", '');
		if(!$formatAll || $formatAll == '')
			$formatAll = '#BONUS#';
		$arResult["ADD_BONUS_FORMAT"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ADD_BONUS_FORMAT"] = str_replace('#BONUS#', (string)$arBonus["ALL_BONUS"], $formatAll);
		
		
		$arResult["ORDER_PROP_PAYMENT_BONUS_ID"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_PROP_PAYMENT_BONUS_ID"] = $payment_prop_id;
		$arResult["ORDER_PROP_ADD_BONUS_ID"] = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_PROP_ADD_BONUS_ID"] = $addBpnus_prop_id;
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["DISCOUNT_TO_PRODUCTS"] = \COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'N');
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_PAY_BONUS_AUTO"] = \COption::GetOptionString("logictim.balls", "ORDER_PAY_BONUS_AUTO", 'Y');
		
		//ADD_TEXT
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_BALLS"] = \COption::GetOptionString("logictim.balls", "TEXT_BONUS_BALLS", 'bonus:');
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_PAY"] = \COption::GetOptionString("logictim.balls", "TEXT_BONUS_PAY", 'pay from bonus:');
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["TEXT_BONUS_FOR_ITEM"] = \COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_ITEM", 'pay from bonus:');
		
		$payCooment = '';
		if(strpos(\COption::GetOptionString("logictim.balls", "MIN_BONUS_TEXT", ''), '#BONUS#') !== false || strpos(\COption::GetOptionString("logictim.balls", "MAX_BONUS_TEXT", ''), '#BONUS#') !== false)
		{
			$payCooment .= \COption::GetOptionString("logictim.balls", "CAN_BONUS_TEXT", 'Can use bonus');
			if($minBonusSum > 0)
				$payCooment .= ' '.\Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "MIN_BONUS_TEXT", 'Min use bonus'), '#BONUS#', $minBonusSum);
			if($maxBonusSum > 0 && $maxBonusSum >= $minBonusSum)
				$payCooment .= ' '.\Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "MAX_BONUS_TEXT", 'Max use bonus'), '#BONUS#', $maxBonusSum);
		}
		else
		{
			if($minBonusSum > 0)
				$payCooment .= '<span>'.\Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "MIN_BONUS_TEXT", 'Min use bonus'), '#BONUS#', $minBonusSum).'</span>';
			if($maxBonusSum > 0 && $maxBonusSum >= $minBonusSum)
				$payCooment .= '<span>'.\Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "MAX_BONUS_TEXT", 'Max use bonus'), '#BONUS#', $maxBonusSum).'</span>';
		}
		$errorMinBonusComment = '';
		if(\COption::GetOptionString("logictim.balls", "TEXT_BONUS_ERROR_MIN_BONUS", '') != '')
		{
			if(strpos(\COption::GetOptionString("logictim.balls", "TEXT_BONUS_ERROR_MIN_BONUS", ''), '#BONUS#') !== false)
				$errorMinBonusComment .= \Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "TEXT_BONUS_ERROR_MIN_BONUS", ''), '#BONUS#', $minBonusSum);
		}
		else
			$errorMinBonusComment .= $payCooment;
		
		$arResult["JS_DATA"]["LOGICTIM_BONUS"]["MODULE_LANG"] = array(
															"HAVE_BONUS_TEXT" => \COption::GetOptionString("logictim.balls", "HAVE_BONUS_TEXT", 'Have bonus'),
															"HAVE_BONUS_TEXT_FORMAT" => \Logictim\Balls\Helpers::FormatBonusString(\COption::GetOptionString("logictim.balls", "HAVE_BONUS_TEXT", 'Have bonus'), '#BONUS#', $UserBallance),
															"CAN_USE_BONUS_TEXT" => \COption::GetOptionString("logictim.balls", "CAN_BONUS_TEXT", 'Can use bonus'),
															"CAN_USE_BONUS_TEXT_FORMAT" => $payCooment,
															"MIN_BONUS_TEXT" => \COption::GetOptionString("logictim.balls", "MIN_BONUS_TEXT", 'Min use bonus').$arResult["MIN_BONUS"],
															"MAX_BONUS_TEXT" => \COption::GetOptionString("logictim.balls", "MAX_BONUS_TEXT", 'Max use bonus').$arResult["MAX_BONUS"],
															"PAY_BONUS_TEXT" => \COption::GetOptionString("logictim.balls", "PAY_BONUS_TEXT", 'Pay from bonus'),
															"TEXT_BONUS_FOR_PAYMENT" => \COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_PAYMENT", 'Pay from bonus'),
															"TEXT_BONUS_FOR_PAYMENT" => \COption::GetOptionString("logictim.balls", "TEXT_BONUS_FOR_PAYMENT", 'Pay from bonus'),
															"TEXT_BONUS_USE_BONUS_BUTTON" => \COption::GetOptionString("logictim.balls", "TEXT_BONUS_USE_BONUS_BUTTON", 'Use'),
															"TEXT_BONUS_ERROR_MIN_BONUS_FORMAT" => $errorMinBonusComment,
															);
		
		
		//Udalyaem platejnie sistemi bonusov iz shablona
		foreach($arResult["JS_DATA"]["PAY_SYSTEM"] as $keyPaysystem => $paySystem):
			if($paySystem["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
				unset($arResult["JS_DATA"]["PAY_SYSTEM"][$keyPaysystem]);
		endforeach;
		$arResult["JS_DATA"]["PAY_SYSTEM"] = array_values($arResult["JS_DATA"]["PAY_SYSTEM"]);
		foreach($arResult["PAY_SYSTEM"] as $keyPaysystem => $paySystem):
			if($paySystem["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
				unset($arResult["PAY_SYSTEM"][$keyPaysystem]);
		endforeach;
		$arResult["PAY_SYSTEM"] = array_values($arResult["PAY_SYSTEM"]);
		
		global $APPLICATION;
		if(\COption::GetOptionString("logictim.balls", "INTEGRATE_IN_SALE_ORDER_AJAX", 'N') == 'Y')
		{
			$APPLICATION->AddHeadScript('/bitrix/js/logictim.balls/sale_order_ajax.js');
			$APPLICATION->SetAdditionalCSS("/bitrix/js/logictim.balls/sale_order_ajax.css");
			//CJSCore::Init(array("jquery2"));
		}
		
	}
}
