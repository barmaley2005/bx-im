<?
namespace Logictim\Balls\AddBonus;

IncludeModuleLangFile(__FILE__);

use Bitrix\Sale;
use Bitrix\Main\Mail\Event;

class FromReferalOrder {
	public static function BonusFromReferalOrder($arOrderParams, $arItems)
	{
		$order_user_id = $arOrderParams["USER_ID"];
		$order_id = $arOrderParams["ORDER_ID"];
		
		$arPartners = \LBReferalsApi::GetPartnersList($order_user_id);
		
		if(empty($arPartners))
			return;
			
		//Opredelyaem ID ibfobloka s operaciyami
			$iblokOperationsId = \Logictim\Balls\Helpers::IblokOperationsId();
		//Opredelyaem ID ibfobloka s operaciyami ojidaniya
			$iblokWaitId = \Logictim\Balls\Helpers::IblokWaitId();
			
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
			$operationsType = \Logictim\Balls\Helpers::OperationsType();
		//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE" ibfobloka s operaciyami ojidaniya
			$operationsTypeWait = \Logictim\Balls\Helpers::OperationsTypeWait();
			
		
			
		foreach($arPartners as $arPartner):
		
			//Proveryaem, net li uzhe operacii nachisleniya po etomu zakazu
			$dbOperations = \CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokOperationsId, $iblokWaitId), "ACTIVE"=>"Y", "PROPERTY_USER" => $arPartner["PARTNER_ID"], "PROPERTY_ORDER_ID" => $order_id, "PROPERTY_OPERATION_TYPE" => array($operationsType['ADD_FROM_REFERAL'], $operationsTypeWait['ADD_FROM_REFERAL'], $operationsType['MINUS_FROM_ORDER_CANCEL'])), false, false, array());
			while($Op = $dbOperations->GetNextElement())
			{
				 $OperationAddFields = $Op->GetFields();
				 $OperationAddProps = $Op->GetProperties();
				 $lastOperationType = $OperationAddProps["OPERATION_TYPE"]["VALUE_XML_ID"];
			}
			if(!empty($OperationAddFields) && $lastOperationType != 'MINUS_FROM_ORDER_CANCEL') //Esli bonusi po zakazu uzhe nachisleni, vihodim
				continue;
			
			
			$arBonus = \Logictim\Balls\CalcBonus::getBonus($arItems, array("TYPE"=>'cart', "PROFILE_TYPE" => 'order_referal', "EVENT_ORDER_PAID"=>$arOrderParams["EVENT_ORDER_PAID"], "ORDER"=>$arOrderParams, "PARTNER" => array("PARTNER_LEVEL" => $arPartner["LEVEL"], "PARTNER_ID" => $arPartner["PARTNER_ID"])));
			
			$detailInfo = '';
			foreach($arItems as $arItem):
				$item = $arBonus["ITEMS"][$arItem["BASKET_ID"]];
				if($item["BONUS_TYPE"] == 'percent')
				{
					if($item["ROUND_TYPE"] == 'UNIT')
						$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = ('.$item["BONUS"].'% x '.$item["PRICE"]["DISCOUNT_PRICE"].') x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
					else //POSITION
						$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].'% x '.$item["PRICE"]["DISCOUNT_PRICE"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
				}
				if($item["BONUS_TYPE"] == 'bonus')
					$detailInfo .= 'product_id='.$item["ID"].' '.$item["NAME"].' bonus = '.$item["BONUS"].' x '.$item["QUANTITY"].' = '.$item["ADD_BONUS"]."\n";
			endforeach;
			if(isset($arBonus["FIX_ORDER_BONUS"]) && $arBonus["FIX_ORDER_BONUS"] > 0)
				$detailInfo .= 'Bonus from order = '.$arBonus["FIX_ORDER_BONUS"];
			
			if($arBonus["ALL_BONUS"] > 0):
				$arFields = array(
									"ADD_BONUS" => $arBonus["ALL_BONUS"],
									"USER_ID" => $arPartner["PARTNER_ID"],
									"OPERATION_TYPE" => 'ADD_FROM_REFERAL',
									"OPERATION_NAME" => GetMessage("logictim.balls_BONUS_ADD_REF").$arPartner["LEVEL"].GetMessage("logictim.balls_BONUS_ADD_REF_LEVEL"),//$arOrderParams["ORDER_NUM"],
									"ORDER_ID" => $arOrderParams["ORDER_ID"],
									"DETAIL_TEXT" => $detailInfo,
									"ACTIVE_AFTER" => $arBonus["PROFILE"]["ACTIVE_AFTER"],
									"ACTIVE_AFTER_TYPE" => $arBonus["PROFILE"]["ACTIVE_AFTER_TYPE"],
									"DEACTIVE_AFTER" => $arBonus["PROFILE"]["DEACTIVE_AFTER"],
									"DEACTIVE_AFTER_TYPE" => $arBonus["PROFILE"]["DEACTIVE_AFTER_TYPE"],
									"MAIL_EVENT" => array(
										  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REFERAL_ADD",
										  "CUSTOM_FIELDS" => array(
																  "ORDER_NUM" => $values["ACCOUNT_NUMBER"],
																  )
											),
									"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REFERAL_ADD_SMS",
                                      "CUSTOM_FIELDS" => array(
                                                              "ORDER_NUM" => $values["ACCOUNT_NUMBER"],
                                                              )
                                        ),
								);
				\logictimBonusApi::AddBonus($arFields);
			endif;
			
			
		endforeach;
		
	}
}


?>