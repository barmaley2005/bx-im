<?php
IncludeModuleLangFile(__FILE__);
class cBonusFromRegister {
	static function BonusFromRegister($arFields)
	{
		CModule::IncludeModule("sale");
		CModule::IncludeModule("main");
		
		$rsUser = CUser::GetByID($arFields["USER_ID"]);
		$arUser = $rsUser->Fetch();
		$arUser["USER_GROUPS"] = \CUser::GetUserGroup($arFields["USER_ID"]);
		
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
		{
			$bonusRegister = (float)COption::GetOptionString("logictim.balls", "BONUS_REGISTRATION", 0);
			
			if(cHelper::CheckSiteDostup($arUser["LID"]) != 'Y')
				return;
		}
		else
		{
			$arParams = array(
								"PROFILE_TYPE" => 'registration',
								"SITE_ID" => $arUser["LID"],
								"USER_GROUPS" => $arUser["USER_GROUPS"]
							);
			$arProfiles = \Logictim\Balls\Profiles::getProfiles($arParams);
			$arProfile = end($arProfiles);
			$bonusRegister = (float)$arProfile["add_bonus"];
		}
		
		if($arFields["USER_ID"] > 0 && $bonusRegister > 0):
			
			//Nachislyaem bonusi useram
			$arFields = array(
						"ADD_BONUS" => $bonusRegister,
						"USER_ID" => $arFields["USER_ID"],
						"OPERATION_TYPE" => 'ADD_FROM_REGISTER',
						"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_ADD_REGISTER").$arFields["USER_ID"],
						"ACTIVE_AFTER" => $arProfile["active_after_period"],
						"ACTIVE_AFTER_TYPE" => $arProfile["active_after_type"],
						"DEACTIVE_AFTER" => $arProfile["deactive_after_period"],
						"DEACTIVE_AFTER_TYPE" => $arProfile["deactive_after_type"],
						"MAIL_EVENT" => array(
							  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REGISTER_ADD",
								),
						"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REGISTER_ADD_SMS",
                                        ),
					);
			\logictimBonusApi::AddBonus($arFields);
			
		endif;
    }
	
}
?>