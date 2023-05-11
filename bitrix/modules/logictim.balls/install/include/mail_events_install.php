<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile(__FILE__);

use Bitrix\Main;
Main\Loader::includeModule("main");

//Poluchaem spisok saitov
$sites = array();
$rsSites = CSite::GetList($by="sort", $order="desc", array());
while($arSite = $rsSites->Fetch())
{
  $sites[] = $arSite["ID"];
}

$mailEvents = array(
					//pri nachislenii bonusov za zakaz
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_ORDER_ADD",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_ORDER_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_ORDER_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_ORDER_ADD",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_ORDER_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_ORDER_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//pri nachislenii bonusov za zakaz referala
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REFERAL_ADD",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_REFERAL_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_REFERAL_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REFERAL_ADD",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_REFERAL_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_REFERAL_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//Pri registracii
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REGISTER_ADD",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_REGISTER_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_REGISTER_ADD_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REGISTER_ADD",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_REGISTER_ADD_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_REGISTER_ADD_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//Na den' rojdeniya
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_BIRTHDAY_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_BIRTHDAY_ADD_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_BIRTHDAY_ADD",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_BIRTHDAY_ADD_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_BIRTHDAY_ADD_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//Akt dobroy voli
						array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_FREE_ADD_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FREE_ADD_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FREE_ADD_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FREE_ADD_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//Preduprejdenie o sgoranii bonusov
						array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_WARNING_END_TIME",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_WARNING_END_TIME_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_WARNING_END_TIME_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_WARNING_END_TIME",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_WARNING_END_TIME_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_WARNING_END_TIME_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//pri nachislenii bonusov za repost
					array(
						"EVENT" => array(
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REPOST",
										"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_REPOST_NAME"),
										"LID"  => "ru",
										"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_REPOST_DESCRIPTION")
									),
						"TEMPLATE" => array(
										"ACTIVE" => "Y",
										"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REPOST",
										"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_REPOST_SUBJECT"),
										"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_REPOST_MESSAGE"),
										"LID" => $sites,
										"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
										"EMAIL_TO" => "#EMAIL#",
										"BCC" => COption::GetOptionString("main", "all_bcc", ''),
										"BODY_TYPE" => "html",
									)
						),
					//pri nachislenii bonusov za otziv
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REVIEW",
											"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_REVIEW_NAME"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_REVIEW_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REVIEW",
											"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_REVIEW_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_REVIEW_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => "#EMAIL#",
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					//pri nachislenii bonusov za podpisku
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_SUBSCRIBE",
											"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_SUBSCRIBE_NAME"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_SUBSCRIBE_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_SUBSCRIBE",
											"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_SUBSCRIBE_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_SUBSCRIBE_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => "#EMAIL#",
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					//pri nachislenii bonusov za perehod po ssilke (referal)
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_LINK",
											"NAME" => GetMessage("logictim.balls_ME_BONUS_FROM_LINK_NAME"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_BONUS_FROM_LINK_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_FROM_LINK",
											"SUBJECT" => GetMessage("logictim.balls_MT_BONUS_FROM_LINK_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_BONUS_FROM_LINK_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => "#EMAIL#",
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					//pri zakritii zaprosa na vivod bonusov
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_CLOSE",
											"NAME" => GetMessage("logictim.balls_ME_EXIT_BONUS_CLOSE"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_EXIT_BONUS_CLOSE_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_CLOSE",
											"SUBJECT" => GetMessage("logictim.balls_MT_EXIT_BONUS_CLOSE_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_EXIT_BONUS_CLOSE_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => "#USER_EMAIL#",
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					//pri otmene zaprosa na vivod bonusov
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_CANCEL",
											"NAME" => GetMessage("logictim.balls_ME_EXIT_BONUS_CANCEL"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_EXIT_BONUS_CANCEL_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_CANCEL",
											"SUBJECT" => GetMessage("logictim.balls_MT_EXIT_BONUS_CANCEL_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_EXIT_BONUS_CANCEL_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => "#USER_EMAIL#",
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					//pri sozdanii zaprosa na vivod bonusov
					array(
							"EVENT" => array(
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_INSERT",
											"NAME" => GetMessage("logictim.balls_ME_EXIT_BONUS_INSERT"),
											"LID"  => "ru",
											"DESCRIPTION"   => GetMessage("logictim.balls_ME_EXIT_BONUS_INSERT_DESCRIPTION")
										),
							"TEMPLATE" => array(
											"ACTIVE" => "Y",
											"EVENT_NAME" => "LOGICTIM_BONUS_EXIT_BONUS_INSERT",
											"SUBJECT" => GetMessage("logictim.balls_MT_EXIT_BONUS_INSERT_SUBJECT"),
											"MESSAGE" => GetMessage("logictim.balls_MT_EXIT_BONUS_INSERT_MESSAGE"),
											"LID" => $sites,
											"EMAIL_FROM" => COption::GetOptionString("main", "email_from", ''),
											"EMAIL_TO" => COption::GetOptionString("main", "email_from", ''),
											"BCC" => COption::GetOptionString("main", "all_bcc", ''),
											"BODY_TYPE" => "html",
										)
						),
					
					);

//Sozdaem
foreach($mailEvents as $event):
	//Sobitie
	$obEventType = new CEventType;
	$obEventType->Add($event["EVENT"]);
	
	//Shablon
	$obTemplate = new CEventMessage;
	$obTemplate->Add($event["TEMPLATE"]);
endforeach;
?>