<?php
IncludeModuleLangFile(__FILE__);
class cBonusDeactivateFromDate {
	
	public static function BonusWarningFromDate() {
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
			return "cBonusDeactivateFromDate::BonusWarningFromDate();";
		
		
		//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = cHelper::IblokOperationsId();
			
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = cHelper::OperationsType();
	
		//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
			$operationsStatus = cHelper::LiveStatus();
			
			
		//---------PREDUPREJDENIE O SGORANII BONUSOV---------//
		$countWarningDay = (string)COption::GetOptionString("logictim.balls", "COUNT_DAY_WARNING", 0);
		if($countWarningDay == '0')
			return "cBonusDeactivateFromDate::BonusWarningFromDate();";
			
		$warningDaysExp = explode(',', $countWarningDay);
		$arWarningDays = array();
		if(!empty($warningDaysExp))
		{
			foreach($warningDaysExp as $wd):
				$arWarningDays[] = (int)$wd;
			endforeach;
		}
			
		
		$arParamsWarning_str = file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/logictim.balls/classes/helpers/temp.txt");
		$arParamsWarning = unserialize($arParamsWarning_str);
		
		
		if(!$arParamsWarning || !is_array($arParamsWarning) || $arParamsWarning["TODAY"] != date('Y-m-d', time()))
			$arParamsWarning = array("MAX_LIST" => 500, "TODAY" => date('Y-m-d', time()), "WARNING_DAY_KEY" => 0, "LAST_OPERATION_ID" => 0);
		else
		{
			if($arParamsWarning['WARNING_DAYS'] != $arWarningDays)
			{
				\Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/logictim.balls/classes/helpers/temp.txt");
				$arParamsWarning = array("MAX_LIST" => 500, "TODAY" => date('Y-m-d', time()), "WARNING_DAY_KEY" => 0, "LAST_OPERATION_ID" => 0);
			}
			
			if($arParamsWarning["END"] == 'Y' && $arParamsWarning["TODAY"] == date('Y-m-d', time()))
				return "cBonusDeactivateFromDate::BonusWarningFromDate();";
				
		}
		$arParamsWarning['WARNING_DAYS'] = $arWarningDays;
		
		foreach($arParamsWarning['WARNING_DAYS'] as $warningDayKey => $warningDay):
			if($warningDayKey != $arParamsWarning['WARNING_DAY_KEY'])
				continue;
				
			$arMessages = array();
			
			//Poluchaem spisok operaciy dlya preduprejdeniya o sgoranii bonusov
			$date = date('Y-m-d', time() + $warningDay*86400);
			$arFilterWarning = Array(
						'IBLOCK_CODE' => 'logictim_bonus_operations',
						'>ID' => $arParamsWarning["LAST_OPERATION_ID"],
						'<=PROPERTY_LIVE_DATE' => $date.' 23:59:59',
						'>=PROPERTY_LIVE_DATE' => $date.' 0:00:00',
						'PROPERTY_LIVE_STATUS' => $operationsStatus["ACTIVE"],
						">PROPERTY_BALLANCE" => 0,
						);
			$resWarning = CIBlockElement::GetList(array("ID" => "ASC"), $arFilterWarning, false, Array("nPageSize"=>$arParamsWarning["MAX_LIST"]), array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" ));
			
			$arOperationsWarning = array();
			$usersIdWarning = array();
			while($obWarning = $resWarning->GetNextElement()) {
				$arOpWarning = $obWarning->GetFields();
				$arOpPropsWarning = $obWarning->GetProperties();
				$arOpWarning = array_merge($arOpWarning, $arOpPropsWarning);
				$arOperationsWarning[] = $arOpWarning;
				$usersIdWarning[] = $arOpWarning["USER"]["VALUE"];
				
				if(isset($arMessages[$arOpWarning["USER"]["VALUE"]]))
				{
					$arMessages[$arOpWarning["USER"]["VALUE"]]["OPERATIONS_LIST"][] = $arOpWarning["ID"];
					$arMessages[$arOpWarning["USER"]["VALUE"]]["OPERATIONS_SUM"] = $arMessages[$arOpWarning["USER"]["VALUE"]]["OPERATIONS_SUM"] + $arOpWarning["BALLANCE"]["VALUE"];
				}
				else
				{
					$arMessages[$arOpWarning["USER"]["VALUE"]] = array(
																		"USER" => array("ID"=>$arOpWarning["USER"]["VALUE"]),
																		"OPERATIONS_LIST" => array($arOpWarning["ID"]),
																		"OPERATIONS_SUM" => $arOpWarning["BALLANCE"]["VALUE"],
																		"LIVE_DATE" => $arOpWarning["LIVE_DATE"]["VALUE"]
																		);
				}
				
				$arParamsWarning["LAST_OPERATION_ID"] = $arOpWarning["ID"];
			}
			
			//Poluchaem spisok userov dlya preduprejdeniya
			if(!empty($usersIdWarning))
			{
				$usersIdWarning = array_unique($usersIdWarning);
				$arParamsW["SELECT"] = array("UF_LOGICTIM_BONUS", "UF_LGB_SUBSCRIBE");
				$rsUsersW = CUser::GetList(($by="ID"), ($order="desc"), array("ID" => implode('|', $usersIdWarning)), $arParamsW);
				$usersInfoW = array();
				while($arUserW = $rsUsersW->Fetch()) 
				{
					$usersInfoW[$arUserW["ID"]] = $arUserW;
					
					if(isset($arMessages[$arUserW["ID"]]))
						$arMessages[$arUserW["ID"]]["USER"] = $arUserW;
				}
			}
			
			if(!empty($arMessages))
			{
				foreach($arMessages as $arMessage):
					if($arMessage["USER"]["UF_LGB_SUBSCRIBE"] == 1)
					{
						$mailFields = array( 
								"BONUS" => $arMessage["OPERATIONS_SUM"],
								"BALLANCE_USER" => cHelper::UserBallance($arMessage["USER"]["ID"]),
								"NAME" => $arMessage["USER"]["NAME"],
								"LAST_NAME" => $arMessage["USER"]["LAST_NAME"],
								"LOGIN" => $arMessage["USER"]["LOGIN"],
								"EMAIL" => $arMessage["USER"]["EMAIL"],
								"SITE" => $_SERVER['SERVER_NAME'],
								"BONUS_LIVE_DATE" => $arMessage["LIVE_DATE"]
							);
						$arSites = cHelper::SitesId();
						CEvent::Send("LOGICTIM_BONUS_WARNING_END_TIME", $arSites, 
							$mailFields,
							'N', '', array(), ''
						);
						
						//Otpravlyaem SMS
						if(CModule::IncludeModule("messageservice"))
						{
							if(isset($arMessage["USER"]["LID"]) && $arMessage["USER"]["LID"] != '')
								$siteId = $arMessage["USER"]["LID"];
							else
								$siteId = $arSites[0];
							$smsFields = $mailFields;
							$smsFields["PERSONAL_PHONE"] = $arMessage["USER"]["PERSONAL_PHONE"];
							$smsFields["PERSONAL_MOBILE"] = $arMessage["USER"]["PERSONAL_MOBILE"];
							$smsFields["WORK_PHONE"] = $arMessage["USER"]["WORK_PHONE"];
							
							if($numberExists = \Bitrix\Main\UserPhoneAuthTable::getList(array("filter" => array("=USER_ID" => $arMessage["USER"]["ID"]), "select" => array("USER_ID", "PHONE_NUMBER")))->fetch())
								$smsFields["PHONE_NUMBER"] = $numberExists["PHONE_NUMBER"];
							
							$sms = new \Bitrix\Main\Sms\Event('LOGICTIM_BONUS_WARNING_END_TIME_SMS', $smsFields);
							$sms->setSite($siteId);
							$res = $sms->send(true);
						}
					}
				endforeach;
			}
			
		endforeach;
		
		
		//end foreach of days
		if(count($arOperationsWarning) < $arParamsWarning["MAX_LIST"])
		{
			$nextDayKey = $arParamsWarning['WARNING_DAY_KEY']+1;
			
			if(isset($arParamsWarning['WARNING_DAYS'][$nextDayKey]))
			{
				$arParamsWarning['WARNING_DAY_KEY'] = $nextDayKey;
				$arParamsWarning["LAST_OPERATION_ID"] = 0;
			}
			else
				$arParamsWarning["END"] = 'Y';
		}
		
		$arParamsWarning_str = serialize($arParamsWarning);
		$f = fopen($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/logictim.balls/classes/helpers/temp.txt", 'w');
		fwrite($f, $arParamsWarning_str);
		fclose($f);
		
		
		
		//---------PREDUPREJDENIE O SGORANII BONUSOV---------//
		
		return "cBonusDeactivateFromDate::BonusWarningFromDate();";
	}
	
	public static function BonusDeactivateFromDate() {
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("main");
		CModule::IncludeModule("sale");
		
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		
			//Esli vklyuchen parametr "srok jizni bonusov"
			if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
				$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
			else
				$liveBonus = 'Y';
				
			if($liveBonus == 'Y'):
			
				//Opredelyaem ID ibfobloka s operaciyami
					$iblokOperationsId = cHelper::IblokOperationsId();
					
				//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
					$operationsType = cHelper::OperationsType();
			
				//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
					$operationsStatus = cHelper::LiveStatus();
			
				
				
				
				
				//Poluchaem spisok prosrochennih ballov (operaciy)
				$arrSort = Array("ID" => "ASC");
				$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" );
				$arFilter = Array(
							'IBLOCK_CODE' => 'logictim_bonus_operations',
							'<=PROPERTY_LIVE_DATE' => date('Y-m-d H:i:s'),
							'PROPERTY_LIVE_STATUS' => $operationsStatus["ACTIVE"],
							">PROPERTY_BALLANCE" => 0,
							//'PROPERTY_USER' => $USER->GetID()
							);
			
				$res = CIBlockElement::GetList($arrSort, $arFilter, false, Array("nPageSize"=>1000), $arSelect);
			
				//Sostavlyaem spisok operaciy dlya deaktivacii, i spisok userov dlya izmeneniya ballanca
				$arOperations = array();
				$usersId = array();
				while($ob = $res->GetNextElement()) {
					$arOp = $ob->GetFields();
					$arOpProps = $ob->GetProperties();
					$arOp = array_merge($arOp, $arOpProps);
					$arOperations[] = $arOp;
					$usersId[] = $arOp["USER"]["VALUE"];
				}
				//Polucaem tekushie ballanci pol'zovateley
				if(!empty($usersId)) {
					$arParams["SELECT"] = array("UF_LOGICTIM_BONUS");
					$rsUsers = CUser::GetList(($by="ID"), ($order="desc"), array("ID" => implode('|', $usersId)), $arParams);
					$usersInfo = array();
					while ($arUser = $rsUsers->Fetch()) 
					{
						$usersInfo[$arUser["ID"]] = $arUser;
					}
				}
				
				//deaktiviruem operacii, spisivaem bonusi, i sozdaem operacii deaktivacii
				foreach($arOperations as $operationSpisanie):
					$ostalos = $operationSpisanie["BALLANCE"]["VALUE"]*1; //skolko ostalos' ot dannogo nachislenita
					$userId = $operationSpisanie["USER"]["VALUE"];
					$userBonus = cHelper::UserBallance($userId);
					
					//deaktiviruem operaciyu
					CIBlockElement::SetPropertyValuesEx($operationSpisanie["ID"], false, array("LIVE_STATUS" => $operationsStatus["LIVE_END"]));
					
					if($userBonus > 0 && $userBonus >= $ostalos) {
						$updateUserBonus = $userBonus - $ostalos;
						$updateUserBonus = round($updateUserBonus, $round);
						
						$usersInfo[$userId]["UF_LOGICTIM_BONUS"] = $updateUserBonus;
						
						//Sozdaem operaciyu spisaniya
						$newOperation = new CIBlockElement;
						$PROP = array();
						$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['DEACIVATE_FROM_DATE']);
						$PROP["USER"] = $userId;
						$PROP["OPERATION_SUM"] = $ostalos;
						$PROP["BALLANCE_BEFORE"] = $userBonus;
						$PROP["BALLANCE_AFTER"] = $updateUserBonus;
						$PROP["ORDER_ID"] = '';
						$newOperationArray = Array(
												"MODIFIED_BY"    =>  '', 
												"IBLOCK_SECTION" => false,          
												"IBLOCK_ID"      => $iblokOperationsId,
												"IBLOCK_CODE "   => 'logictim_bonus_operations',
												"PROPERTY_VALUES"=> $PROP,
												"NAME"           => GetMessage("logictim.balls_BONUS_DEACTIVATE_FROM_DATE").$operationSpisanie["ID"],
												"ACTIVE"         => "Y",
												"CODE" => 'API_OPERATIONS'
												);
						if($newOperation->Add($newOperationArray));
						
						//spisivaem bonusi u usera
						//Esli ispol'zuetsya bonusniy schet modulya
						if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 1)
						{
							global $USER_FIELD_MANAGER;
							$USER_FIELD_MANAGER->Update("USER", $userId, array("UF_LOGICTIM_BONUS" => $updateUserBonus));
						}
						else
						{
							$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB');
							CSaleUserAccount::UpdateAccount($userId, -$ostalos, $currency, GetMessage("logictim.balls_BONUS_DEACTIVATE_FROM_DATE").$operationSpisanie["ID"]);
						}
					}
					
					
					
				endforeach;
				
			
			endif; //if($liveBonus == 'Y')
		
		return "cBonusDeactivateFromDate::BonusDeactivateFromDate();";
	}
}
?>