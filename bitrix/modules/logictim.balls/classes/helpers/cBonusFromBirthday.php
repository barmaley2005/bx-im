<?php
IncludeModuleLangFile(__FILE__);
class cBonusFromBirthday {
	public static function BonusFromBirthday() {
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
			return "cBonusFromBirthday::BonusFromBirthday();";
		
		//Poluchaem spisok userov, u kogo segodnya den' rojdeniya
		$arFilter = array(
		   "PERSONAL_BIRTHDAY_DATE" => date('m-d'),
		   "ACTIVE" => 'Y',
		);
		$arParams["SELECT"] = array("UF_LOGICTIM_BONUS");
		$rsUsers = CUser::GetList(($by="ID"), ($order="desc"), $arFilter, $arParams);
		$usersId = array();
		$usersInfo = array();
		while ($arUser = $rsUsers->Fetch()) 
		{
			$usersId[$arUser["ID"]] = $arUser["ID"];
			$usersInfo[$arUser["ID"]] = $arUser;
			$usersInfo[$arUser["ID"]]["USER_GROUPS"] = \CUser::GetUserGroup($arUser["ID"]);
		}
		//echo '<pre>'; print_r($usersId); echo '</pre>';
		//Poluchaem spisok userov, u kogo segodnya den' rojdeniya
		
		//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = cHelper::IblokOperationsId();
			$iblokWaitId = \Logictim\Balls\Helpers::IblokWaitId();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = cHelper::OperationsType();
			$operationsTypeWait = \Logictim\Balls\Helpers::OperationsTypeWait();
		
		//Proveryaem, net li uzhe operacii nachisleniya etomu useru v etom godu
			$dbOperations = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokOperationsId, $iblokWaitId), "ACTIVE"=>"Y", "PROPERTY_USER" => $usersId, "PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_BIRTHDAY'], $operationsTypeWait['ADD_FROM_BIRTHDAY'])), false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME", "TIMESTAMP_X", "PROPERTY_USER"));
					while($Op = $dbOperations->GetNextElement())
					{
						 $OperationAddFields = $Op->GetFields();
						 $arDate = ParseDateTime($OperationAddFields["TIMESTAMP_X"], FORMAT_DATETIME);
						 $OperationAddFields["YEAR-ADD"] = $arDate["YYYY"];
						 $OperationAddFields["YEAR-TODAY"] = date('Y');
						 
						 //udalyaem usera iz spiska, esli v etom godu emu uje nachislyali za den' rojdeniya
						 if($OperationAddFields["YEAR-ADD"] == $OperationAddFields["YEAR-TODAY"])
						 {
							unset($usersId[$OperationAddFields["PROPERTY_USER_VALUE"]]);
							unset($usersInfo[$OperationAddFields["PROPERTY_USER_VALUE"]]);
						 }
					}
		//echo '<pre>'; print_r($usersInfo); echo '</pre>';
		
		
		//Nachislyaem bonusi useram
			foreach($usersInfo as $user):
				
				if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
				{
					$bonus = (float)COption::GetOptionString("logictim.balls", "BONUS_BIRTHDAY", 0);
				}
				else
				{
					$arParams = array(
										"PROFILE_TYPE" => 'birthday',
										"SITE_ID" => $user["LID"],
										"USER_GROUPS" => $user["USER_GROUPS"]
									);
					$arProfiles = \Logictim\Balls\Profiles::getProfiles($arParams);
					$arProfile = end($arProfiles);
					$bonus = (float)$arProfile["add_bonus"];
				}
				
				
				if($bonus > 0 && $user["ID"] > 0)
				{
					
					//Nachislyaem bonusi useram
					$arFields = array(
								"ADD_BONUS" => $bonus,
								"USER_ID" => $user["ID"],
								"OPERATION_TYPE" => 'ADD_FROM_BIRTHDAY',
								"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_ADD_BIRTHDAY").$user["ID"].' ('.date('d.m.Y').')',
								"ACTIVE_AFTER" => $arProfile["active_after_period"],
								"ACTIVE_AFTER_TYPE" => $arProfile["active_after_type"],
								"DEACTIVE_AFTER" => $arProfile["deactive_after_period"],
								"DEACTIVE_AFTER_TYPE" => $arProfile["deactive_after_type"],
								"MAIL_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD",
                                        ),
								"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD_SMS",
                                        ),
							);
					\logictimBonusApi::AddBonus($arFields);
				}
			endforeach; 
		
		return "cBonusFromBirthday::BonusFromBirthday();";
	}
}
?>