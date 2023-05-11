<?php
IncludeModuleLangFile(__FILE__);
class cBeforeUserUpdate {
    static $MODULE_ID="logictim.balls";
    
	static function BeforeUserUpdate(&$arFields)
	{
		
		if(isset($arFields["UF_LOGICTIM_BONUS"])) {
			$userId = $arFields["ID"];
			$newBallance = $arFields["UF_LOGICTIM_BONUS"];
			
			if(!preg_match("|^[\d]*$|", $newBallance)) {
				if(!preg_match("|^[\d]*\.[\d]*$|", $newBallance)) {
					global $APPLICATION;
					$APPLICATION->throwException(GetMessage("logictim.balls_BONUS_FORMAT_ERROR"));
					return false;
				}
			}
			
			//Polucaem prejnee znachenie polya "bonusi"
			global $USER;
			$arParams["SELECT"] = array("UF_LOGICTIM_BONUS");
			$dbUserParams = CUser::GetList($by,$desc,array("ID" => $userId),$arParams);
				if ($UserParams = $dbUserParams->Fetch()) {
					$oldBallance =  $UserParams["UF_LOGICTIM_BONUS"];
				}
				
			//Schitaem raznicu, i sozdaem operaciyu
			if($newBallance != $oldBallance)
			{
				if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') > 3)
				{
					//global $APPLICATION;
					//$APPLICATION->throwException(GetMessage("logictim.balls_BONUS_USER_CHANGE_BLOCK"));
					$newBallance = $oldBallance;
					$arFields["UF_LOGICTIM_BONUS"] = $oldBallance;
				}
				
				if($newBallance > $oldBallance)
				{
					$operationSum = $newBallance - $oldBallance;
				}
				if($newBallance < $oldBallance)
				{
					$operationSum = $oldBallance - $newBallance;
				}
				
				//-------------------Sozdaem operaciyu-------------------------//
				
				if(CModule::IncludeModule("iblock") && $operationSum > 0)
				{
					//Opredelyaem ID ibfobloka s operaciyami
							$dbiblokOpertion = CIBlock::GetList(
													Array(), 
													Array(
														'ACTIVE'=>'Y', 
														"CODE"=>'logictim_bonus_operations'
													), true
												);
							while($iblokOpertion = $dbiblokOpertion->Fetch())
							{
								$iblokOperationsId = $iblokOpertion["ID"];
							}
					//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
							$operationsType = array();
							$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"OPERATION_TYPE"));
							while($enum_fields = $property_enums->GetNext())
							{
								$operationsType[$enum_fields["XML_ID"]] = $enum_fields["ID"];
							}
							
						$newOperation = new CIBlockElement;
							$PROP = array();
							$PROP["OPERATION_TYPE"] = Array("VALUE" => $operationsType['USER_BALLANCE_CHANGE']);
							$PROP["USER"] = $userId;
							$PROP["OPERATION_SUM"] = $operationSum;
							$PROP["BALLANCE_BEFORE"] = $oldBallance;
							$PROP["BALLANCE_AFTER"] = $newBallance;
							$PROP["ORDER_ID"] = '';
							$PROP["ADD_DETAIL"] = Array("VALUE" => Array ("TEXT" => GetMessage("logictim.balls_BONUS_USER_CHANGE"), "TYPE" => "text"));
							
							$liveBonus = COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N');
							if($liveBonus == 'Y' && $newBallance > $oldBallance) {
								$liveBonusTime = COption::GetOptionString("logictim.balls", "LIVE_BONUS_TIME", '365');
								//Poluchaem vozmojnie znacheniya svoystava "LIVE_STATUS"
								$operationsStatus = array();
								$status_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblokOperationsId, "CODE"=>"LIVE_STATUS"));
								while($status_fields = $status_enums->GetNext())
								{
									$operationsStatus[$status_fields["XML_ID"]] = $status_fields["ID"];
								}
								$dateBonusEnd = time() + $liveBonusTime*86400;
								$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "FULL");
								$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
								$PROP["BALLANCE"] = $operationSum;
							}
							
							$newOperationArray = Array(
													"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
													"IBLOCK_SECTION" => false,          
													"IBLOCK_ID"      => $iblokOperationsId,
													"IBLOCK_CODE "   => 'logictim_bonus_operations',
													"PROPERTY_VALUES"=> $PROP,
													"NAME"           => GetMessage("logictim.balls_BONUS_USER_CHANGE_ID").$userId,
													"ACTIVE"         => "Y",
													"CODE" => 'API_OPERATIONS'
													);
							if($newOperation->Add($newOperationArray));
					//-------------------END Sozdaem operaciyu-------------------------//
				}
			}

			
		}
	}
}
?>