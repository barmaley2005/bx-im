<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeTemplateLangFile(__FILE__);

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('sale');

CUtil::JSPostUnescape();

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$action = htmlspecialcharsbx($request->getPost('ACTION'));

$arRes["POST"] = $_POST;
if($action == 'ADD_COUPON'):
	$partnerId = $USER->GetID();
	$arCoupon = LBReferalsApi::AddPartnerCoupon($partnerId, '');
	if($arCoupon["ERROR"] != '')
	{
		if($arCoupon["ERROR"] == 'NO_SET_DISCOUNT')
			$error[] = GetMessage("NO_SET_DISCOUNT");
		if($arCoupon["ERROR"] == 'ERROR_ADD_COUPON')
		{
			foreach($arCoupon["ERROR_TEXT"] as $ertext):
				$error[] = $ertext;
			endforeach;
		}
	}
	$arRes["COUPON"] = $arCoupon["COUPONE_CODE"];
endif;

if($action == 'ENTER_COUPON'):
	$couponCode = htmlspecialcharsbx($request->getPost('COUPON_CODE'));
	if($couponCode == '')
		$error[] = GetMessage("ENTER_COUPON_CODE");
	
	if(empty($error))
	{
		$partnerId = $USER->GetID();
		$arCoupon = LBReferalsApi::AddPartnerCoupon($partnerId, $couponCode);
		if($arCoupon["ERROR"] != '')
		{
			if($arCoupon["ERROR"] == 'NO_SET_DISCOUNT')
				$error[] = GetMessage("NO_SET_DISCOUNT");
			if($arCoupon["ERROR"] == 'ERROR_ADD_COUPON')
			{
				foreach($arCoupon["ERROR_TEXT"] as $ertext):
					$error[] = $ertext;
				endforeach;
			}
		}
		$arRes["COUPON"] = $arCoupon["COUPONE_CODE"];
	}
	
endif;

	if(!empty($error))
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

if($action == 'EXIT_BONUS'):
	$userId = $USER->GetID();
	$result = \Logictim\Balls\PayBonus\ExitBonus::QueryExitBonus($userId, $_POST["SUM"]);
	$arRes["RESULT"] = $result;
endif;






$APPLICATION->RestartBuffer();
header('Content-Type: application/json; charset='.LANG_CHARSET);
echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
die();

?>
