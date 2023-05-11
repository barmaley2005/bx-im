<?php
IncludeModuleLangFile(__FILE__);
class cUpdateElement {
    static $MODULE_ID="logictim.balls";
    
	//Izmenenie elementa istorii operaciy
	static function onBeforeElementUpdateHandler(&$arFields)
	{
		CModule::IncludeModule("iblock");
		
		$iblokOperationsId = cHelper::IblokOperationsId(); //Opredelyaem ID ibfobloka s operaciyami
		$iblokWaitId = cHelper::IblokWaitId(); //Opredelyaem ID ibfobloka s operaciyami ojidaniya
			
		//Zapret izmenenmiya istorii operaciy && Zapret izmenenmiya operaciy ojidaniya
		if($arFields["IBLOCK_ID"] == $iblokOperationsId || $arFields["IBLOCK_ID"] == $iblokWaitId)
		{
			global $APPLICATION;
			$APPLICATION->throwException(GetMessage("logictim.balls_ELEMENT_UPDATE_ERROR"));
		   if($arFields["CHANGE_FLAG"] != 'Y')
		   {
				return false;
		   }
		}		
		
		//Nachislenie bonuzov za otziv
		if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
		{
			$iblocksReview = unserialize(COption::GetOptionString("logictim.balls", "REVIEW_IBLOCK", ''));
			$addBonus = (int)COption::GetOptionString("logictim.balls", "BONUS_REVIEW", 0); //Skolko nachislyat'
			$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
			$addBonus = round($addBonus, $round);
		}
		else
		{
			$arParams = array(
								"PROFILE_TYPE" => 'review',
								"LIMIT" => 1,
								"SORT_FIELD_1" => 'sort',
								"SORT_ORDER_1" => 'DESC',
							);
			$arProfiles = \Logictim\Balls\Profiles::getProfiles($arParams);
			if(is_array($arProfiles) && !empty($arProfiles)):
				$arProfile = end($arProfiles);
				$arProfile["OTHER_CONDITIONS"] = unserialize($arProfile["other_conditions"]);
				if(!empty($arProfile["PRODUCT_CONDITIONS"])):
					foreach($arProfile["PRODUCT_CONDITIONS"] as $arCondition):
						if($arCondition["controlId"] == 'iblock' && !empty($arCondition["values"]["value"]))
							$iblocksReview = $arCondition["values"]["value"];
					endforeach;
					$addBonus = (float)$arProfile["add_bonus"];
				endif;
			endif;
		}
		
		if($iblocksReview && in_array($arFields["IBLOCK_ID"], $iblocksReview) && $arFields["ID"] > 0 && $arFields["ACTIVE"] == "Y" && $addBonus > 0)
		{
			//Polucaem infu po elementu infobloka, chtobi opredelit usera, kotoriy dobavil comment
			$res = CIBlockElement::GetByID($arFields["ID"]);
			$arComment = $res->GetNext();
			$userId = $arComment["CREATED_BY"];
			
			//Proveryaem ogranichenie na max bonus za otzivi
			if(isset($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_COUNT"]))
				$reviewCount = $arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_COUNT"];
			if(isset($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TIME"]))
			{
				if($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TYPE"] == 'MIN')
					$rewTime = strtotime("-".(int)$arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TIME"]." minute", time());
				if($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TYPE"] == 'H')
					$rewTime = strtotime("-".(int)$arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TIME"]." hour", time());
				if($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TYPE"] == 'D')
					$rewTime = strtotime("-".(int)$arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TIME"]." day", time());
				if($arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TYPE"] == 'M')
					$rewTime = strtotime("-".(int)$arProfile["OTHER_CONDITIONS"]["MAX_RIVIEW_TIME"]." month", time());
			}
			
			if($reviewCount && $rewTime)
			{
				//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
				$operationsType = cHelper::OperationsType();
				$date = ConvertTimeStamp($rewTime, "FULL");
				$arFilterCheckReviewsCount = Array(
					'IBLOCK_CODE' => 'logictim_bonus_operations',
					'>=DATE_CREATE' => array($date),
					'PROPERTY_USER' => $userId,
					'PROPERTY_OPERATION_TYPE' => $operationsType['ADD_FROM_REVIEW'],
					);
				$resCheckAll = CIBlockElement::GetList(array("ID" => "ASC"), $arFilterCheckReviewsCount, false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" ));
				$allReviewsCount = 0;
				while($obCheckAll = $resCheckAll->GetNextElement()) {
					$arCheckAll = $obCheckAll->GetFields();
					$allReviewsCount++;
				}
				if($allReviewsCount >= $reviewCount)
					return;
			}
			
			//Proveryaem - net li uje nachisleniya za eton comment
			$searchProperty = 'IBLOCK_ID: '.$arFields["IBLOCK_ID"].'; ELEMENT_ID: '.$arFields["ID"];
			$dbOperations = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>$iblokOperationsId, "ACTIVE"=>"Y", "PROPERTY_SERVICE_INFO" => $searchProperty), false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME"));
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationAddFields = $Op->GetFields();
			}
			if(!empty($OperationAddFields))
				return;
			
			//Poluchaem nazvaniya operaciy iz infobloka
			$operationsName = array();
			$iblokOperationsId = cHelper::IblokOperationsId();
			$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
			while($enum_fields = $property_enums->GetNext())
			{
				$operationsName[$enum_fields["XML_ID"]] = $enum_fields["VALUE"];
			}
			
			
			
			$arAddBonus = array(
				  "ADD_BONUS" => $addBonus,
				  "USER_ID" => $arComment["CREATED_BY"],
				  "OPERATION_TYPE" => 'ADD_FROM_REVIEW',
				  "OPERATION_NAME" => $operationsName["ADD_FROM_REVIEW"],
				  "ACTIVE_AFTER" => $arProfile["active_after_period"],
				  "ACTIVE_AFTER_TYPE" => $arProfile["active_after_type"],
				  "DEACTIVE_AFTER" => $arProfile["deactive_after_period"],
				  "DEACTIVE_AFTER_TYPE" => $arProfile["deactive_after_type"],
				  "SERVICE_INFO" => 'IBLOCK_ID: '.$arFields["IBLOCK_ID"].'; ELEMENT_ID: '.$arFields["ID"],
				  "MAIL_EVENT" => array(
									  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REVIEW",
									  "CUSTOM_FIELDS" => array()
										),
				  "SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REVIEW_SMS",
                                        )
				);

			logictimBonusApi::AddBonus($arAddBonus);
			
		}
		
    }
	
}
?>