<?
class cLTSaleOrderCanceled
{
	public static function CangeOrderCanceled($order)
	{
		$fields = $order->GetFields();
		$arOrder = $fields->GetValues();
		
		$order_id = $arOrder["ID"];
		$order_num = $arOrder["ACCOUNT_NUMBER"];
		$user_id = $arOrder["USER_ID"];
		$order_status = $arOrder["STATUS_ID"];
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($user_id);
		$UserBallance = cHelper::UserBallance($user_id);
		$operationsName = cHelper::OperationsName();
		
		//Opredelyaem ID ibfobloka s operaciyami
		$iblokOperationsId = cHelper::IblokOperationsId();
		$iblokWaitId = cHelper::IblokWaitId();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
		$operationsType = cHelper::OperationsType();
		$operationsTypeWait = cHelper::OperationsTypeWait();
		
		
		//Otmena zakaza
		if($arOrder["CANCELED"] == 'Y')
		{
			
			//Poluchaem spisok operaciy po zakazu
			$dbOperations = CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $order_id, 
									"PROPERTY_USER"=>$user_id,
									"PROPERTY_OPERATION_TYPE" => array($operationsType['MINUS_FROM_ORDER'], $operationsType['BACK_FROM_CANCEL'], $operationsType['BACK_FROM_DELETTE'], $operationsType['DEACIVATE_FROM_DATE'])
								), 
								false, 
								array("nPageSize"=>1)
							);
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationFields = $Op->GetFields();
				 $operationProps = $Op->GetProperties();
				 $lastOperationType = $operationProps["OPERATION_TYPE"]["VALUE_XML_ID"];
				 $operationSum = $operationProps["OPERATION_SUM"]["VALUE"];
			}
			
				
			//Esli poslednyaya opersciz spisaniya, to delaem vozvrat bonusov
			if($lastOperationType == 'MINUS_FROM_ORDER')
			{
				
				$arOperationsUse = unserialize($operationProps["SERVICE_INFO"]["~VALUE"]);
				$arBack = array();
				$allbackSum = 0;
				if(!empty($arOperationsUse))
				{
					foreach($arOperationsUse as $arOperationUse):
						$arBack[$arOperationUse["LIVE_DATE"]][$arOperationUse["OPERATION_ADD_ID"]] = array(
																										"OPERATION_ADD_ID"=>$arOperationUse["OPERATION_ADD_ID"],
																										"PAY_FROM_OPERATION"=>$arOperationUse["PAY_FROM_OPERATION"],
																										"LIVE_DATE"=>$arOperationUse["LIVE_DATE"]
																										);
						if($arBack[$arOperationUse["LIVE_DATE"]]["BACK_SUM"] > 0)
							$arBack[$arOperationUse["LIVE_DATE"]]["BACK_SUM"] = $arBack[$arOperationUse["LIVE_DATE"]]["BACK_SUM"] + $arOperationUse["PAY_FROM_OPERATION"];
						else
							$arBack[$arOperationUse["LIVE_DATE"]]["BACK_SUM"] = $arOperationUse["PAY_FROM_OPERATION"];
						$allbackSum = $allbackSum + $arOperationUse["PAY_FROM_OPERATION"];
					endforeach;
					
					if($allbackSum == $operationSum)
						$check = 'Y';
						
				}
				
				if(!empty($arOperationsUse) && $check == 'Y')
				{
					foreach($arBack as $liveDate => $backOperation):
					
						$dateSpisanie = strtotime($OperationFields["DATE_CREATE"]); //data spisaniya bonusov
						$dateActiveBonus = strtotime($liveDate); //do kakoy dati oni bili activni
						$newDateActive = $dateActiveBonus + (time() - $dateSpisanie);
						$newDeactiveAfter = ($newDateActive - time())/86400+1;
						
						$arAddBonus = array(
							  "ADD_BONUS" => $backOperation["BACK_SUM"],
							  "USER_ID" => $user_id,
							  "OPERATION_TYPE" => 'BACK_FROM_CANCEL',
							  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_BACK_FROM_CANCEL_ORDER").$order_num.GetMessage("logictim.balls_BONUS_ACTIVE").$liveDate,
							  "ORDER_ID" => $order_id,
							  "DETAIL_TEXT" => '',
							  "SERVICE_INFO" => '',
							  "MAIL_EVENT" => '',
							  "DEACTIVE_AFTER" => $newDeactiveAfter,
							  "DEACTIVE_AFTER_TYPE" => 'D'
							);
						logictimBonusApi::AddBonus($arAddBonus);
					endforeach;
				}
				else
				{
				
					$arAddBonus = array(
								  "ADD_BONUS" => $operationSum,
								  "USER_ID" => $user_id,
								  "OPERATION_TYPE" => 'BACK_FROM_CANCEL',
								  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_BACK_FROM_CANCEL_ORDER").$order_num,
								  "ORDER_ID" => $order_id,
								  "DETAIL_TEXT" => '',
								  "SERVICE_INFO" => '',
								  "MAIL_EVENT" => ''
								);
				
					logictimBonusApi::AddBonus($arAddBonus);
				}
			}
			
			
			//Udalyaem operaciyu ojidania nachisleniya po otmenennomu zakazu
			$dbWait = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>$iblokWaitId, "ACTIVE"=>"Y", "PROPERTY_ORDER_ID" => $order_id), false, Array("nPageSize"=>PHP_INT_MAX));
			while($Op = $dbWait->GetNextElement())
			{
				$OperationWaitFields = $Op->GetFields();
				CIBlockElement::Delete($OperationWaitFields["ID"]);
			}
			
			//Spisivaem nachislennie po zakazu bonusi
			$dbOperations = CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $order_id, 
									"PROPERTY_USER"=>$user_id,
									"PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_ORDER'], $operationsType['MINUS_FROM_ORDER_CANCEL'])
								), 
								false, 
								array("nPageSize"=>1)
							);
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationFields = $Op->GetFields();
				 $operationProps = $Op->GetProperties();
				 $lastOperationType = $operationProps["OPERATION_TYPE"]["VALUE_XML_ID"];
				 $operationSum = $operationProps["OPERATION_SUM"]["VALUE"];
			}
			if($lastOperationType == 'ADD_FROM_ORDER')
			{
				$arFields = array(
								  "MINUS_BONUS" => $operationSum,
								  "USER_ID" => $user_id,
								  "OPERATION_TYPE" => 'MINUS_FROM_ORDER_CANCEL',
								  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_ORDER").$order_num,
								  "ORDER_ID" => $order_id,
								  "DETAIL_TEXT" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_ORDER"),
								);
				logictimBonusApi::MinusBonus($arFields);
			}
			
			//Spisivaem nachislennie po zakazu referala bonusi
			$dbOperations = CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $order_id, 
									"PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_REFERAL'], $operationsType['MINUS_FROM_ORDER_CANCEL'])
								), 
								false, 
								false
							);
			$arReferalsOperations = array();
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationFields = $Op->GetFields();
				 $operationProps = $Op->GetProperties();
				 $arReferalsOperations[$operationProps["USER"]["VALUE"]] = array("USER_ID" => $operationProps["USER"]["VALUE"], "LAST_OPERATION_TYPE" => $operationProps["OPERATION_TYPE"]["VALUE_XML_ID"], "OPERATION_SUM" => $operationProps["OPERATION_SUM"]["VALUE"]);
			}
			if(!empty($arReferalsOperations))
			{
				foreach($arReferalsOperations as $refOperation):
					if($refOperation["LAST_OPERATION_TYPE"] == 'ADD_FROM_REFERAL')
					{
						$arFields = array(
								  "MINUS_BONUS" => $refOperation["OPERATION_SUM"],
								  "USER_ID" => $refOperation["USER_ID"],
								  "OPERATION_TYPE" => 'MINUS_FROM_ORDER_CANCEL',
								  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_REFEARAL_ORDER"),
								  "ORDER_ID" => $order_id,
								  "DETAIL_TEXT" => GetMessage("logictim.balls_BONUS_MINUS_FROM_CANCEL_REFEARAL_ORDER"),
								);
						logictimBonusApi::MinusBonus($arFields);
					}
				endforeach;
			}
			
			
		}
		//Otmena otmeni zakaza
		if($arOrder["CANCELED"] == 'N')
		{
			//Poluchaem spisok operaciy po zakazu
			$dbOperations = CIBlockElement::GetList(
								array("ID" => "DESC"), 
								array
								(
									"IBLOCK_ID"=>$iblokOperationsId, 
									"ACTIVE"=>"Y", 
									"PROPERTY_ORDER_ID" => $order_id, 
									"PROPERTY_USER"=>$user_id,
									"PROPERTY_OPERATION_TYPE" => array($operationsType['MINUS_FROM_ORDER'], $operationsType['BACK_FROM_CANCEL'], $operationsType['BACK_FROM_DELETTE'], $operationsType['DEACIVATE_FROM_DATE'])
								), 
								false, 
								false
							);
			
			$operationSum = 0;
			$lastOperationType = '';
			$stop = '';
			while($Op = $dbOperations->GetNextElement())
			{
				$OperationFields = $Op->GetFields();
				$operationProps = $Op->GetProperties();
				 
				if($lastOperationType == '')
					$lastOperationType = $operationProps["OPERATION_TYPE"]["VALUE_XML_ID"];
				 
				if(
					$operationProps["OPERATION_TYPE"]["VALUE_XML_ID"] == 'BACK_FROM_CANCEL' && $stop != 'Y' 
					|| 
					$operationProps["OPERATION_TYPE"]["VALUE_XML_ID"] == 'BACK_FROM_DELETTE' && $stop != 'Y'
					)
					$operationSum = $operationSum + $operationProps["OPERATION_SUM"]["VALUE"];
				else
					$stop = 'Y';
			}
			
			if($lastOperationType == 'BACK_FROM_CANCEL' || $lastOperationType == 'BACK_FROM_DELETTE')
			{
				if($UserBallance < $operationSum)
				{
					exit('NO MONEY - NO HONEY!<br />Ne dostatocho sredstv u polzovatelya dlya oplati');
				}
				else
				{
					
					$arFields = array(
									  "MINUS_BONUS" => $operationSum,
									  "USER_ID" => $user_id,
									  "OPERATION_TYPE" => 'MINUS_FROM_ORDER',
									  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num,
									  "ORDER_ID" => $order_id,
									  "DETAIL_TEXT" => GetMessage("logictim.balls_BONUS_FROM_ORDER_CANCELED"),
									);
					logictimBonusApi::MinusBonus($arFields);
					
				}
			}
		}
		
		
		//Otmena otmeni zakaza. Esli est zaderjka nachisleniya
		if($arOrder["CANCELED"] == 'N' && $UserBonusSystemDostup == 'Y' && (int)COption::GetOptionString("logictim.balls", "BONUS_ORDER_WAIT", 0) > 0):
			
			//Proveryaem, net li uzhe operacii nachisleniya po etomu zakazu
			$dbOperations = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokOperationsId, $iblokWaitId), "ACTIVE"=>"Y", "PROPERTY_ORDER_ID" => $order_id, "PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_ORDER'], $operationsTypeWait['ADD_FROM_ORDER'])), false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME"));
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationAddFields = $Op->GetFields();
			}
			if(empty($OperationAddFields))
			{
				//Sozdaem operaciyu ojidaniya
				if($order_status == 'F' && COption::GetOptionString("logictim.balls", "EVENT_ORDER_END", 'Y') == 'Y' || $arOrder["PAYED"] == 'Y' && COption::GetOptionString("logictim.balls", "EVENT_ORDER_PAYED", 'Y') == 'Y')
					BonusFromOrderAdd::BonusAdd($order);
			}
		endif;
		
	}
}
?>