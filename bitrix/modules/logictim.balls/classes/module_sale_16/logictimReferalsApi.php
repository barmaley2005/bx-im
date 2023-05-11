<?
use Bitrix\Main;
use Bitrix\Sale;
Main\Loader::includeModule("sale");
Main\Loader::includeModule("iblock");
Main\Loader::includeModule("main");

class LBReferalsApi
{
	public static function AddPartnerCoupon($partnerId, $enterCouponCode = '')
	{
		global $DB;
		$discountId = (int)COption::GetOptionString("logictim.balls", "REFERAL_COUPON_DISCOUNT", 0);
		
		//select discount from profaile
		if(COption::GetOptionString("logictim.balls", "REFERAL_COUPON_DISCOUNT_IN_PROFILE", 'N') == 'Y'):
			$profileParams = array(
									"PROFILE_TYPE" => 'order_referal',
									"PARTNER" => array("PARTNER_ID" => $partnerId),
									"SITE_ID" => SITE_ID,
									"SORT_FIELD_1" => 'sort',
									"SORT_ORDER_1" => 'ASC',
									"IGNORE_COND_TYPES" => array('ALL')
								);
			$arProfilesOrderRef = \Logictim\Balls\Profiles::getProfiles($profileParams);
			$arProfilesOrderRef = end($arProfilesOrderRef);
			$arOptions = unserialize($arProfilesOrderRef["other_conditions"]);
			if((int)$arOptions["referal_coupon_discount"] > 0)
				$discountId = (int)$arOptions["referal_coupon_discount"];
		endif;
		//select discount from profaile
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($partnerId);
		if($discountId > 0 && $UserBonusSystemDostup == 'Y'):
			
			$couponCode = LBReferalsApi::GetPartnerCoupon($partnerId);
			if(!empty($couponCode))
			{
				$arResult = array("ERROR" => 'PARTNER_HAVE_COUPONE', "COUPONE_CODE" => $couponCode);
				return $arResult;
			}
			
			if($enterCouponCode != '')
				$couponCode = $enterCouponCode;
			else
				$couponCode = LBReferalsApi::GenerateParnterCouponCode($partnerId);
				
			$activeFrom = new \Bitrix\Main\Type\DateTime();
			$activeTo = '';
			$addDb = \Bitrix\Sale\Internals\DiscountCouponTable::add(array(
				'DISCOUNT_ID' => $discountId,
				'COUPON' => $couponCode,
				'TYPE' => \Bitrix\Sale\Internals\DiscountCouponTable::TYPE_MULTI_ORDER,
				'ACTIVE_FROM' => $activeFrom,
				'ACTIVE_TO' => $activeTo,
				//'MAX_USE' => 1,
				//'USER_ID' => 1,
				//'DESCRIPTION' => ''
			));
			if($addDb->isSuccess())
			{
				$rsUser = $DB->Query('SELECT * FROM logictim_balls_users where user_id='.$partnerId.' limit 1;', false, 'USER_ERROR');
				if($arUser = $rsUser->Fetch())
					$DB->Query('UPDATE logictim_balls_users SET partner_coupon="'.$couponCode.'" WHERE user_id='.$partnerId.';', false, '');
				else
					$idRow = $DB->Insert('logictim_balls_users', array('user_id'=>$partnerId, 'partner_coupon'=>'"'.$couponCode.'"'), $err_mess.__LINE__);
					
				$partnerGroups = unserialize(COption::GetOptionString('logictim.balls', "PARTNER_GROUPS", array()));
				if(!empty($partnerGroups))
				{
					CUser::SetUserGroup($partnerId, array_merge(CUser::GetUserGroup($partnerId), $partnerGroups));
					global $USER;
					$USER->SetUserGroupArray(array_merge(CUser::GetUserGroup($partnerId), $partnerGroups));
				}
				
				$arResult = array("COUPONE_CODE" => $couponCode);
				return $arResult;
			} else {
				$arResult = array("ERROR" => 'ERROR_ADD_COUPON', "ERROR_TEXT" => $addDb->getErrorMessages());
				return $arResult;
			}
		else:
			if(!$discountId)
			{
				$arResult = array("ERROR" => 'NO_SET_DISCOUNT');
				return $arResult;
			}
				
				
		endif;
	}
	public static function GetPartnerCoupon($partnerId)
	{
		global $DB;
		$arID = array();
		$rsData = $DB->Query('SELECT * FROM logictim_balls_users where user_id='.$partnerId.' limit 1;', false, 'USER_ERROR');
		if($arUser = $rsData->Fetch())
			$couponCode = $arUser["partner_coupon"];
		
		if(!empty($couponCode))
		{
			$arCoupon = \Bitrix\Sale\DiscountCouponsManager::getData($couponCode, true);
			if($arCoupon["ID"] > 0)
				$couponCode = $arCoupon["COUPON"];
			else
			{
				$couponCode = '';
				$DB->Query('UPDATE logictim_balls_users SET partner_coupon="" WHERE user_id='.$partnerId.';', false, '');
			}
			
		}
		else
			$couponCode = '';
			
			
		return $couponCode;
		
	}
	public static function GetPartnerFromCoupon($couponCode)
	{
		global $DB;
		$rsData = $DB->Query('SELECT * FROM logictim_balls_users where partner_coupon="'.$couponCode.'" limit 1;', false, 'USER_ERROR');
		if($arUser = $rsData->Fetch())
			$partnerId = (int)$arUser["user_id"];
		return $partnerId;
	}
	
	public static function GenerateParnterCouponCode($partnerId)
	{
		//$couponCode = \Bitrix\Sale\Internals\DiscountCouponTable::generateCoupon(true);
		
		//SOBITIE DO GENERACII CUPONA
		$arFields = array("PARTNRER_ID" => $partnerId, "CUSTOM_COUPON_CODE" => '');
		$event = new \Bitrix\Main\Event("logictim.balls", "BeforeGenerateParnterCouponCode", $arFields);
		$event->send();
		if($event->getResults())
		{
			foreach ($event->getResults() as $eventResult):
				$arFields = $eventResult->getParameters();
			endforeach;
		}
		if($arFields["CUSTOM_COUPON_CODE"] != '')
			return $arFields["CUSTOM_COUPON_CODE"];
		//SOBITIE DO GENERACII CUPONA
		
		$GenerateRule = COption::GetOptionString("logictim.balls", "REFERAL_COUPON_PREFIX", '');
		if($GenerateRule != '')
		{
			$arVars = array('#USER_ID#', '#AUTO#');
			foreach($arVars as $var):
				
				if(strpos($GenerateRule, $var) !== false)
				{
					if($var == '#USER_ID#')
						$varVal = $partnerId;
					if($var == '#AUTO#')
						$varVal = \Bitrix\Sale\Internals\DiscountCouponTable::generateCoupon(true);
					
					$GenerateRule = str_replace($var, $varVal, $GenerateRule);
				}
			endforeach;
			
			if($varVal != '')
				$couponCode = $GenerateRule;
			else
				$couponCode = 'PARTNER_'.$partnerId;
		}
		else
		{
			$couponCode = 'PARTNER_'.$partnerId;
		}
		
		return $couponCode;
	}
	
	public static function AddReferal($referalId, $partnerId)
	{
		//Opredelyaem ID ibfobloka s referalami
		$iblokReferalsId = cHelper::IblokLReferalsId();
		
		if($partnerId == $referalId)
			return;
			
		if($partnerId > 0)
		{
			$partnerUser = CUser::GetByID($partnerId);
			$arPartnerUser = $partnerUser->Fetch();
			if(empty($arPartnerUser)) return;
		}
		if($referalId > 0)
		{
			$referalUser = CUser::GetByID($referalId);
			$arReferalUser = $referalUser->Fetch();
			if(empty($arReferalUser)) return;
		}
		
		$UserBonusSystemDostup = cHelper::UserBonusSystemDostup($partnerId);
		if($UserBonusSystemDostup != 'Y') return;
		
		//Proveryaem, net li uzhe takogo referala
		$dbReferals = CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID"=>array($iblokReferalsId), "ACTIVE"=>"Y", "PROPERTY_REFERAL" => $referalId, "PROPERTY_PARTNER" => $partnerId), false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "NAME"));
		while($Op = $dbReferals->GetNextElement())
		{
			 $ReferalFields = $Op->GetFields();
		}
		if(!empty($ReferalFields))
			return;
			
		$newReferal = new CIBlockElement;
		$PROP = array();
					$PROP["REFERAL"] = $referalId;
					$PROP["PARTNER"] = $partnerId;
					$newReferalArray = Array(
											"MODIFIED_BY"    =>  $referalId, 
											"IBLOCK_SECTION" => false,          
											"IBLOCK_ID"      => $iblokReferalsId,
											"IBLOCK_CODE "   => 'logictim_referals_list',
											"PROPERTY_VALUES"=> $PROP,
											"NAME"           => 'Referal:'.$referalId,
											"ACTIVE"         => "Y"
											);
					if($newReferal->Add($newReferalArray));
	}
	
	public static function GetPartnersList($order_user_id)
	{
		$params = array(
					"LEVELS" => (int)COption::GetOptionString("logictim.balls", "REFERAL_LEVELS", 1),
					);
		
		$arPartners = array();
		
		$referalId = $order_user_id;
		while($x++ < $params["LEVELS"])
		{
			$dbRefers = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_CODE" => 'logictim_bonus_referals', "PROPERTY_REFERAL" => $referalId), false, array("nPageSize"=>1), array("ID", "NAME", "PROPERTY_PARTNER", "PROPERTY_REFERAL"));
			
			while($obRef = $dbRefers->GetNextElement())
			{
				$dbReferer = $obRef->GetFields();
				$arPartners[$x] = array("LEVEL" => $x, "PARTNER_ID" => $dbReferer["PROPERTY_PARTNER_VALUE"], "LAST_REFERAL" => $order_user_id, "MAIN_REFERAL" => $referalId);
				
				if($dbReferer["PROPERTY_PARTNER_VALUE"] > 0)
					$referalId = $dbReferer["PROPERTY_PARTNER_VALUE"];
			}
		}
		
		return $arPartners;
		
	}
	
	public static function GetReferalsList($user_id, $maxLevel)
	{
		if(!$maxLevel)
			$maxLevel = (int)COption::GetOptionString("logictim.balls", "REFERAL_LEVELS", 1);
			
		$arReferals = array();
		
		$arNextUsersId = array();
		while($x++ < $maxLevel)
		{
			if($x > 1 && !empty($arNextUsersId))
				$arPartners = $arNextUsersId;
			else
				$arPartners[] = $user_id;
			
			$dbRefers = CIBlockElement::GetList(array("ID"=>"ASC"), array("IBLOCK_CODE" => 'logictim_bonus_referals', "PROPERTY_PARTNER" => $arPartners), false, false, array("ID", "NAME", "PROPERTY_PARTNER", "PROPERTY_REFERAL"));
			while($obRef = $dbRefers->GetNextElement())
			{
				$dbReferal = $obRef->GetFields();
				$arReferals[$x][$dbReferal["PROPERTY_REFERAL_VALUE"]] = array(
																			"REFERAL_ID" => $dbReferal["PROPERTY_REFERAL_VALUE"],
																			"PARTNER_ID" => $dbReferal["PROPERTY_PARTNER_VALUE"],
																			"REFERAL_LEVEL" => $x
																		);
				
				$arNextUsersId[] = $dbReferal["PROPERTY_REFERAL_VALUE"];
			}
			$arNextUsersId = array_diff($arNextUsersId, $arPartners);
			
			
		}
		
		return $arReferals;
		
	}
}
?>