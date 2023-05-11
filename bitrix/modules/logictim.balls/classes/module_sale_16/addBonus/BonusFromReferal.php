<?

class cBonusFromReferal
{
	public static function BonusAdd($order, $params = array())
	{
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		
		$order_id = $values["ID"];
		$order_num = $values["ACCOUNT_NUMBER"];
		$order_user_id = $values["USER_ID"];
		$order_status = $values["STATUS_ID"];
		$order_price = $values["PRICE"];
		$order_delivery_sum = $values["PRICE_DELIVERY"];
		$order_cart_sum = $order_price - $order_delivery_sum;
		
		
		$referalSystemType = (int)COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0);
		
		if($referalSystemType > 0)
		{
		}
		else
			return;
		
		//Poluchaem id partnera
		$dbPartners = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_CODE" => 'logictim_bonus_referals', "PROPERTY_REFERAL" => $order_user_id), false, array("nPageSize"=>1), array("ID", "NAME", "PROPERTY_PARTNER", "PROPERTY_REFERAL"));
		while($obPartners = $dbPartners->GetNextElement())
		{
			$dbPartner = $obPartners->GetFields();
			if($dbPartner["PROPERTY_PARTNER_VALUE"] > 0)
				$partnerId = $dbPartner["PROPERTY_PARTNER_VALUE"];
		}
		
		if(cHelper::UserBonusSystemDostup($partnerId) != 'Y')
			return;
			
		//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = cHelper::IblokOperationsId();
		//Opredelyaem ID ibfobloka s operaciyami ojidaniya
			$iblokWaitId = cHelper::IblokWaitId();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = cHelper::OperationsType();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE" ibfobloka s operaciyami ojidaniya
			$operationsTypeWait = cHelper::OperationsTypeWait();
			
		//Proveryaem, net li uzhe operacii nachisleniya po etomu zakazu
		$dbOperations = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokOperationsId, $iblokWaitId), "ACTIVE"=>"Y", "PROPERTY_ORDER_ID" => $order_id, "PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_REFERAL'], $operationsTypeWait['ADD_FROM_REFERAL'])), false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME"));
		while($Op = $dbOperations->GetNextElement())
		{
			 $OperationAddFields = $Op->GetFields();
		}
		
		if(!empty($OperationAddFields)) //Esli bonusi po zakazu uzhe nachisleni, vihodim
			return;
			
		$allBonus = 0;
		$commentBonus = '';
		$bonusKoef = (float)COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_BONUS", 0);
		
		if($referalSystemType == 1):
			$allBonus = $order_price * $bonusKoef / 100;
			$commentBonus .= $order_price.' * '.$bonusKoef.' / 100';
		elseif($referalSystemType == 2):
			$allBonus = $order_cart_sum * $bonusKoef / 100;
			$commentBonus .= $order_cart_sum.' * '.$bonusKoef.' / 100';
		elseif($referalSystemType == 3):
		
			//Poluchaem svoystva zakaza
				$order_props = array();
				$db_order_props = $order->getPropertyCollection();
				foreach($db_order_props as $order_prop) 
				{
					$fields = $order_prop->GetFields();
					$values = $fields->GetValues();
					$order_props[$values["CODE"]] = $values;
				}
				
			//Poluchaem plategi po zakazu
			$paymentCollection = $order->getPaymentCollection();
			$arOrderPayments = array();
			foreach($paymentCollection as $arPayment):
				$fields = $arPayment->GetFields();
				$values = $fields->GetValues();
				$arOrderPayments[] = $values;
			endforeach;
			
			//ID plategnoy sistemi bonusov
			$paySystemId = cHelper::PaySystemBonusId();
			
			//Poluchaem sostav zakaza
			$basket = $order->getBasket();
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
			
			//Skolko oplacheno ballami
			if(COption::GetOptionString("logictim.balls", "BONUS_MINUS_BONUS", 'N') == 'Y'):
				$pay_bonus = 0;
				foreach($arOrderPayments as $payment)
				{
					if($payment["PAY_SYSTEM_ID"] == $paySystemId)
						$pay_bonus = $pay_bonus + $payment["SUM"];
				}
			endif;
			
			$arBonus = cHelperCalc::OrderBonus($arItems, $order_price, $order_cart_sum, $order_delivery_sum, $pay_bonus, $order_id, $params);	
			$allBonus = $arBonus["ALL_BONUS"] * $bonusKoef / 100;
			$commentBonus = '('.$arBonus["COMMENT_FOR_OPERATION"].') * '. $bonusKoef.'%';
			
			
		endif;
		
		
		//Nachislyaem bonusi useru
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		$allBonus = round($allBonus, $round);
		if($allBonus > 0)
		{
			$UserBallance = cHelper::UserBallance($partnerId);
			$updateUserBonus = $UserBallance + $allBonus;
			
			//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
			$operationsStatus = array();
			$status_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"LIVE_STATUS"));
			while($status_fields = $status_enums->GetNext())
			{
				$operationsStatus[$status_fields["XML_ID"]] = $status_fields["ID"];
			}
			
			//ESLI EST ZADERJKA AKTIVACII BONUSOV
			if((int)COption::GetOptionString("logictim.balls", "BONUS_ORDER_WAIT", 0) > 0):
			
			
				//Sozdaem operaciyu ojidaniya
				$newOperation = new CIBlockElement;
				$PROP = array();
				$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsTypeWait['ADD_FROM_REFERAL']);
				$PROP["USER"] = $partnerId;
				$PROP["OPERATION_SUM"] = $allBonus;
				$PROP["ORDER_ID"] = $order_id;
				$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $commentBonus, "TYPE" => "text"));
				
				$daysDelay = (int)COption::GetOptionString("logictim.balls", "BONUS_ORDER_WAIT", 0);
				$dateActivate = time() + $daysDelay*86400;
				$PROP["ACTIVATE_DATE"] = ConvertTimeStamp($dateActivate, "SHORT");;
				
				$newOperationArray = Array(
										"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
										"IBLOCK_SECTION" => false,          
										"IBLOCK_ID"      => $iblokWaitId,
										"IBLOCK_CODE "   => 'logictim_bonus_wait',
										"PROPERTY_VALUES"=> $PROP,
										"NAME"           => GetMessage("logictim.balls_BONUS_ADD_REF"),
										"ACTIVE"         => "Y",
										"CODE" => 'API_OPERATIONS'
										);
				$newOperation->Add($newOperationArray);
				
				
				
			else:
				
				//Sozdaem operaciyu nachisleniya
				$newOperation = new CIBlockElement;
				$PROP = array();
				$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['ADD_FROM_REFERAL']);
				$PROP["USER"] = $partnerId;
				$PROP["OPERATION_SUM"] = $allBonus;
				$PROP["BALLANCE_BEFORE"] = $UserBallance;
				$PROP["BALLANCE_AFTER"] = $updateUserBonus;
				$PROP["ORDER_ID"] = $order_id;
				$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $commentBonus, "TYPE" => "text"));
				$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
				$liveBonusTime = COption::GetOptionString("logictim.balls", "LIVE_BONUS_TIME", '365');
				if($liveBonus == 'Y') {
					$dateBonusEnd = time() + $liveBonusTime*86400 + 86400;
					$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "SHORT");
					$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
					$PROP["BALLANCE"] = $allBonus;
				}
				$newOperationArray = Array(
										"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
										"IBLOCK_SECTION" => false,          
										"IBLOCK_ID"      => $iblokOperationsId,
										"IBLOCK_CODE "   => 'logictim_bonus_operations',
										"PROPERTY_VALUES"=> $PROP,
										"NAME"           => GetMessage("logictim.balls_BONUS_ADD_REF"),
										"ACTIVE"         => "Y",
										"CODE" => 'API_OPERATIONS'
										);
				if($newOperation->Add($newOperationArray));
				
				//Esli ispol'zuetsya bonusniy schet modulya
				if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
				{
					global $DB, $USER_FIELD_MANAGER;
					$USER_FIELD_MANAGER->Update("USER",$partnerId, array("UF_LOGICTIM_BONUS" => $updateUserBonus));
				}
				//Esli ispol'zuetsya vnutrenniy schet bitrix
				else
				{
					$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
					CSaleUserAccount::UpdateAccount($partnerId, +$allBonus, $currency, GetMessage("logictim.balls_BONUS_ADD_REF").$order_num, $order_id);
				}
				
				//Otpravlyaem email s uvedomleniem
				CModule::IncludeModule("main");
				$rsUser = CUser::GetByID($partnerId);
				$arUser = $rsUser->Fetch();
				
				if($liveBonus == 'Y')
					$bonusLiveDate = ConvertTimeStamp($dateBonusEnd, "SHORT");
				else
					$bonusLiveDate = '-';
				CEvent::Send("LOGICTIM_BONUS_FROM_REFERAL_ADD", cHelper::SitesId(), 
					array( 
						"ORDER_ID" => $order_id, 
						"BONUS" => $allBonus,
						"BALLANCE_BEFORE" => $userBonus,
						"BALLANCE_AFTER" => $updateUserBonus,
						"NAME" => $arUser["NAME"],
						"LAST_NAME" => $arUser["LAST_NAME"],
						"SECOND_NAME" => $arUser["SECOND_NAME"],
						"LOGIN" => $arUser["LOGIN"],
						"EMAIL" => $arUser["EMAIL"],
						"DETAIL" => $commentBonus,
						"SITE" => $_SERVER['SERVER_NAME'],
						"ORDER_NUM" => $order_num,
						"BONUS_LIVE_DATE" => $bonusLiveDate
					) 
				);
			
			endif;
			
		}
		
		
	}
}










?>