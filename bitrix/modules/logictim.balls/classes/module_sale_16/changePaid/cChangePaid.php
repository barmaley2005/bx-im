<?
use Bitrix\Sale;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
Loader::includeModule("iblock");
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class cChangePaid
{
	public static function ChangePaid(\Bitrix\Main\Event $obEvent) 
    {
		$obPayment = $obEvent->getParameter("ENTITY");
		$arPayment = $obPayment->getFields()->getValues();
		$order = \Bitrix\Sale\Order::load($arPayment["ORDER_ID"]);
		
		$arOrder = $order->GetFields();
		$arOrder = $arOrder->GetValues();
		$order_num = $arOrder["ACCOUNT_NUMBER"];
		
		CModule::IncludeModule("logictim.balls");
		$arPaySystems = cHelper::PaySystemsBonus();
		if(!in_array($arPayment["PAY_SYSTEM_ID"], $arPaySystems))
			return;
		
		$iblokOperationsId = cHelper::IblokOperationsId();
		$operationsType = cHelper::OperationsType();
		$order_id = $arPayment["ORDER_ID"];
		$user_id = $order->getUserId();
		$dbOperations = CIBlockElement::GetList(
										array("ID" => "DESC"), 
										array
										(
											"IBLOCK_ID" => $iblokOperationsId, 
											"PROPERTY_ORDER_ID" => $order_id, 
											"PROPERTY_USER" => $user_id,
											"PROPERTY_OPERATION_TYPE" => array($operationsType['MINUS_FROM_ORDER'], $operationsType['BACK_FROM_CANCEL'], $operationsType['BACK_FROM_DELETTE'], $operationsType['DEACIVATE_FROM_DATE'])
										), 
										false, 
										array("nPageSize"=>1)
									);
		while($Op = $dbOperations->GetNextElement())
		{
			 $lastOperationFields = $Op->GetFields();
			 $lastOperationProps = $Op->GetProperties();
			 $lastOperationType = $lastOperationProps["OPERATION_TYPE"]["VALUE_XML_ID"];
		}
		
		$pay_sum = $arPayment["SUM"];
		$lastOperationSum = $lastOperationProps["OPERATION_SUM"]["VALUE"];
		
		//Otmena plateja
		if($lastOperationType == 'MINUS_FROM_ORDER' && $arPayment["PAID"] == 'N' && $pay_sum == $lastOperationSum)
		{
			$arOperationsUse = unserialize($lastOperationProps["SERVICE_INFO"]["~VALUE"]);
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
				
				if($allbackSum == $lastOperationSum && $lastOperationSum == $pay_sum)
					$check = 'Y';
					
			}
			
			
			if(!empty($arOperationsUse) && $check == 'Y')
			{
				foreach($arBack as $liveDate => $backOperation):
				
					$dateSpisanie = strtotime($lastOperationFields["DATE_CREATE"]); //data spisaniya bonusov
					$dateActiveBonus = strtotime($liveDate); //do kakoy dati oni bili activni
					$newDateActive = $dateActiveBonus + (time() - $dateSpisanie);
					$newDeactiveAfter = ($newDateActive - time())/86400+1;
					
					$arAddBonus = array(
						  "ADD_BONUS" => $backOperation["BACK_SUM"],
						  "USER_ID" => $user_id,
						  "OPERATION_TYPE" => 'BACK_FROM_CANCEL',
						  "OPERATION_NAME" => Loc::getMessage("logictim.balls_BONUS_BACK_USER").$order_num.Loc::getMessage("logictim.balls_BONUS_ACTIVE").$liveDate,
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
							  "ADD_BONUS" => $pay_sum,
							  "USER_ID" => $user_id,
							  "OPERATION_TYPE" => 'BACK_FROM_CANCEL',
							  "OPERATION_NAME" => Loc::getMessage("logictim.balls_BONUS_BACK_USER").$order_num,
							  "ORDER_ID" => $order_id,
							  "DETAIL_TEXT" => '',
							  "SERVICE_INFO" => '',
							  "MAIL_EVENT" => ''
							);
			
				logictimBonusApi::AddBonus($arAddBonus);
			}
			
			//otmechaem v zakaze
			$props = $order->getPropertyCollection();
			foreach($props as $prop):
				$propFields = $prop->GetFields();
				$propValues = $propFields->GetValues();
				if($propValues["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
				{
					$pay_bonus = $propValues["VALUE"];
					$payBonusPropId = $propValues["ORDER_PROPS_ID"];
				}
			endforeach;
			$PayBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
			$PayBonusProp->setValue(0);
			$order->save();
		}
		
		
		//Provedenie plateja
		if($lastOperationType == 'BACK_FROM_CANCEL' && $arPayment["PAID"] == 'Y' || $lastOperationType == 'BACK_FROM_DELETTE' && $arPayment["PAID"] == 'Y')
		{
			$UserBallance = cHelper::UserBallance($user_id);
			
			if($UserBallance < $pay_sum)
			{
				exit('NO MONEY - NO HONEY!<br />Ne dostatocho sredstv u polzovatelya dlya oplati');
				
			}
			if($pay_sum > 0 && $UserBallance >= $pay_sum)
			{
				$arFields = array(
									  "MINUS_BONUS" => $pay_sum,
									  "USER_ID" => $user_id,
									  "OPERATION_TYPE" => 'MINUS_FROM_ORDER',
									  "OPERATION_NAME" => Loc::getMessage("logictim.balls_BONUS_FROM_ORDER_NUM").$order_num,
									  "ORDER_ID" => $order_id,
									  "PAYMENT_ID" => $arPayment["ID"],
									);
				logictimBonusApi::MinusBonus($arFields);
				
				//otmechaem v zakaze
				$props = $order->getPropertyCollection();
				foreach($props as $prop):
					$propFields = $prop->GetFields();
					$propValues = $propFields->GetValues();
					if($propValues["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
					{
						$pay_bonus = $propValues["VALUE"];
						$payBonusPropId = $propValues["ORDER_PROPS_ID"];
					}
				endforeach;
				$PayBonusProp = $props->getItemByOrderPropertyId($payBonusPropId);
				$PayBonusProp->setValue($pay_sum);
				$order->save();
			}
		}
	}
}

?>