<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
IncludeModuleLangFile(__FILE__);

Bitrix\Main\Loader::includeModule("main");

//Poluchaem spisok saitov
$sites = array();
$rsSites = CSite::GetList($by="sort", $order="desc", array());
while($arSite = $rsSites->Fetch())
{
  $sites[] = $arSite["ID"];
}

$smsEvents = array(
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_ORDER_ADD_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_ORDER_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_ORDER_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#USER_PHONE_ORDER#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_ORDER_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FREE_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FREE_ADD_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FREE_ADD_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REFERAL_ADD_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_REFERAL_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_REFERAL_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_REFERAL_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REGISTER_ADD_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_REGISTER_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_REGISTER_ADD_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_REGISTER_ADD_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_BIRTHDAY_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_BIRTHDAY_ADD_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_BIRTHDAY_ADD_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_WARNING_END_TIME_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_WARNING_END_TIME_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_WARNING_END_TIME_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_WARNING_END_TIME_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REVIEW_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_REVIEW_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_REVIEW_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_REVIEW_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_LINK_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_LINK_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_LINK_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_LINK_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REPOST_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_REPOST_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_REPOST_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_REPOST_MESSAGE"),
									)
						),
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_SUBSCRIBE_SMS",
										"NAME" => GetMessage("logictim.balls_SE_BONUS_FROM_SUBSCRIBE_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_SE_BONUS_FROM_SUBSCRIBE_DESCRIPTION"),
										"EVENT_TYPE" => 'sms'
									),
						"TEMPLATE" => array(
										"ACTIVE" => 'Y',
										"SENDER" => "#DEFAULT_SENDER#",
										"RECEIVER" => "#PERSONAL_PHONE#",
										"LANGUAGE_ID" => "",
										"MESSAGE" => GetMessage("logictim.balls_ST_BONUS_FROM_SUBSCRIBE_MESSAGE"),
									)
						),
				);

foreach($smsEvents as $event):

	//Sobitie
	$obEventType = new CEventType;
	$obEventType->Add($event["EVENT"]);
	
	//Shablon
	$entity = Bitrix\Main\Sms\TemplateTable::getEntity();
	$template = $entity->createObject();
	
	
	$fields = $template->entity->getFields();
	$template->set("EVENT_NAME", $event["EVENT"]["EVENT_NAME"]);
	foreach($event["TEMPLATE"] as $fieldName => $value):
		$template->set($fieldName, $value);
	endforeach;
	foreach($sites as $siteId):
			$site = Bitrix\Main\SiteTable::getEntity()->wakeUpObject($siteId);
			$template->addToSites($site);
	endforeach;
	
	$result = $template->save();
	
	
	
	

endforeach;



?>