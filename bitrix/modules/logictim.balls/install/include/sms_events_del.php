 <?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$arEvents = array(
				'LOGICTIM_BONUS_FROM_ORDER_ADD_SMS', 
				'LOGICTIM_BONUS_FROM_FREE_ADD_SMS', 
				'LOGICTIM_BONUS_FROM_REFERAL_ADD_SMS', 
				'LOGICTIM_BONUS_FROM_REGISTER_ADD_SMS', 
				'LOGICTIM_BONUS_FROM_BIRTHDAY_ADD_SMS', 
				'LOGICTIM_BONUS_WARNING_END_TIME_SMS',
				'LOGICTIM_BONUS_FROM_REVIEW_SMS',
				'LOGICTIM_BONUS_FROM_LINK_SMS',
				'LOGICTIM_BONUS_FROM_REPOST_SMS',
				'LOGICTIM_BONUS_FROM_SUBSCRIBE_SMS',
				);

foreach($arEvents as $event):

	//Del events
	Bitrix\Main\Loader::includeModule("main");
	$et = new CEventType;
	$et->Delete($event);
	
	//del templates
	$filter = array();
	$filter["=EVENT_NAME"] = $event;
	$data = Bitrix\Main\Sms\TemplateTable::getList(array("filter" => $filter));
	while($temlate = $data->fetch())
		$result = Bitrix\Main\Sms\TemplateTable::delete($temlate["ID"]);	

endforeach;


?>
