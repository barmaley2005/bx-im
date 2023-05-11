<?
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
Main\Loader::includeModule("iblock");
Main\Loader::includeModule("main");
IncludeModuleLangFile(__FILE__);

class logictimBonusApi
{
	public static function AddBonus($arFields)
	{
		//SOBITIE DO NACHISLENIYA BONUSOV
		$event = new Bitrix\Main\Event("logictim.balls", "BeforeAddBonus", $arFields);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arFields = $eventResult->getParameters();
			endforeach;
		}
		//SOBITIE DO NACHISLENIYA BONUSOV
		
		$user_id = $arFields["USER_ID"];
		$operationCode = $arFields["OPERATION_TYPE"];
		$order_id = $arFields["ORDER_ID"];
		$detailText = $arFields["DETAIL_TEXT"];
		$operationName = $arFields["OPERATION_NAME"];
		$repostId = $arFields["REPOST_ID"];
		$serviceInfo = $arFields["SERVICE_INFO"];
		$externalId = $arFields["EXTERNAL_ID"];
		if(isset($arFields["MAIL_EVENT"]) && is_array($arFields["MAIL_EVENT"]) && $arFields["MAIL_EVENT"]["EVENT_NAME"] != '')
		{
			$mailEvent = $arFields["MAIL_EVENT"]["EVENT_NAME"];
			$mailCustomFields = $arFields["MAIL_EVENT"]["CUSTOM_FIELDS"]; //array();
		}
		if(isset($arFields["SMS_EVENT"]) && is_array($arFields["SMS_EVENT"]) && $arFields["SMS_EVENT"]["EVENT_NAME"] != '')
		{
			$smsEvent = $arFields["SMS_EVENT"]["EVENT_NAME"];
			$smsCustomFields = $arFields["SMS_EVENT"]["CUSTOM_FIELDS"]; //array();
		}
		
		//--- Activate date ---//
		if((int)$arFields["ACTIVE_AFTER"] > 0):
			if($arFields["ACTIVE_AFTER_TYPE"] != 'D' && $arFields["ACTIVE_AFTER_TYPE"] != 'M')
				$arFields["ACTIVE_AFTER_TYPE"] = 'D';
				
			if($arFields["ACTIVE_AFTER_TYPE"] == 'D')	
				$dateActivate = strtotime("+".(int)$arFields["ACTIVE_AFTER"]." day", time());
			elseif($arFields["ACTIVE_AFTER_TYPE"] == 'M')
				$dateActivate = strtotime("+".(int)$arFields["ACTIVE_AFTER"]." month", time());
		endif;
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4 && !$dateActivate && $operationCode == 'ADD_FROM_ORDER'):
			$daysDelay = (int)COption::GetOptionString("logictim.balls", "BONUS_ORDER_WAIT", 0);
			if($daysDelay > 0)
				$dateActivate = time() + $daysDelay*86400;
		endif;
		//--- Activate date ---//
		
		//--- Deactivate date ---//
		if((int)$arFields["DEACTIVE_AFTER"] > 0):
			if($arFields["DEACTIVE_AFTER_TYPE"] != 'D' && $arFields["DEACTIVE_AFTER_TYPE"] != 'M')
				$arFields["DEACTIVE_AFTER_TYPE"] = 'D';
			
			if($arFields["DEACTIVE_AFTER_TYPE"] == 'D')
				if($dateActivate > 0)
					$dateBonusEnd = strtotime("+".(int)$arFields["DEACTIVE_AFTER"]." day", $dateActivate);
				else
					$dateBonusEnd = strtotime("+".(int)$arFields["DEACTIVE_AFTER"]." day", time());
			elseif($arFields["DEACTIVE_AFTER_TYPE"] == 'M')
				if($dateActivate > 0)
					$dateBonusEnd = strtotime("+".(int)$arFields["DEACTIVE_AFTER"]." month", $dateActivate);
				else
					$dateBonusEnd = strtotime("+".(int)$arFields["DEACTIVE_AFTER"]." month", time());
		endif;
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4 && !$dateBonusEnd):
			$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
			if($liveBonus == 'Y')
			{
				$liveBonusTime = COption::GetOptionString("logictim.balls", "LIVE_BONUS_TIME", '365');
				if($dateActivate > 0)
					$dateBonusEnd = $dateActivate + $liveBonusTime*86400 + 86400;
				else
					$dateBonusEnd = time() + $liveBonusTime*86400 + 86400;
			}
		endif;
		//--- Deactivate date ---//
		
		
		if((int)COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4):
			$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
			$addBonus = round($arFields["ADD_BONUS"], $round);
			$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
			$UserBallance = cHelper::UserBallance($user_id);
		else:
			$addBonus = $arFields["ADD_BONUS"];
			$UserBonusSystemDostup = 'Y';
			$UserBallance = cHelper::UserBallance($user_id);
		endif;
		
		
		if($UserBonusSystemDostup == 'Y' && $addBonus > 0 && $user_id > 0):
			
			//Sozdaem operaciyu ojidaniya
			if($dateActivate > 0)
			{
				$iblokWaitId = cHelper::IblokWaitId();
				$operationsTypeWait = cHelper::OperationsTypeWait();
				
				$newOperation = new CIBlockElement;
				$PROP = array();
				$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsTypeWait[$operationCode]);
				$PROP["USER"] = $user_id;
				$PROP["OPERATION_SUM"] = $addBonus;
				$PROP["ORDER_ID"] = $order_id;
				$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $detailText, "TYPE" => "text"));
				$PROP["ACTIVATE_DATE"] = ConvertTimeStamp($dateActivate, "SHORT");
				if($dateBonusEnd > 0)
					$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "SHORT");
				
				$newOperationArray = Array(
										"MODIFIED_BY"    =>  $user_id, 
										"IBLOCK_SECTION" => false,          
										"IBLOCK_ID"      => $iblokWaitId,
										"IBLOCK_CODE "   => 'logictim_bonus_wait',
										"EXTERNAL_ID" => $externalId,
										"PROPERTY_VALUES"=> $PROP,
										"NAME"           => $operationName,
										"ACTIVE"         => "Y",
										"CODE" => 'API_OPERATIONS'
										);
				if($operationId = $newOperation->Add($newOperationArray))
				{
					$operationId;
					//SOBITIE POSLE DOBAVLENIYA OJIDANIYA BONUSOV
					$newOperationArray["OPERATION_ID"] = $operationId;
					unset($newOperationArray["CODE"]);
					$eventInfo = array("OPERATION_INFO" => $newOperationArray, "MAIL_EVENT" => array("EVENT" => $mailEvent), "OPERATIONS_TYPE" => $operationsType);
					$event = new Bitrix\Main\Event("logictim.balls", "AfterAddBonusWait", $eventInfo);
					$event->send();
					//SOBITIE POSLE DOBAVLENIYA OJIDANIYA BONUSOV
				}
				else
					$operationId = false;
				
				//Otmechaem v zakaze, chto bonusi nachisleni
				if($operationId > 0 && $operationCode == 'ADD_FROM_ORDER' && $order_id > 0)
				{
					//Poluchaem svoystva zakaza
					$order = \Bitrix\Sale\Order::load($order_id);
					$order_props = array();
					$db_order_props = $order->getPropertyCollection();
					foreach($db_order_props as $order_prop) 
					{
						$fields = $order_prop->GetFields();
						$values = $fields->GetValues();
						$order_props[$values["CODE"]] = $values;
					}
					$addBonusProp = $db_order_props->getItemByOrderPropertyId($order_props["LOGICTIM_ADD_BONUS"]["ORDER_PROPS_ID"]);
					$addBonusProp->setValue($addBonus);
					$addBonusProp->save();
				}
				
			}
			
			else
			{
				//Sozdaem operaciyu nachisleniya
				$iblokOperationsId = cHelper::IblokOperationsId();
				$operationsType = cHelper::OperationsType();
				$operationsStatus = cHelper::LiveStatus();
				
				$newOperation = new CIBlockElement;
				$PROP = array();
				
				$updateUserBonus = $UserBallance + $addBonus;
				
				$PROP["USER"] = $user_id;
				$PROP["OPERATION_SUM"] = $addBonus;
				$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType[$operationCode]);
				$PROP["BALLANCE_BEFORE"] = $UserBallance;
				$PROP["BALLANCE_AFTER"] = $updateUserBonus;
				$PROP["ORDER_ID"] = $order_id;
				$PROP["REPOST_ID"] = $repostId;
				$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $detailText, "TYPE" => "text"));
				$PROP["SERVICE_INFO"] = $serviceInfo;
				if($dateBonusEnd > 0)
				{
					$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "SHORT");
					$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
					$PROP["BALLANCE"] = $addBonus;
				}
				
				
				$newOperationArray = Array(
										"MODIFIED_BY"    =>  $user_id, 
										"IBLOCK_SECTION" => false,          
										"IBLOCK_ID"      => $iblokOperationsId,
										"IBLOCK_CODE "   => 'logictim_bonus_operations',
										"EXTERNAL_ID" => $externalId,
										"PROPERTY_VALUES"=> $PROP,
										"NAME"           => $operationName,
										"ACTIVE"         => "Y",
										"CODE" => 'API_OPERATIONS'
										);
										
				if($operationId = $newOperation->Add($newOperationArray))
				{
					$operationId;
					//SOBITIE POSLE NACHISLENIYA BONUSOV
					$newOperationArray["OPERATION_ID"] = $operationId;
					unset($newOperationArray["CODE"]);
					$eventInfo = array("OPERATION_INFO" => $newOperationArray, "MAIL_EVENT" => array("EVENT" => $mailEvent), "OPERATIONS_TYPE" => $operationsType);
					$event = new Bitrix\Main\Event("logictim.balls", "AfterAddBonus", $eventInfo);
					$event->send();
					//SOBITIE POSLE NACHISLENIYA BONUSOV
				}
				else
					$operationId = false;
					
				//Nachislyaem bonusi useru
				if($operationId > 0)
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
						CSaleUserAccount::UpdateAccount($user_id, +$addBonus, $currency, $operationName, $order_id);
					}
				}
				
				//Otmechaem v zakaze, chto bonusi nachisleni
				if($operationId > 0 && $operationCode == 'ADD_FROM_ORDER' && $order_id > 0)
				{
					//Poluchaem svoystva zakaza
					$order = \Bitrix\Sale\Order::load($order_id);
					$order_props = array();
					$db_order_props = $order->getPropertyCollection();
					foreach($db_order_props as $order_prop) 
					{
						$fields = $order_prop->GetFields();
						$values = $fields->GetValues();
						$order_props[$values["CODE"]] = $values;
					}
					$addBonusProp = $db_order_props->getItemByOrderPropertyId($order_props["LOGICTIM_ADD_BONUS"]["ORDER_PROPS_ID"]);
					$addBonusProp->setValue($addBonus);
					$addBonusProp->save();
					
					//for sms send
					$phonePropValue = $db_order_props->getPhone();
					if($phonePropValue)
						$phoneOrder = $phonePropValue->getValue();
				}
				
				
				//For mail and SMS
				if($operationId > 0 && $mailEvent || $operationId > 0 && $smsEvent):
					$rsUser = CUser::GetByID($user_id);
					$arUser = $rsUser->Fetch();
					
					$arSiteId = cHelper::SitesId();
					if($order_id > 0)
					{
						$order = Bitrix\Sale\Order::load($order_id);
						$siteId = $order->getSiteId();
					}
					else
						$siteId = $arUser["LID"];
					if(!in_array($siteId, $arSiteId))
						$siteId = $arSiteId;
					
					
					if($dateBonusEnd > 0)
						$bonusLiveDate = ConvertTimeStamp($dateBonusEnd, "SHORT");
					else
						$bonusLiveDate = '-';
					
					$rsSite = CSite::GetByID($siteId);
					$arSite = $rsSite->Fetch();
					if($arSite["SERVER_NAME"] != '')
						$domain = $arSite["SERVER_NAME"];
					else
						$domain = $_SERVER['SERVER_NAME'];
					$siteUrl =$arSite["SITE_URL"];
				endif;
				
				//Send email
				if($operationId > 0 && $mailEvent):
					$mailFields = array( 
											"ORDER_ID" => $order_id, 
											"BONUS" => $addBonus,
											"BALLANCE_BEFORE" => $UserBallance,
											"BALLANCE_AFTER" => $updateUserBonus,
											"NAME" => $arUser["NAME"],
											"LAST_NAME" => $arUser["LAST_NAME"],
											"SECOND_NAME" => $arUser["SECOND_NAME"],
											"LOGIN" => $arUser["LOGIN"],
											"EMAIL" => $arUser["EMAIL"],
											"SITE" => $domain,
											"SITE_URL" => $siteUrl,
											"BONUS_LIVE_DATE" => $bonusLiveDate,
											"OPERATION_NAME" => $operationName
										) ;
					if(!empty($mailCustomFields))
						$mailFields = $mailFields + $mailCustomFields;
					CEvent::Send($mailEvent, $siteId, $mailFields, 'N', '', array(), '');
				endif;
				
				
				//Send SMS
				if($operationId > 0 && $smsEvent && CModule::IncludeModule("messageservice")):
					$smsFields = $mailFields;
					
					if($phoneOrder)
						$smsFields["USER_PHONE_ORDER"] = $phoneOrder;
					else
						$smsFields["USER_PHONE_ORDER"] = '';
						
					$smsFields["PERSONAL_PHONE"] = $arUser["PERSONAL_PHONE"];
					$smsFields["PERSONAL_MOBILE"] = $arUser["PERSONAL_MOBILE"];
					$smsFields["WORK_PHONE"] = $arUser["WORK_PHONE"];
					
					if($numberExists = \Bitrix\Main\UserPhoneAuthTable::getList(array("filter" => array("=USER_ID" => $user_id), "select" => array("USER_ID", "PHONE_NUMBER")))->fetch())
						$smsFields["PHONE_NUMBER"] = $numberExists["PHONE_NUMBER"];
					
					if(!empty($smsCustomFields))
						$smsFields = $smsFields + $smsCustomFields;
					
					$sms = new \Bitrix\Main\Sms\Event($smsEvent, $smsFields);
					$sms->setSite($siteId);
					$res = $sms->send(true);
					
					//For Log
					$arErrors = array();
					$errors = $res->geterrors();
					foreach($errors as $error):
						$er = $error->getmessage();
						if($er != '')
							$arErrors[] = $er;
					endforeach;
					//For Log
					
				endif;
				
			}
			//cHelper::UserBonusSystemDostupNew();
			
		endif;
		
		return $operationId;
	}
	
	public static function MinusBonus($arFields)
	{
		//SOBITIE DO SPISANIYA BONUSOV
		$event = new Bitrix\Main\Event("logictim.balls", "BeforeMinusBonus", $arFields);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arFields = $eventResult->getParameters();
			endforeach;
		}
		//SOBITIE DO SPISANIYA BONUSOV
		
		CModule::IncludeModule("iblock");
		
		$user_id = $arFields["USER_ID"];
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
		{
			$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
			$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
			$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		}
		else
		{
			$liveBonus = 'Y';
			$UserBonusSystemDostup = 'Y';
			$round = 2;
		}
		
		
		$operationSum = round($arFields["MINUS_BONUS"], $round);
		$operationCode = $arFields["OPERATION_TYPE"];
		$order_id = $arFields["ORDER_ID"];
		$detailText = $arFields["DETAIL_TEXT"];
		$operationName = $arFields["OPERATION_NAME"];
		$repostId = $arFields["REPOST_ID"];
		$serviceInfo = $arFields["SERVICE_INFO"];
		$externalId = $arFields["EXTERNAL_ID"];
		if(isset($arFields["MAIL_EVENT"]) && $arFields["MAIL_EVENT"]["EVENT_NAME"] != '')
		{
			$mailEvent = $arFields["MAIL_EVENT"]["EVENT_NAME"];
			$mailCustomFields = $arFields["MAIL_EVENT"]["CUSTOM_FIELDS"]; //array();
		}
		
		
		$UserBallance = cHelper::UserBallance($user_id);
		
		if($operationSum > $UserBallance)
			$operationSum = $UserBallance;
		
		if($UserBonusSystemDostup == 'Y' && $user_id > 0 && $operationSum > 0 && $UserBallance >= $operationSum):
			
			$NewUserBallance = $UserBallance - $operationSum;
			
			//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = cHelper::IblokOperationsId();
			
			//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = cHelper::OperationsType();
			
			//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
			$operationsStatus = cHelper::LiveStatus();
			
			
			//Esli vklyuchen parametr "srok jizni bonusov"
			if($liveBonus == 'Y'):
				//Poluchaem spisok nachisleniy s ustanovlennim srokom jizni, i aktivnimi bonusami
				$arFilter = Array(
							'IBLOCK_CODE' => 'logictim_bonus_operations',
							//'PROPERTY_OPERATION_TYPE' => array($operationsType['ADD_FROM_ORDER'], $operationsType['USER_BALLANCE_CHANGE']),
							'!PROPERTY_LIVE_DATE' => false,
							'PROPERTY_LIVE_STATUS' => $operationsStatus["ACTIVE"],
							">PROPERTY_BALLANCE" => 0,
							'PROPERTY_USER' => $user_id
							);
			
				$res = CIBlockElement::GetList(array("PROPERTY_LIVE_DATE" => "ASC", "ID" => "ASC"), $arFilter, false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" ));
			
				$arOperations = array();
				$ostalosPoOperaciyam = 0;
				while($ob = $res->GetNextElement())
				{
					$arOp = $ob->GetFields();
					$arOpProps = $ob->GetProperties();
					$arOp = array_merge($arOp, $arOpProps);
					$arOperations[] = $arOp;
					if($arOp["BALLANCE"]["VALUE"] != '')
						$ostatok = $arOp["BALLANCE"]["VALUE"]; 
					else
						$ostatok = $arOp["OPERATION_SUM"]["VALUE"];
					$ostalosPoOperaciyam = $ostalosPoOperaciyam + $arOp["BALLANCE"]["VALUE"]*1;
				}
				
				//--- Esli ne sovpadaet ostatok po operaciyam i balans pol'zovatelya --//
				if($UserBallance > 0 && empty($arOperations) || (int)$UserBallance > (int)$ostalosPoOperaciyam)
				{
					$newOperation = new CIBlockElement;
					$PROP = array();
					$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['USER_BALLANCE_CHANGE']);
					$PROP["USER"] = $user_id;
					$PROP["OPERATION_SUM"] = 0;
					$PROP["BALLANCE_BEFORE"] = $UserBallance;
					$PROP["BALLANCE_AFTER"] = $UserBallance;
					$PROP["ORDER_ID"] = '';
					$PROP["LIVE_DATE"] = '31.12.2099';
					$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
					$PROP["BALLANCE"] = $UserBallance;
					$newOperationArray = Array(
											"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
											"IBLOCK_SECTION" => false,          
											"IBLOCK_ID"      => $iblokOperationsId,
											"IBLOCK_CODE "   => 'logictim_bonus_operations',
											"PROPERTY_VALUES"=> $PROP,
											"NAME"           => GetMessage("logictim.balls_BONUS_SET_TIME_LIMIT"),
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
						$arOperations["xxx"]["LIVE_DATE"]["VALUE"] = $PROP["LIVE_DATE"];
					}
				}
				//--- Esli ne sovpadaet ostatok po operaciyam i balans pol'zovatelya --//
				
				$arOperationsUse = array();
				$paySum = $operationSum = round($operationSum, $round); // skol'ko vsego bonusov nujno spisat'
				foreach($arOperations as $operationSpisanie):
					$ostalos = round((float)$operationSpisanie["BALLANCE"]["VALUE"], $round); //skolko ostalos' ot dannogo nachislenita
					$potracheno = round((float)$operationSpisanie["PAID"]["VALUE"], $round);  //skol'ko potracheno s etogo nachisleniya
					
					if($paySum < $ostalos) {
						$ostalos = $ostalos - $paySum;
						$potracheno = $potracheno + $paySum;
						$payFromOperation = $paySum;
						if($payFromOperation > 0)
							$arOperationsUse[$operationSpisanie["ID"]] = array(
																			"OPERATION_ADD_ID" => $operationSpisanie["ID"], 
																			"PAY_FROM_OPERATION" => $payFromOperation, 
																			"LIVE_DATE" => $operationSpisanie["LIVE_DATE"]["VALUE"], 
																			"DATE_CREATE"=>$operationSpisanie["DATE_CREATE"]
																			);
						CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno));
						break;
					}
					if($paySum == $ostalos) {
						$ostalos = 0;
						$potracheno = $potracheno + $paySum;
						$payFromOperation = $paySum;
						if($payFromOperation > 0)
							$arOperationsUse[$operationSpisanie["ID"]] = array(
																			"OPERATION_ADD_ID" => $operationSpisanie["ID"], 
																			"PAY_FROM_OPERATION" => $payFromOperation, 
																			"LIVE_DATE" => $operationSpisanie["LIVE_DATE"]["VALUE"], 
																			"DATE_CREATE"=>$operationSpisanie["DATE_CREATE"]
																			);
						CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno, "LIVE_STATUS" => $operationsStatus["END"]));
						break;
					}
					if($paySum > $ostalos) {
						$paySum = $paySum - $ostalos;
						$potracheno = $potracheno + $ostalos;
						$payFromOperation = $ostalos;
						$ostalos = 0;
						if($payFromOperation > 0)
							$arOperationsUse[$operationSpisanie["ID"]] = array(
																			"OPERATION_ADD_ID" => $operationSpisanie["ID"], 
																			"PAY_FROM_OPERATION" => $payFromOperation, 
																			"LIVE_DATE" => $operationSpisanie["LIVE_DATE"]["VALUE"], 
																			"DATE_CREATE"=>$operationSpisanie["DATE_CREATE"]
																			);
						CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("BALLANCE" => $ostalos, "PAID" => $potracheno, "LIVE_STATUS" => $operationsStatus["END"]));
					}
					
				endforeach;
			endif; //if($liveBonus == 'Y')
			
			if(!empty($arOperationsUse))
			{
				$serviceInfo = serialize($arOperationsUse);
				$detailText .= 'Pri oplate ispolzovani nachisleniya:'."\n";
				foreach($arOperationsUse as $useOperation):
					$detailText .= 'ID='.$useOperation["OPERATION_ADD_ID"].' spisano s nachisleniya='.$useOperation["PAY_FROM_OPERATION"].' srok jizni nachisleniya='.$useOperation["LIVE_DATE"]."\n";
				endforeach;
			}
			
			//Sozdaem operaciyu spisaniya
			$newOperation = new CIBlockElement;
			$PROP = array();
			$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType[$operationCode]);
			$PROP["USER"] = $user_id;
			$PROP["OPERATION_SUM"] = $operationSum;
			$PROP["BALLANCE_BEFORE"] = $UserBallance;
			$PROP["BALLANCE_AFTER"] = $NewUserBallance;
			$PROP["ORDER_ID"] = $order_id;
			$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => $detailText, "TYPE" => "text"));
			$PROP["SERVICE_INFO"] = $serviceInfo;
			$newOperationArray = Array(
										"MODIFIED_BY"    =>  $user_id, 
										"IBLOCK_SECTION" => false,          
										"IBLOCK_ID"      => $iblokOperationsId,
										"IBLOCK_CODE "   => 'logictim_bonus_operations',
										"EXTERNAL_ID" => $externalId,
										"PROPERTY_VALUES"=> $PROP,
										"NAME"           => $operationName,
										"ACTIVE"         => "Y",
										"CODE" => 'API_OPERATIONS'
										);
			if($operationId = $newOperation->Add($newOperationArray))
			{
				$operationId;
				//SOBITIE POSLE SPISANIYA BONUSOV
				$newOperationArray["OPERATION_ID"] = $operationId;
				unset($newOperationArray["CODE"]);
				$eventInfo = array("OPERATION_INFO" => $newOperationArray, "MAIL_EVENT" => array("EVENT" => $mailEvent), "OPERATIONS_TYPE" => $operationsType);
				$event = new Bitrix\Main\Event("logictim.balls", "AfterMinusBonus", $eventInfo);
				$event->send();
				//SOBITIE POSLE SPISANIYA BONUSOV
			}
			else
				$operationId = false;
				
			//Spisivaem bonusi u useru
			if($operationId > 0)
			{
				//Esli ispol'zuetsya bonusniy schet modulya
				if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
				{
					global $USER, $DB, $USER_FIELD_MANAGER;
					$USER_FIELD_MANAGER->Update("USER", $user_id, array("UF_LOGICTIM_BONUS" => $NewUserBallance));
				}
				//Esli ispol'zuetsya vnutrenniy schet bitrix
				else
				{
					$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
					CSaleUserAccount::UpdateAccount($user_id, -$operationSum, $currency, $operationsName["MINUS_FROM_ORDER"].' '.$order_num, $order_id);
				}
			}
			
			//Otmechaem v zakaze, chto bonusi spisani
			if($operationId > 0 && $operationCode == 'MINUS_FROM_ORDER_CANCEL' && $order_id > 0)
			{
				//Poluchaem svoystva zakaza
				$order = \Bitrix\Sale\Order::load($order_id);
				$order_props = array();
				$db_order_props = $order->getPropertyCollection();
				foreach($db_order_props as $order_prop) 
				{
					$fields = $order_prop->GetFields();
					$values = $fields->GetValues();
					$order_props[$values["CODE"]] = $values;
				}
				$addBonusProp = $db_order_props->getItemByOrderPropertyId($order_props["LOGICTIM_ADD_BONUS"]["ORDER_PROPS_ID"]);
				$addBonusProp->setValue(0);
				$addBonusProp->save();
			}
			
			//Send email
			if($operationId > 0 && $mailEvent):
				$rsUser = CUser::GetByID($user_id);
				$arUser = $rsUser->Fetch();
				
				$arSiteId = cHelper::SitesId();
				if($order_id > 0)
				{
					$order = Bitrix\Sale\Order::load($order_id);
					$siteId = $order->getSiteId();
				}
				else
					$siteId = $arUser["LID"];
				if(!in_array($siteId, $arSiteId))
					$siteId = $arSiteId;
				
				if($liveBonus == 'Y')
					$bonusLiveDate = ConvertTimeStamp($dateBonusEnd, "SHORT");
				else
					$bonusLiveDate = '-';
				$mailFields = array( 
										"ORDER_ID" => $order_id, 
										"BONUS" => $addBonus,
										"BALLANCE_BEFORE" => $UserBallance,
										"BALLANCE_AFTER" => $updateUserBonus,
										"NAME" => $arUser["NAME"],
										"LAST_NAME" => $arUser["LAST_NAME"],
										"SECOND_NAME" => $arUser["SECOND_NAME"],
										"LOGIN" => $arUser["LOGIN"],
										"EMAIL" => $arUser["EMAIL"],
										"SITE" => $_SERVER['SERVER_NAME'],
										"BONUS_LIVE_DATE" => $bonusLiveDate,
										"OPERATION_NAME" => $operationName
									);
				if(!empty($mailCustomFields))
				{
					$mailFields = $mailFields + $mailCustomFields;
				}
				CEvent::Send($mailEvent, $siteId, $mailFields, 'N', '', array(), '');
			endif;
			
			return $operationId;
		
		else:
			return 'Ne dostatochno sredstv na scete';
		endif;
		
	}
}
?>