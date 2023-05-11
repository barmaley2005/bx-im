<?
use Bitrix\Sale;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
Loader::includeModule("iblock");
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class cLogictimBeforeUserAccountUpdate
{
	public static function BeforeUserAccountUpdate($ID, $arFields) 
    { 
		if(isset($arFields["CURRENT_BUDGET"])):
		
			if(COption::GetOptionString("logictim.balls", "BONUS_BILL", '1') == 2):
				
				$arAccount = CSaleUserAccount::GetByID($ID);
				$currency = COption::GetOptionString("logictim.balls", "BONUS_CURRENCY", 'RUB'); //Valyuta iz nastroek modulya
				
				if($arAccount["CURRENCY"] == $currency)
				{
					$userId = $arAccount["USER_ID"];
					$oldBallance = $arAccount["CURRENT_BUDGET"]*1;
					$newBallance = $arFields["CURRENT_BUDGET"]*1;
					
					if($newBallance > $oldBallance)
						$operationSum = $newBallance - $oldBallance;
					if($newBallance < $oldBallance)
						$operationSum = $oldBallance - $newBallance;
					
					//-------------------Sozdaem operaciyu-------------------------//
					CModule::IncludeModule("iblock");
					
					//Opredelyaem ID ibfobloka s operaciyami
					$iblokOperationsId = cHelper::IblokOperationsId();
					//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
					$operationsType = cHelper::OperationsType();
					
					//Proveryaem, net li uzhe takoy operacii v operaciyah modulya bonusov za poslednie 30 secund
					$dbOperations = CIBlockElement::GetList(array("ID" => "ASC"), 
															array(
																"IBLOCK_ID"=>$iblokOperationsId, 
																"ACTIVE"=>"Y", 
																"PROPERTY_USER" => $userId, 
																"PROPERTY_BALLANCE_AFTER" => $newBallance,
																">DATE_CREATE"=>array(ConvertTimeStamp(time()-30, "FULL"))
																), 
																false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME"));
					while($Op = $dbOperations->GetNextElement())
					{
						 $OperationAddFields = $Op->GetFields();
					}
					
					if(!empty($OperationAddFields))
						return;
						
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
						$PROP["LIVE_DATE"] = ConvertTimeStamp($dateBonusEnd, "SHORT");
						$PROP["LIVE_STATUS"] = $operationsStatus["ACTIVE"];
						$PROP["BALLANCE"] = $operationSum;
					}
					global $USER;
					$newOperationArray = Array(
											"MODIFIED_BY"    =>  $USER->GetID(), 
											"IBLOCK_SECTION" => false,          
											"IBLOCK_ID"      => $iblokOperationsId,
											"IBLOCK_CODE "   => 'logictim_bonus_operations',
											"PROPERTY_VALUES"=> $PROP,
											"NAME"           => GetMessage("logictim.balls_BONUS_USER_CHANGE_ID").$userId,
											"ACTIVE"         => "Y",
											"CODE" => 'API_OPERATIONS'
											);
					if($newOperation->Add($newOperationArray));
					
					//echo '<pre>'; print_r($arAccount); echo '</pre>';
					//echo '<pre>'; print_r($arFields); echo '</pre>';
				}
				
					
				
				//echo $userBonus = cHelper::UserBallance('').'<br />';
			
			endif;
		endif;
		
	}
}

?>