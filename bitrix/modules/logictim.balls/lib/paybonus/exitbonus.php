<?
namespace Logictim\Balls\PayBonus;

IncludeModuleLangFile(__FILE__);

use Bitrix\Sale;

class ExitBonus {
	public static function QueryExitBonus($userId, $sum)
	{
		$rsUser = \CUser::GetByID($userId);
		$arUser = $rsUser->Fetch();
		$arUser["USER_GROUPS"] = \CUser::GetUserGroup($userId);
		$arUser["USER_BONUS"] = \Logictim\Balls\Helpers::UserBallance($userId);
		
		$profileParams = array(
								"PROFILE_TYPE" => 'exit_bonus',
								"USER_GROUPS" => $arUser["USER_GROUPS"],
								"PARTNER" => array("PARTNER_ID" => $userId),
								"SITE_ID" => $arUser["LID"],
								"LIMIT" => 1,
								"SORT_FIELD_1" => 'sort',
								"SORT_ORDER_1" => 'DESC',
								"IGNORE_COND_TYPES" => array('ALL')
							);
		$arProfilesExitBonus = \Logictim\Balls\Profiles::getProfiles($profileParams);
		if(!empty($arProfilesExitBonus))
		{
			$canExitBonus = 'Y';
			$arProfileExitBonus = current($arProfilesExitBonus);
			$arProfileExitBonus["CONDITIONS"] = unserialize($arProfileExitBonus["other_conditions"]);
			$result["MIN_EXIT_BONUS"] = $arProfileExitBonus["CONDITIONS"]["MIN_EXIT_BONUS"];
			$result["MAX_EXIT_BONUS"] = $arProfileExitBonus["CONDITIONS"]["MAX_EXIT_BONUS"];
			$result["EXIT_BONUS"] = $sum;
			
			if($sum < $result["MIN_EXIT_BONUS"])
				$result["ERROR"][] = GetMessage("logictim.balls_BONUS_MIN_EXIT_BONUS").$result["MIN_EXIT_BONUS"];
			if($sum > $result["MAX_EXIT_BONUS"])
				$result["ERROR"][] = GetMessage("logictim.balls_BONUS_MAX_EXIT_BONUS").$result["MAX_EXIT_BONUS"];
			if($sum > $arUser["USER_BONUS"])
				$result["ERROR"][] = GetMessage("logictim.balls_BONUS_NO_EXIT_BONUS");
		}
		else
		{
			$result["ERROR"][] = GetMessage("logictim.balls_BONUS_CANT_EXIT_BONUS");
		}
		
		if(empty($result["ERROR"]))
		{
			global $DB, $USER;
			$time = time();
			$userInsert = $USER->GetID();
			
			//Sozdaem operaciyu spisaniya
			$arFields = array(
			  "MINUS_BONUS" => $sum,
			  "USER_ID" => $userId,
			  "OPERATION_TYPE" => 'EXIT_BONUS',
			  "OPERATION_NAME" => GetMessage("logictim.balls_BONUS_EXIT_BONUS"),
			);

			$operationId = \logictimBonusApi::MinusBonus($arFields);
			
			$arSaveFields = array();
			$arSaveFields['name'] = '"'.GetMessage("logictim.balls_BONUS_EXIT_BONUS_QUERY").'"';
			$arSaveFields['status'] = '"P"';
			$arSaveFields['sum'] = $sum;
			$arSaveFields['user'] = $userId;
			$arSaveFields['insert_user'] = $userInsert;
			$arSaveFields['date_insert'] = '"'.date('Y-m-d H:i:s', $time).'"';
			$arSaveFields['operation_output'] = $operationId;
			
			$id = $DB->Insert('logictim_balls_exit_bonus', $arSaveFields, $err_mess.__LINE__);
			$result["RESULT"] = 'OK';
			$result["RESULT_TEXT"] = GetMessage("logictim.balls_BONUS_OK_EXIT_BONUS");
			
			$mailFields = array( 
							"EXIT_ID" => $id,
							"EXIT_NAME" => $arSaveFields['name'],
							"EXIT_SUM" => $arSaveFields['sum'],
							"DATE_INSERT" => $DB->FormatDate($arSaveFields['date_insert'], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()),
							"USER_NAME" => $arUser["NAME"],
							"USER_LAST_NAME" => $arUser["LAST_NAME"],
							"USER_SECOND_NAME" => $arUser["SECOND_NAME"],
							"USER_LOGIN" => $arUser["LOGIN"],
							"USER_EMAIL" => $arUser["EMAIL"],
							"SITE" => $_SERVER['SERVER_NAME'],
						) ;
			\CEvent::Send("LOGICTIM_BONUS_EXIT_BONUS_INSERT", \cHelper::SitesId(), $mailFields, 'N', '', array(), '');
			
		}
		else
		{
			$result["RESULT"] = 'ERROR';
			
			$errorText = '';
			$i = 0;
			foreach($result["ERROR"] as $error):
				$i++;
				if($i > 1)
					$errorText .= '<br />';
				$errorText .= $error;
			endforeach;
			
			$result["RESULT_TEXT"] = $errorText;
		}
		
		
		
		return $result;
	}
	
	public static function Statuses()
	{
		$arStatuses = array(
					"P" => GetMessage("logictim.balls_EXIT_STATUS_P"),
					"F" => GetMessage("logictim.balls_EXIT_STATUS_F"),
					"C" => GetMessage("logictim.balls_EXIT_STATUS_C")
					);
		return $arStatuses;
	}
	
	public static function QueryClose($request)
	{
		global $DB;
		$arExitDB = $DB->Query('SELECT * FROM logictim_balls_exit_bonus WHERE id='.$request['id'].';', false, $err_mess.__LINE__);
		$arExit = $arExitDB->Fetch();
		
		if($arExit["status"] == 'F' || $arExit["status"] == 'C')
			return;
		
		$arSaveFields['status'] = '"F"';
		$arSaveFields['date_close'] = '"'.date('Y-m-d H:i:s', time()).'"';
		if($request['comment_admin'] != '')
			$arSaveFields['comment_admin'] = '"'.$request['comment_admin'].'"';
		$DB->Update('logictim_balls_exit_bonus', $arSaveFields, "where id='".$request['id']."'");
		
		$rsUser = \CUser::GetByID($arExit["user"]);
		$arUser = $rsUser->Fetch();
		$mailFields = array( 
							"EXIT_ID" => $request['id'],
							"EXIT_NAME" => $arExit['name'],
							"EXIT_SUM" => $arExit["sum"],
							"DATE_INSERT" => $DB->FormatDate($arExit["date_insert"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()),
							"DATE_CLOSE" => $DB->FormatDate($arExit["date_close"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()),
							"COMMENT_ADMIN" => nl2br($arSaveFields['comment_admin']),
							"USER_NAME" => $arUser["NAME"],
							"USER_LAST_NAME" => $arUser["LAST_NAME"],
							"USER_SECOND_NAME" => $arUser["SECOND_NAME"],
							"USER_LOGIN" => $arUser["LOGIN"],
							"USER_EMAIL" => $arUser["EMAIL"],
							"SITE" => $_SERVER['SERVER_NAME'],
						) ;
		\CEvent::Send("LOGICTIM_BONUS_EXIT_BONUS_CLOSE", \cHelper::SitesId(), $mailFields, 'N', '', array(), '');
		
		return $request['id'];
	}
	public static function QueryCancel($request)
	{
		global $DB;
		$arExitDB = $DB->Query('SELECT * FROM logictim_balls_exit_bonus WHERE id='.$request['id'].';', false, $err_mess.__LINE__);
		$arExit = $arExitDB->Fetch();
		
		if($arExit["status"] == 'F' || $arExit["status"] == 'C')
			return;
		
		$arFields = array(
						"ADD_BONUS" => $arExit["sum"],
						"USER_ID" => $arExit["user"],
						"OPERATION_TYPE" => 'EXIT_REFUND_BONUS',
						"OPERATION_NAME" => GetMessage("logictim.balls_EXIT_RETURN_OPERATION_NAME").$request['id'],
						"DEACTIVE_AFTER" => $request['deactive_after_period'],
						"DEACTIVE_AFTER_TYPE" => $request['deactive_after_type'],
					);
		$operationId = \logictimBonusApi::AddBonus($arFields);
		
		if($operationId > 0)
		{
			$arSaveFields['status'] = '"C"';
			$arSaveFields['operation_refund'] = $operationId;
			$arSaveFields['date_close'] = '"'.date('Y-m-d H:i:s', time()).'"';
			if($request['comment_admin'] != '')
				$arSaveFields['comment_admin'] = '"'.$request['comment_admin'].'"';
			$DB->Update('logictim_balls_exit_bonus', $arSaveFields, "where id='".$request['id']."'");
			
			$rsUser = \CUser::GetByID($arExit["user"]);
			$arUser = $rsUser->Fetch();
			$mailFields = array( 
								"EXIT_ID" => $request['id'],
								"EXIT_NAME" => $arExit['name'],
								"EXIT_SUM" => $arExit["sum"],
								"DATE_INSERT" => $DB->FormatDate($arExit["date_insert"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()),
								"DATE_CANCEL" => $DB->FormatDate($arExit["date_close"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat()),
								"COMMENT_ADMIN" => nl2br($arSaveFields['comment_admin']),
								"USER_NAME" => $arUser["NAME"],
								"USER_LAST_NAME" => $arUser["LAST_NAME"],
								"USER_SECOND_NAME" => $arUser["SECOND_NAME"],
								"USER_LOGIN" => $arUser["LOGIN"],
								"USER_EMAIL" => $arUser["EMAIL"],
								"SITE" => $_SERVER['SERVER_NAME'],
							) ;
			\CEvent::Send("LOGICTIM_BONUS_EXIT_BONUS_CANCEL", \cHelper::SitesId(), $mailFields, 'N', '', array(), '');
		}
		
		return $request['id'];
	}
}
?>