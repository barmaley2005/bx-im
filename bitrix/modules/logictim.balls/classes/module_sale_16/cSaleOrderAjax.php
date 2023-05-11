<?
class cSaleOrderAjax
{
	public static function SaleOrderAjaxEvent(&$arResult)
	{
		global $USER;
			
		
		//Vichitaem oplatu bonusami iz summi zazaka, esli stoit v nastroykax modulya
		if(COption::GetOptionString("logictim.balls", "ORDER_TOTAL_BONUS", 'Y') == 'Y' && $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS"] > 0) 
		{
			$pay_bonus = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["PAY_BONUS"];
			$order_sum = $arResult["JS_DATA"]["LOGICTIM_BONUS"]["ORDER_SUM"];
			$order_new_sum = $order_sum - $pay_bonus;
			
			$arResult["JS_DATA"]["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = $arResult["ORDER_TOTAL_PRICE_FORMATED"] = SaleFormatCurrency($order_new_sum, $arResult['BASE_LANG_CURRENCY']);
			
			if(strlen($arResult["PAYED_FROM_ACCOUNT_FORMATED"]) > 0)
			{
				$payFromAccaunt = (float)str_replace(" ", "", $arResult["PAYED_FROM_ACCOUNT_FORMATED"]);
				if($payFromAccaunt + $pay_bonus > $order_sum)
				{
					$payFromAccaunt = $payFromAccaunt - ($pay_bonus + $payFromAccaunt - $order_sum);
					$arResult["PAYED_FROM_ACCOUNT_FORMATED"] = SaleFormatCurrency($payFromAccaunt, $arResult['BASE_LANG_CURRENCY']);
				}
				$arResult["ORDER_TOTAL_LEFT_TO_PAY_FORMATED"] = SaleFormatCurrency($order_sum - $pay_bonus - $payFromAccaunt, $arResult['BASE_LANG_CURRENCY']);
			}
		}
			
		
			
				
				
		//Udalyaem platejnie sistemi bonusov oz shablona
		foreach($arResult["JS_DATA"]["PAY_SYSTEM"] as $keyPaysystem => $paySystem):
			if($paySystem["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
				unset($arResult["JS_DATA"]["PAY_SYSTEM"][$keyPaysystem]);
		endforeach;
		$arResult["JS_DATA"]["PAY_SYSTEM"] = array_values($arResult["JS_DATA"]["PAY_SYSTEM"]);
		foreach($arResult["PAY_SYSTEM"] as $keyPaysystem => $paySystem):
			if($paySystem["CODE"] == 'LOGICTIM_PAYMENT_BONUS')
				unset($arResult["PAY_SYSTEM"][$keyPaysystem]);
		endforeach;
		$arResult["PAY_SYSTEM"] = array_values($arResult["PAY_SYSTEM"]);
				
								
				
				
		global $APPLICATION;
		if(COption::GetOptionString("logictim.balls", "INTEGRATE_IN_SALE_ORDER_AJAX", 'N') == 'Y')
		{
			$APPLICATION->AddHeadScript('/bitrix/js/logictim.balls/sale_order_ajax.js');
			//CJSCore::Init(array("jquery2"));
		}
				

	}
		
}


?>