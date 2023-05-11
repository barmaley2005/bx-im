<?
class cSaleOrderAjax
{
	function SaleOrderAjaxEvent(&$arResult)
	{
		if(!CModule::IncludeModule('logictim.balls'))
			return;
		global $USER;
		
		$logictimBonus = array();
		
		$logictimBonus["ORDER_SUM"] = $arResult["ORDER_PRICE"] + $arResult["DELIVERY_PRICE"];
		$logictimBonus["DELIVERY_PRICE"] = $arResult["DELIVERY_PRICE"];
		$logictimBonus["CART_SUM"] = $arResult["ORDER_PRICE"];
		
		$logictimBonus["LOGICTIM_BONUS_USER_DOSTUP"] = cHelper::UserBonusSystemDostup('');
		
		$round = (int)COption::GetOptionString("logictim.balls", "BONUS_ROUND", 2);
		
		
		
		//Poluchaeb balans usera
		$logictimBonus["USER_BONUS"] = cHelper::UserBallance('');
		$round_user_bonus = round($logictimBonus["USER_BONUS"], $round);
		if($round_user_bonus > $logictimBonus["USER_BONUS"])
			$logictimBonus["USER_BONUS"] = $round_user_bonus - 1;
		else
			$logictimBonus["USER_BONUS"] = $round_user_bonus;
		
		
		
		if($USER->IsAuthorized() && $logictimBonus["LOGICTIM_BONUS_USER_DOSTUP"] == 'Y' && $logictimBonus["USER_BONUS"]):
		
			//Polucaem id svoistva zakaza 'LOGICTIM_PAYMENT_BONUS' (oplatit' ballami)
			$peronTypyId = '';
			foreach($arResult["PERSON_TYPE"] as $personType):
				if(isset($personType["CHECKED"]) && $personType["CHECKED"] == 'Y')
					$peronTypyId = $personType["ID"];
			endforeach;
			
			$dbOrderProps = CSaleOrderProps::GetList(
										array("SORT" => "ASC"),
										array("PERSON_TYPE_ID" => $peronTypyId, "CODE" => 'LOGICTIM_PAYMENT_BONUS'),
										false,
										false,
										array()
									);
					while ($arOrderProps = $dbOrderProps->GetNext())
					{
						$logictimBonus["ORDER_PROP_PAYMENT_BONUS_ID"] = $arOrderProps["ID"];
					}
					
			//Poluchaem minimal'nuju summu oplaty bonusami (iz nastroek modulja)
			$logictimBonus["MIN_BONUS"] = cHelper::MinBonusSum($logictimBonus["ORDER_SUM"], $logictimBonus["CART_SUM"], $logictimBonus["DELIVERY_PRICE"]);
			
			//Poluchaem maksimal'nuju summu oplaty bonusami (iz nastroek modulja)
			if(COption::GetOptionString("logictim.balls", "MAX_PAYMENT_DISCOUNT", 'N') == 'Y')
				$basket = $arResult["BASKET_ITEMS"];
			else
				$basket = '';
			$logictimBonus["MAX_BONUS"] = cHelper::MaxBonusSum($logictimBonus["ORDER_SUM"], $logictimBonus["CART_SUM"], $logictimBonus["DELIVERY_PRICE"], $basket);
			if($logictimBonus["MAX_BONUS"] > $logictimBonus["USER_BONUS"])
				$logictimBonus["MAX_BONUS"] = $logictimBonus["USER_BONUS"];
				
			
			
			//solko bonusov vveli v pole oplatat' bonusami
			if(isset($_REQUEST['ORDER_PROP_'.$arResult["ORDER_PROP_PAYMENT_BONUS_ID"]]))
				$input_bonus = $_REQUEST['ORDER_PROP_'.$logictimBonus["ORDER_PROP_PAYMENT_BONUS_ID"]];
			if(!is_numeric($input_bonus))
			{
				foreach($arResult["JS_DATA"]["ORDER_PROP"]["properties"] as $prop):
					if($prop["CODE"] == 'LOGICTIM_PAYMENT_BONUS' && $prop["VALUE"][0] != $prop["DEFAULT_VALUE"])
						$input_bonus = $prop["VALUE"][0];
				endforeach;
			}
			if($input_bonus)
				$logictimBonus["INPUT_BONUS"] = $input_bonus;
				
			//Shitaem, skolko bonusov postavit v pole "oplatiy' bonusami"
			if(isset($logictimBonus["INPUT_BONUS"]) && $logictimBonus["INPUT_BONUS"] >= 0)
				$payBonus = $logictimBonus["INPUT_BONUS"];
			else
				$payBonus = $logictimBonus["MAX_BONUS"];
				
			if($payBonus > $logictimBonus["ORDER_SUM"])
				$payBonus = $logictimBonus["ORDER_SUM"];
			if($payBonus > $logictimBonus["MAX_BONUS"])
				$payBonus = $logictimBonus["MAX_BONUS"];
			if($payBonus < $logictimBonus["MIN_BONUS"])
				$payBonus < $logictimBonus["MIN_BONUS"];
			
				
			
			
			if(COption::GetOptionString("logictim.balls", "ORDER_PAY_BONUS_AUTO", 'Y') != 'Y')
				$logictimBonus["PAY_BONUS"] = 0;
				
			$logictimBonus["PAY_BONUS"] = $payBonus;
			
			$arResult["PAY_BONUS"] = 1000;
		endif;
		
		
		$arResult["LOGICTIM_BONUS"] = $logictimBonus;
		$arResult["JS_DATA"]["LOGICTIM_BONUS"] = $logictimBonus;
	}
		
}


?>