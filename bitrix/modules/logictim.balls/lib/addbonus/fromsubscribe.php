<?
namespace Logictim\Balls\AddBonus;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class FromSubscribe {
	public static function BonusFromSubscribe($arOptions)
	{
		
		$userGroups = \CUser::GetUserGroup($arOptions["USER_ID"]);
		
		$arParams = array(
					"PROFILE_TYPE" => 'subscribe',
					"USER_GROUPS" => $userGroups
				);
		$arProfiles = \Logictim\Balls\Profiles::getProfiles($arParams);
		
		$arSubscribes = array();
		//Subscribes from module Sender
		global $DB;
		$listSender = $DB->Query('select * from b_sender_mailing;');
		$arSenders = array();
		while($arSender = $listSender->GetNext()) {
			$arSubscribes['sender_'.$arSender["ID"]] = array("ID" => $arSender["ID"], "SITE_ID" => $arSender["SITE_ID"], "NAME" => $arSender["NAME"]);
		}
		
		//Subscribes from module Subscribe
		if(\CModule::IncludeModule("subscribe"))
		{
			$rsRubric = \CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array());
			$arRubrics = array();
			while($arRubric = $rsRubric->GetNext()) {
				$arSubscribes['subscribe_'.$arRubric["ID"]] = array("ID" => $arRubric["ID"], "SITE_ID" => $arRubric["LID"], "NAME" => $arRubric["NAME"]);
			}
		}
		
		
		
		if(!empty($arProfiles))
		{
			$arAddBonus = array();
			
			foreach($arProfiles as $arProfile):
				if(!empty($arProfile["PRODUCT_CONDITIONS"]))
				{
					foreach($arProfile["PRODUCT_CONDITIONS"] as $arCondition):
						$moduleId = $arCondition['controlId'];
						$subscribesId = $arCondition['values']['value'];
						
						if($moduleId == $arOptions["MODULE_ID"])
						{
							foreach($subscribesId as $subscribeId):
								if(in_array($subscribeId, $arOptions["SUBSCRIBE_ID"]))
								{
									$arAddBonus[$moduleId.'_'.$subscribeId] = array("MODULE_ID" => $moduleId, "SUBSCRIBE_ID" => $subscribeId, "PROFILE" => $arProfile, "SUBSCRIBE" => $arSubscribes[$moduleId.'_'.$subscribeId]);
								}
							endforeach;
						}
					endforeach;
				}
			endforeach;
			
			
			if(!empty($arAddBonus))
			{
				\CModule::IncludeModule("iblock");
				foreach($arAddBonus as $arBonus):
					//Proveryaem, net li eje nachisleniya za dannuyu podpisku
					$arCheck = array();
					$arFilterCheckBonus = Array(
											'IBLOCK_CODE' => 'logictim_bonus_operations',
											'PROPERTY_USER' => $arOptions["USER_ID"],
											'PROPERTY_SERVICE_INFO' => '%MODULE_ID: '.$arBonus["MODULE_ID"].'; '.'SUBSCRIBE_ID: '.$arBonus["SUBSCRIBE_ID"].';%',
											);
					$resCheck = \CIBlockElement::GetList(array("ID" => "ASC"), $arFilterCheckBonus, false, false, array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE"));
					while($obCheck = $resCheck->GetNextElement()) {
						$arCheck = $obCheck->GetFields();
					}
					
					if(!empty($arCheck))
						continue;
						
					$arWorkProfile = $arBonus["PROFILE"];
					
					$arFields = array(
						"ADD_BONUS" => $arWorkProfile["add_bonus"],
						"USER_ID" => $arOptions["USER_ID"],
						"OPERATION_TYPE" => 'ADD_FROM_SUBSCRIBE',
						"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_ADD_SUBSCRIBE").' "'.$arBonus["SUBSCRIBE"]["NAME"].'"',
						"ACTIVE_AFTER" => $arWorkProfile["active_after_period"],
						"ACTIVE_AFTER_TYPE" => $arWorkProfile["active_after_type"],
						"DEACTIVE_AFTER" => $arWorkProfile["deactive_after_period"],
						"DEACTIVE_AFTER_TYPE" => $arWorkProfile["deactive_after_type"],
						"SERVICE_INFO" => 'MODULE_ID: '.$arBonus["MODULE_ID"].'; '.'SUBSCRIBE_ID: '.$arBonus["SUBSCRIBE_ID"].'; '.'PROFILE_ID: '.$arWorkProfile["id"],
						"MAIL_EVENT" => array(
							  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_SUBSCRIBE",
								),
						"SMS_EVENT" => array(
							  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_SUBSCRIBE_SMS",
							  "CUSTOM_FIELDS" => array("SUBSCRIBE_ID" => $arBonus["SUBSCRIBE_ID"], "SUBSCRIBE_NAME" => $arBonus["SUBSCRIBE"]["NAME"])
								),
					);
					
					\logictimBonusApi::AddBonus($arFields);
				endforeach;
			}
		}
		
		
		
	}
}


?>