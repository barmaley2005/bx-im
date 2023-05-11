<?
class cLTSaleOrderDeleted
{
	public static function AfterOrderDeleted($order, $isSuccess)
	{
		if($isSuccess)
		{
			$order_id = $order->getId();
			
			$fields = $order->GetFields();
			$values = $fields->GetValues();
			$user_id = $values["USER_ID"];
			
			//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = cHelper::IblokOperationsId();
			//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = cHelper::OperationsType();
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
				
			}
			
			//Esli poslednyaya opersciz spisaniya, to delaem vozvrat bonusov
			if($lastOperationType == 'MINUS_FROM_ORDER')
			{
				$operationSum = $operationProps["OPERATION_SUM"]["VALUE"];
				
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
							  "OPERATION_TYPE" => 'BACK_FROM_DELETTE',
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
								  "OPERATION_TYPE" => 'BACK_FROM_DELETTE',
								  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_BACK_FROM_CANCEL_ORDER").$order_num,
								  "ORDER_ID" => $order_id,
								  "DETAIL_TEXT" => '',
								  "SERVICE_INFO" => '',
								  "MAIL_EVENT" => ''
								);
				
					logictimBonusApi::AddBonus($arAddBonus);
				}
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
	}
}
?>