<?php
class cLTBAfterUserAdd {
	static function AfterUserAdd($arFields)
	{
		
		if($arFields["ID"] > 0)
			$user_id = $arFields["ID"];
		else
			return;
			
		if(COption::GetOptionString("logictim.balls", "USER_REGISTER", 'ADD') == 'ADD')
		{
			$arFields["USER_ID"] = $arFields["ID"];
			cBonusFromRegister::BonusFromRegister($arFields);
		}
			
		if(isset($_COOKIE["LT_BONUS_REFERAL"]) && $_COOKIE["LT_BONUS_REFERAL"] > 0) {
			$partnerId = $_COOKIE["LT_BONUS_REFERAL"];
		}
		if(isset($_SESSION['LT_BONUS_REFERAL']) && $_SESSION['LT_BONUS_REFERAL'] > 0) {
			$partnerId = $_SESSION['LT_BONUS_REFERAL'];
		}
		
		if($partnerId > 0)
		{
			$partnerUser = CUser::GetByID($partnerId);
			$arPartnerUser = $partnerUser->Fetch();
		}
		
		if(empty($arPartnerUser)) return;
		
		//Opredelyaem ID ibfobloka s referalami
		$iblokReferalsId = cHelper::IblokLReferalsId();
		
		//Sozdaem referala
		CModule::IncludeModule("iblock");
		$newReferal = new CIBlockElement;
					$PROP = array();
					$PROP["REFERAL"] = $arFields["ID"];
					$PROP["PARTNER"] = $arPartnerUser["ID"];
					$newReferalArray = Array(
											"MODIFIED_BY"    =>  $arFields["ID"], 
											"IBLOCK_SECTION" => false,          
											"IBLOCK_ID"      => $iblokReferalsId,
											"IBLOCK_CODE "   => 'logictim_bonus_referals',
											"PROPERTY_VALUES"=> $PROP,
											"NAME"           => 'Referal:'.$arFields["ID"],
											"ACTIVE"         => "Y"
											);
					if($newReferal->Add($newReferalArray));
		
	}
}


?>