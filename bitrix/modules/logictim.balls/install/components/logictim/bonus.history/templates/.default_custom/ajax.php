<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('sale');

CUtil::JSPostUnescape();

global $DB;

$arRes["POST"] = $_POST;

$error = array();

if($_POST["ACTION"] == 'ENTER_COUPON'):
	
	$partnerId = $USER->GetID();
	$couponCode = $_POST["COUPON_CODE"];
	
	if($couponCode == '')
		$error[] = '??????? ????????';
		
	if(!empty($couponCode) && empty($error))
	{
		
		//????????? ??????? ?????? ? ???????? ????????????
		$rsUser = $DB->Query('SELECT * FROM logictim_balls_users where user_id='.$partnerId.' limit 1;', false, 'USER_ERROR');
		if($arUser = $rsUser->Fetch())
		{
			if($arUser["partner_coupon"] != '')
				$error[] = '? ??? ??? ??????? ???????? '.$arUser["partner_coupon"];
		}
		
		//????????? ????? ? ????????? ? ?????????????
		if(empty($error))
		{
			$rsData = $DB->Query('SELECT * FROM logictim_balls_users where partner_coupon="'.$couponCode.'" limit 1;', false, 'USER_ERROR');
			if($arUser = $rsData->Fetch())
				$error[] = '?????? ???????? ??? ???????? ? ??????? ????????????';
		}
		
			
		//????????? ??????? ?????? ? ???????
		if(empty($error))
		{
			$arCoupon = \Bitrix\Sale\DiscountCouponsManager::getData($couponCode, true);
			if($arCoupon["ID"] > 0)
			{
				$rsUser = $DB->Query('SELECT * FROM logictim_balls_users where user_id='.$partnerId.' limit 1;', false, 'USER_ERROR');
				if($arUser = $rsUser->Fetch())
					$DB->Query('UPDATE logictim_balls_users SET partner_coupon="'.$couponCode.'" WHERE user_id='.$partnerId.';', false, '');
				else
					$idRow = $DB->Insert('logictim_balls_users', array('user_id'=>$partnerId, 'partner_coupon'=>'"'.$couponCode.'"'), $err_mess.__LINE__);
			}
			else
				$error[] = '???????? ??????????? ? ???????';
		}
	}
	
	//$arCoupon = LBReferalsApi::AddPartnerCoupon($partnerId);
	if(empty($error))
		$arRes["COUPON"] = $couponCode;
	else
	{
		$arRes["ERROR"] = $error;
		
		$error_str = '';
		foreach($error as $key_er => $er):
			if($key_er > 0)
				$error_str .= '<br />';
			$error_str .= $er;
		endforeach;
		$arRes["ERROR_TEXT"] = $error_str;
	}
	
endif;






$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
die();

?>
