<?
define("STOP_STATISTICS", true);
define('NO_AGENT_CHECK', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('sale');

CUtil::JSPostUnescape();

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$type = htmlspecialcharsbx($request->getPost('TYPE'));


if($type == 'UserBallance'):

	global $USER;
	$user_id = (int)$USER->GetID();
	
	$user_id_post = (int)htmlspecialcharsbx($request->getPost('USER_ID'));
	$rights = $APPLICATION->GetGroupRight("logictim.balls");
	if($user_id == $user_id_post || $rights > 'D')
	{
		$userBonus = cHelper::UserBallance($user_id_post);
		$arRes["USER_BONUS"] = $userBonus;
	}
	else
		$arRes["USER_BONUS"] = 'net dostupa';
	
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json; charset='.LANG_CHARSET);
	echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
	die();

endif;


if($type == 'HandOperation'):

	global $USER;
	$rights = $APPLICATION->GetGroupRight("logictim.balls");
	if( $rights <= 'D')
		die();

	$arParams["STEP_TYPE"] = 'time';
	$arParams["STEP_TIME_LIMIT"] = 10;
	$arParams["STEP_COUNT_LIMIT"] = 10;
	
	$session = $_SESSION['lb_hand_operation'];
	
	//STEPS
	if($arParams["STEP_TYPE"] == 'time'):
		$timeBegin = time();
		$timeEnd = $timeBegin + $arParams["STEP_TIME_LIMIT"] - 2;
	endif;
	session_start();
	if(isset($session['progress_counter']))
		$arParams["STEP_FROM"] = $session['progress_counter'];
	else
		$arParams["STEP_FROM"] = 0;
	$arParams["STEP_TO"] = $arParams["STEP_FROM"] + $arParams["STEP_COUNT_LIMIT"];
	
	$arElements = $session['elements'];
	$count = $session['count'];
	
	$i = 0;
	foreach($arElements as $arElement):
		$i++;
		
		if($i <= $arParams["STEP_FROM"])
			continue;
			
		//-------iteration user-------//
		$operationType = $session['operation_params']['operationType'];
		$operationSum = $session['operation_params']['operationSum'];
		$deactivePeriod = $session['operation_params']['deactivePeriod'];
		$deactiveType = $session['operation_params']['deactiveType'];
		$user_id = $arElement;
		$operationName = $session['operation_params']['operationName'];
		if($operationType == 'plus')
		{
			$arFields = array(
			  "ADD_BONUS" => $operationSum,
			  "DEACTIVE_AFTER" => $deactivePeriod,
			  "DEACTIVE_AFTER_TYPE" => $deactiveType,
			  "USER_ID" => $user_id,
			  "OPERATION_TYPE" => 'USER_BALLANCE_CHANGE',
			  "OPERATION_NAME" => $operationName,
			  "MAIL_EVENT" => array(
							  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD",
								),
			  "SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD_SMS",
                                        ),
			);
			logictimBonusApi::AddBonus($arFields);
		}
		if($operationType == 'minus')
		{
			$arFields = array(
			  "MINUS_BONUS" => $operationSum,
			  "USER_ID" => $user_id,
			  "OPERATION_TYPE" => 'USER_BALLANCE_CHANGE',
			  "OPERATION_NAME" => $operationName,
			);
			logictimBonusApi::MinusBonus($arFields);
		}
		//-------iteration user-------//
		$arRes["ITERATIONS"][] = $i;
		
		if($i >= $arParams["STEP_TO"] || time() >= $timeEnd || $i == $count)
			break;
		
	endforeach;
	
	$arRes["STEP_FROM"] = $arParams["STEP_FROM"]; //for log
	$arRes["STEP_TO"] = $arParams["STEP_TO"]; //for log
	$arRes["COUNT_FOREACH"] = count($arElements); //for log
	
	$_SESSION['lb_hand_operation']['progress_counter'] = $i;
	$arRes["ITERATION"] = $i;
	$arRes["COUNT"] = $count;
	$arRes["PROGRESS_BAR"] = round($i*100/$count, 0);
	
	if($i == $count)
	{
		unset($_SESSION['lb_hand_operation']);
		$arRes["PROGRESS_BAR_END"] = 'Y';
	}
	
	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json; charset='.LANG_CHARSET);
	echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
	die();
	
endif;


if($type == 'PAY_ORDER_BONUS'):
	
	$orderId = htmlspecialcharsbx($request->getPost('ORDER_ID'));
	$payBonus = htmlspecialcharsbx($request->getPost('BONUS'));
	
	$arRes["ORDER_ID"] = $orderId;
	$arRes["BONUS"] = $payBonus;
	
	\Logictim\Balls\Api\OrderPayBonus::PayOrderBonus($orderId, $payBonus);

	$APPLICATION->RestartBuffer();
	header('Content-Type: application/json; charset='.LANG_CHARSET);
	echo \Bitrix\Main\Web\Json::encode($arRes, JSON_BIGINT_AS_STRING);
	die();

endif;


?>
