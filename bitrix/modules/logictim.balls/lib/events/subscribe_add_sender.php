<?
namespace Logictim\Balls\Events;

\Bitrix\Main\Loader::includeModule("logictim.balls");

class SubscribeAddSender {
	public static function AddSubscribe(\Bitrix\Main\Event $e)
	{
		//Get Conttac Id in module Sender
		$arFields = $e->getParameters();
		$mailingId = $arFields['fields']['MAILING_ID'];
		$contactId = $arFields['fields']['CONTACT_ID'];
		
		//Get Email from table 'b_sender_contact'
		global $DB;
		$arTable["TABLE_NAME"] = "b_sender_contact";
		$res=$DB->Query('select * from '.$arTable["TABLE_NAME"].' where id='.$contactId.';');
		$arContact = $res->Fetch();
		$email = $arContact["CODE"];
		
		//Get user from email
		$rsUser = \CUser::GetList(($by="id"), ($order="desc"), array("EMAIL" => $email));
		$arUser = $rsUser->Fetch();
		
		if(is_array($arUser) && $arUser["ID"] > 0)
			\Logictim\Balls\AddBonus\FromSubscribe::BonusFromSubscribe(array("MODULE_ID" => 'sender', "SUBSCRIBE_ID" => array($mailingId), "USER_ID" => $arUser["ID"]));
		
	}
}


?>