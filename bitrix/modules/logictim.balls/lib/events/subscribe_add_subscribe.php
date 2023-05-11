<?
namespace Logictim\Balls\Events;

\Bitrix\Main\Loader::includeModule("logictim.balls");

class SubscribeAddSubscribe {
	public static function AddSubscribe($arFields)
	{
		if($arFields["USER_ID"] > 0)
			$user_id = $arFields["USER_ID"];
		else
		{
			if($arFields["EMAIL"] != '')
			{
				//Get user from email
				$rsUser = \CUser::GetList(($by="id"), ($order="desc"), array("EMAIL" => $arFields["EMAIL"]));
				$arUser = $rsUser->Fetch();
				$user_id = $arUser["ID"];
			}
		}
		
		if($arFields["CONFIRMED"] == 'Y')
			\Logictim\Balls\AddBonus\FromSubscribe::BonusFromSubscribe(array("MODULE_ID" => 'subscribe', "SUBSCRIBE_ID" => $arFields["RUB_ID"], "USER_ID" => $user_id));
		
	}
	
	function UpdateSubscribe($arFields)
	{
		if($arFields["USER_ID"] > 0)
			$user_id = $arFields["USER_ID"];
		else
		{
			if($arFields["EMAIL"] != '')
			{
				//Get user from email
				$rsUser = \CUser::GetList(($by="id"), ($order="desc"), array("EMAIL" => $arFields["EMAIL"]));
				$arUser = $rsUser->Fetch();
				$user_id = $arUser["ID"];
			}
		}
		
		if($arFields["CONFIRMED"] == 'Y')
			\Logictim\Balls\AddBonus\FromSubscribe::BonusFromSubscribe(array("MODULE_ID" => 'subscribe', "SUBSCRIBE_ID" => $arFields["RUB_ID"], "USER_ID" => $user_id));
		
	}
}


?>