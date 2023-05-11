<?php
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
IncludeModuleLangFile(__FILE__);

class cSaleOrderSaved
{
   // static $MODULE_ID="logictim.balls";

	public static function SaleOrderSaved($order)
	{
		global $DB, $USER_FIELD_MANAGER, $USER;
		
		$is_new = $order->isNew();
		
		$order_sum = $order->getPrice();
		
		//Polya zakaza
		$fields = $order->GetFields();
		$values = $fields->GetValues();
		$order_delivery_sum = $values["PRICE_DELIVERY"];
		$order_cart_sum = $order_sum - $order_delivery_sum;
		$user_id = $values["USER_ID"];
		$order_id = $values["ID"];
		$order_num = $values["ACCOUNT_NUMBER"];
		//echo '<pre>'; print_r($values); echo '</pre>';
		
		
		//--- ADD REFERAL FROM COUPON ---//
		$discountData = $order->getDiscount()->getApplyResult();
		if(!empty($discountData["COUPON_LIST"]))
		{
			foreach($discountData["COUPON_LIST"] as $coupon):
				$partnerId = LBReferalsApi::GetPartnerFromCoupon($coupon["COUPON"]);
			endforeach;
			
			if($partnerId > 0)
				LBReferalsApi::AddReferal($referalId = $user_id, $partnerId);
		}
		//--- ADD REFERAL FROM COUPON ---//
		
		
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
		$UserBallance = cHelper::UserBallance($user_id);
		
		if($is_new && $UserBonusSystemDostup == 'Y' && $UserBallance > 0):
		
			//GET ORDER PROPERTIES
			$props = $order->getPropertyCollection();
			foreach($props as $prop) 
			{
				$fields = $prop->GetFields();
				$values = $fields->GetValues();
				//Get property LOGICTIM_PAYMENT_BONUS
				if($values["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
					$pay_bonus = $values["VALUE"];
			}
			
			
			if($pay_bonus > 0):
						
						
				//Minusuem balli u usera
					$updateUserBallance = $UserBallance - $pay_bonus;
				
				
				//-------------------Sozdaem operaciyu spisaniya-------------------------//
				CModule::IncludeModule("iblock");
				//Opredelyaem ID ibfobloka s operaciyami
						$iblokOperationsId = cHelper::IblokOperationsId();
				
				//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
						$operationsType = array();
						$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
						while($enum_fields = $property_enums->GetNext())
						{
							$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
						}
						
				//Esli vklyuchen parametr "srok jizni bonusov"
				$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
				if($liveBonus == 'Y'):
				
					//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
					$operationsStatus = array();
						$status_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"LIVE_STATUS"));
						while($status_fields = $status_enums->GetNext())
						{
							$operationsStatus[$status_fields["XML_ID"]] = $status_fields["ID"];
						}
				
					//Poluchaem spisok nachisleniy s ustanovlennim srokom jizni, i aktivnimi bonusami
					$arrSort = Array("ID" => "ASC");
					$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" );
					$arFilter = Array(
								'IBLOCK_CODE' => 'logictim_bonus_operations',
								//'PROPERTY_OPERATION_TYPE' => array($operationsType['ADD_FROM_ORDER'], $operationsType['USER_BALLANCE_CHANGE']),
								'!PROPERTY_LIVE_DATE' => false,
								'PROPERTY_LIVE_STATUS' => $operationsStatus["ACTIVE"],
								">PROPERTY_BALLANCE" => 0,
								'PROPERTY_USER' => $user_id
								);
				
					$res = CIBlockElement::GetList($arrSort, $arFilter, false, Array("nPageSize"=>PHP_INT_MAX), $arSelect);
				
					$arOperations = array();
					$ostalosPoOperaciyam = 0;
					while($ob = $res->GetNextElement()) {
						$arOp = $ob->GetFields();
						$arOpProps = $ob->GetProperties();
						$arOp = array_merge($arOp, $arOpProps);
						$arOperations[] = $arOp;
						if($arOp["BALLANCE"]["VALUE"] != '') {$ostatok = $arOp["BALLANCE"]["VALUE"];} else {$ostatok = $arOp["OPERATION_SUM"]["VALUE"];}
						$ostalosPoOperaciyam = $ostalosPoOperaciyam + $arOp["BALLANCE"]["VALUE"]*1;
					}
					
					
					//DLYA PERVOGO ISPOL'ZOVANIYA POSLE OBNOVLENIYA MODULYA
					//Esli ballance > 0, a nachisleniya ne ogranicheni po date soraniya, to ves' ballance ogranichivaem datoy sgoraniya (sozdaem operaciyu)
					if($UserBallance > 0 && empty($arOperations) || (int)$UserBallance > (int)$ostalosPoOperaciyam) {
						$newOperation = new CIBlockElement;
						$PROP = array();
						$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['USER_BALLANCE_CHANGE']);
						$PROP["USER"] = $user_id;
						$PROP["OPERATION_SUM"] = 0;
						$PROP["BALLANCE_BEFORE"] = $UserBallance;
						$PROP["BALLANCE_AFTER"] = $UserBallance;
						$PROP["ORDER_ID"] = '';
						$liveDate = COption::GetOptionString("logictim.balls", "LIVE_BONUS_ALL", '');
							if($liveDate != '') {
						$PROP["LIVE_DATE"] = COption::GetOptionString("logictim.balls", "LIVE_BONUS_ALL", '');
							}
							else {
								$PROP["LIVE_DATE"] = '31.12.2099';
							}
						$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
						$PROP["BALLANCE"] = $UserBallance;
						$newOperationArray = Array(
												"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
												"IBLOCK_SECTION" => false,          
												"IBLOCK_ID"      => $iblokOperationsId,
												"IBLOCK_CODE "   => 'logictim_bonus_operations',
												"PROPERTY_VALUES"=> $PROP,
												"NAME"           => GetMessage("logictim.balls_BONUS_LIVE_DATE_SET"),
												"ACTIVE"         => "Y",
												"CODE" => 'API_OPERATIONS'
												);
						if($newId = $newOperation->Add($newOperationArray));
						
						//Deaktiviruem vse starie nachisleniya
						foreach($arOperations as $keyDeactive => $deactive):
							CIBlockElement::SetPropertyValuesEx($deactive["ID"], false, array("LIVE_STATUS" => $operationsStatus["LIVE_END"]));
							unset($arOperations[$keyDeactive]);
						endforeach;
						
						//Dobavlyaem novuyu operaciyu v massiv
						if($newId > 0){
							$arOperations["xxx"]["BALLANCE"]["VALUE"] = $UserBallance;
							$arOperations["xxx"]["PAID"]["VALUE"] = 0;
							$arOperations["xxx"]["ID"] = $newId;
						}
					}
					//DLYA PERVOGO ISPOL'ZOVANIYA POSLE OBNOVLENIYA MODULYA
					
					$paySum = $pay_bonus; // skol'ko vsego bonusov nujno spisat'
					foreach($arOperations as $operationSpisanie):
						$ostalos = $operationSpisanie["BALLANCE"]["VALUE"]*1; //skolko ostalos' ot dannogo nachislenita
						$potracheno = $operationSpisanie["PAID"]["VALUE"]*1;  //skol'ko potracheno s etigo nachisleniya
						
						if($paySum < $ostalos) {
							$ostalos = $ostalos - $paySum;
							$potracheno = $potracheno + $paySum;
							CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno));
							break;
						}
						if($paySum == $ostalos) {
							$ostalos = 0;
							$potracheno = $potracheno + $paySum;
							CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno, "LIVE_STATUS" => $operationsStatus["END"]));
							break;
						}
						if($paySum > $ostalos) {
							$paySum = $paySum - $ostalos;
							$potracheno = $potracheno + $ostalos;
							$ostalos = 0;
							CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno, "LIVE_STATUS" => $operationsStatus["END"]));
						}
						
					endforeach;
				
				endif; //if($liveBonus == 'Y')
				//Esli vklyuchen parametr "srok jizni bonusov"
	
				//Poluchaem ID platega bonusami po zakazu
				$paymentCollection = $order->getPaymentCollection();
				foreach($paymentCollection as $arPayment):
					$fields = $arPayment->GetFields();
					$values = $fields->GetValues();
					$paySystemId = cHelper::PaySystemBonusId();
					if($values["PAY_SYSTEM_ID"] == $paySystemId)
						$paymentId = $values["ID"];
				endforeach;
				
				//Sozdaem operaciyu spisaniya
					$newOperation = new CIBlockElement;
						$PROP = array();
						$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['MINUS_FROM_ORDER']);
						$PROP["USER"] = $user_id;
						$PROP["OPERATION_SUM"] = $pay_bonus;
						$PROP["BALLANCE_BEFORE"] = $UserBallance;
						$PROP["BALLANCE_AFTER"] = $updateUserBallance;
						$PROP["ORDER_ID"] = $order_id;
						$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => GetMessage("logictim.balls_BONUS_FROM_ORDER"), "TYPE" => "text"));
						$PROP["PAYMENT_ID"] = $paymentId;
						$newOperationArray = Array(
												"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
												"IBLOCK_SECTION" => false,          
												"IBLOCK_ID"      => $iblokOperationsId,
												"IBLOCK_CODE "   => 'logictim_bonus_operations',
												"PROPERTY_VALUES"=> $PROP,
												"NAME"           => GetMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num,
												"ACTIVE"         => "Y",
												"CODE" => 'API_OPERATIONS'
												);
						if($newOperation->Add($newOperationArray));
				//-------------------END Sozdaem operaciyu spisaniya-------------------------//
				
				//Sohranyaem ballas polzovatelya
				//Esli ispol'zuetsya bonusniy schet modulya
				if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
				{
					$USER_FIELD_MANAGER->Update("USER", $user_id, array("UF_LOGICTIM_BONUS" => $updateUserBallance));
				}
				//Esli ispol'zuetsya vnutrenniy schet bitrix
				else
				{
					$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
					CSaleUserAccount::UpdateAccount($user_id, -$pay_bonus, $currency, GetMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num, $order_id);
				}
			endif; //END if($pay_bonus > 0)
		endif;
	}
}
