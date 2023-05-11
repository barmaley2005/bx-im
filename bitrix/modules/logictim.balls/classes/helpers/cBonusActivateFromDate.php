<?php
IncludeModuleLangFile(__FILE__);
class cBonusActivateFromDate {
	public static function BonusActivateFromDate() {
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("main");
		CModule::IncludeModule("sale");
		
		//Check work time agents
		$agentWorkTimeFrom = COption::GetOptionString('logictim.balls', 'AGENTS_WORK_TIME_FROM', '00:00');
		$agentWorkTimeTo = COption::GetOptionString('logictim.balls', 'AGENTS_WORK_TIME_TO', '23:59');
		$nowTime = new \Bitrix\Main\Type\DateTime();
		$nowTime = $nowTime->format("H:i");
		
		$arFromTime = explode(':', $agentWorkTimeFrom);
		$arToTime = explode(':', $agentWorkTimeTo);
		$arNowTime = explode(':', $nowTime);
		$fromSecs = $arFromTime[0]*3600 + $arFromTime[1]*60;
		$toSecs = $arToTime[0]*3600 + $arToTime[1]*60;
		$nowSecs = $arNowTime[0]*3600 + $arNowTime[1]*60;
		
		if($nowSecs >= $fromSecs && $nowSecs <= $toSecs){}
		else
			return "cBonusActivateFromDate::BonusActivateFromDate();";
			
		
		//Opredelyaem ID ibfobloka s operaciyami ojidaniya
		$iblokWaitId = cHelper::IblokWaitId();
		
		//Proveryaem, est' li operacii ojidaniya nachisleniya
		$dbWait = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>$iblokWaitId, "ACTIVE"=>"Y", '<=PROPERTY_ACTIVATE_DATE' => date('Y-m-d H:i:s')), false, Array("nPageSize"=>1000));
		$arWaitOperations = array();
		
		while($Op = $dbWait->GetNextElement())
		{
			$OperationWaitFields = $Op->GetFields();
			$OperationWaitFields["PROPS"] = $Op->GetProperties();
			
			$arWaitOperations[$OperationWaitFields["ID"]] = $OperationWaitFields;
		}
		
		if(!empty($arWaitOperations)):
			
			//Opredelyaem ID ibfobloka s operaciyami
				$iblokOperationsId = cHelper::IblokOperationsId();
			//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
				$operationsType = cHelper::OperationsType();
			$operationsStatus = cHelper::LiveStatus();
			
			foreach($arWaitOperations as $waitOperation):
					cBonusActivateFromDate::ActivateFromOrder($waitOperation, $iblokOperationsId, $operationsType, $operationsStatus);
			endforeach;
			
		endif;
		
		
		
		return "cBonusActivateFromDate::BonusActivateFromDate();";
	}
	
	public static function ActivateFromOrder($waitOperation, $iblokOperationsId, $operationsType, $operationsStatus)
	{
		$newOperation = new CIBlockElement;
		$PROP = array();
		
		if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_ORDER' || $waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REFERAL'):
		
			$order_id = $waitOperation["PROPS"]["ORDER_ID"]["VALUE"];
			
			//Esli takogo zakaza uje net, to udalyaem operaciyu ojidaniya
			if(!$order = Bitrix\Sale\Order::load($order_id))
			{
				CIBlockElement::Delete($waitOperation["ID"]);
				return;
			}
			else
				$order = Bitrix\Sale\Order::load($order_id);
				
			//Esli zakaz otmenen, to udalyaem operaciyu ojidaniya
			if($order->isCanceled() == true)
			{
				CIBlockElement::Delete($waitOperation["ID"]);
				return;
			}
			
			$orderFields = $order->GetFields();
			$orderValues = $orderFields->GetValues();
			$order_num = $orderValues["ACCOUNT_NUMBER"];
			
			$PROP["ORDER_ID"] = $order_id;
			
			//for sms send
			$db_order_props = $order->getPropertyCollection();
			$phonePropValue = $db_order_props->getPhone();
			if($phonePropValue)
				$phoneOrder = $phonePropValue->getValue();
		
		endif;
		
		
		
		
		$operationTypeId = $operationsType[$waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"]];
		$user_id = $waitOperation["PROPS"]["USER"]["VALUE"];
		$operation_sum = $waitOperation["PROPS"]["OPERATION_SUM"]["VALUE"];
		$detailBonusProd = $waitOperation["PROPS"]["ADD_DETAIL"]["VALUE"]["TEXT"];
		$operation_name = $waitOperation["NAME"];
		
		$UserBallance = cHelper::UserBallance($user_id);
		$updateUserBonus = $UserBallance + $operation_sum;
	
		//Sozdaem operaciyu nachisleniya
		$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationTypeId);
		$PROP["USER"] = $user_id;
		$PROP["OPERATION_SUM"] = $operation_sum;
		$PROP["BALLANCE_BEFORE"] = $UserBallance;
		$PROP["BALLANCE_AFTER"] = $updateUserBonus;
		$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $detailBonusProd, "TYPE" => "text"));
		
		if($waitOperation["PROPS"]["LIVE_DATE"]["VALUE"] != '')
		{
			$PROP["LIVE_DATE"] = $waitOperation["PROPS"]["LIVE_DATE"]["VALUE"];
			$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
			$PROP["BALLANCE"] = $operation_sum;
		}
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4 && $waitOperation["PROPS"]["LIVE_DATE"]["VALUE"] == '')
		{
			$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
			$liveBonusTime = COption::GetOptionString("logictim.balls", "LIVE_BONUS_TIME", '365');
			if($liveBonus == 'Y') {
				$dateBonusEnd = time() + $liveBonusTime*86400 + 86400;
				$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "SHORT");
				$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
				$PROP["BALLANCE"] = $operation_sum;
			}
		}
		
		$newOperationArray = Array(
								"MODIFIED_BY"    =>  '', 
								"IBLOCK_SECTION" => false,          
								"IBLOCK_ID"      => $iblokOperationsId,
								"IBLOCK_CODE "   => 'logictim_bonus_operations',
								"PROPERTY_VALUES"=> $PROP,
								"NAME"           => $operation_name,
								"ACTIVE"         => "Y",
								"CODE" => 'API_OPERATIONS'
								);
		if($OperationId = $newOperation->Add($newOperationArray));
		
		if($OperationId > 0)
		{
			//Esli ispol'zuetsya bonusniy schet modulya
			if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
			{
				global $DB, $USER_FIELD_MANAGER;
				$USER_FIELD_MANAGER->Update("USER", $user_id, array("UF_LOGICTIM_BONUS" => $updateUserBonus));
			}
			//Esli ispol'zuetsya vnutrenniy schet bitrix
			else
			{
				$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
				CSaleUserAccount::UpdateAccount($user_id, +$operation_sum, $currency, $operation_name, $order_id);
			}
			
			//Udalyaem operaciyu ojidaniya
			CIBlockElement::Delete($waitOperation["ID"]);
			
			//Otpravlyaem email s uvedomleniem. Sozdaem pochtovoe sobitie bitrix
			$rsUser = CUser::GetByID($user_id);
			$arUser = $rsUser->Fetch();
			
			if($PROP["LIVE_DATE"] != '')
				$bonusLiveDate = $PROP["LIVE_DATE"];
			else
				$bonusLiveDate = '-';
			
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_ORDER')
				$mailEvent = "LOGICTIM_BONUS_FROM_ORDER_ADD";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REFERAL')
				$mailEvent = "LOGICTIM_BONUS_FROM_REFERAL_ADD";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REGISTER')
				$mailEvent = "LOGICTIM_BONUS_FROM_REGISTER_ADD";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_BIRTHDAY')
				$mailEvent = "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REPOST')
				$mailEvent = "LOGICTIM_BONUS_FROM_REPOST";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_REVIEW')
				$mailEvent = "LOGICTIM_BONUS_FROM_REVIEW";
			if($waitOperation["PROPS"]["OPERATION_TYPE"]["VALUE_XML_ID"] == 'ADD_FROM_LINK')
				$mailEvent = "LOGICTIM_BONUS_FROM_LINK";
			
			$mailFields = array( 
									"ORDER_ID" => $order_id, 
									"BONUS" => $operation_sum,
									"BALLANCE_BEFORE" => $UserBallance,
									"BALLANCE_AFTER" => $updateUserBonus,
									"NAME" => $arUser["NAME"],
									"LAST_NAME" => $arUser["LAST_NAME"],
									"SECOND_NAME" => $arUser["SECOND_NAME"],
									"LOGIN" => $arUser["LOGIN"],
									"EMAIL" => $arUser["EMAIL"],
									"DETAIL" => $detailBonusProd,
									"SITE" => $_SERVER['SERVER_NAME'],
									"ORDER_NUM" => $order_num,
									"BONUS_LIVE_DATE" => $bonusLiveDate
								);
			$arSites = cHelper::SitesId();
			CEvent::Send($mailEvent, $arSites, $mailFields);
			
			
			//Otpravlyaem SMS
			if(CModule::IncludeModule("messageservice"))
			{
				$smsEvent = $mailEvent.'_SMS';
				$smsFields = $mailFields;
				
				if($phoneOrder)
					$smsFields["USER_PHONE_ORDER"] = $phoneOrder;
				else
					$smsFields["USER_PHONE_ORDER"] = '';
						
				if(isset($arUser["LID"]) && $arUser["LID"] != '')
					$siteId = $arUser["LID"];
				else
					$siteId = $arSites[0];
				
				$smsFields["PERSONAL_PHONE"] = $arUser["PERSONAL_PHONE"];
				$smsFields["PERSONAL_MOBILE"] = $arUser["PERSONAL_MOBILE"];
				$smsFields["WORK_PHONE"] = $arUser["WORK_PHONE"];
				
				if($numberExists = \Bitrix\Main\UserPhoneAuthTable::getList(array("filter" => array("=USER_ID" => $user_id), "select" => array("USER_ID", "PHONE_NUMBER")))->fetch())
						$smsFields["PHONE_NUMBER"] = $numberExists["PHONE_NUMBER"];
				
				$sms = new \Bitrix\Main\Sms\Event($smsEvent, $smsFields);
				$sms->setSite($siteId);
				$res = $sms->send(true);
			}
			
		}
	}
}
?>