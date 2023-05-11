<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Application,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);
	
CModule::IncludeModule("logictim.balls");

//Opredelyaem ID ibfobloka s operaciyami
CModule::IncludeModule("iblock");
$dbiblokOpertion = CIBlock::GetList(
						Array(), 
						Array(
							'ACTIVE'=>'Y', 
							"CODE"=>'logictim_bonus_operations'
						), false
					);
while($iblokOpertion = $dbiblokOpertion->Fetch())
{
	$iblokOperationsId = $iblokOpertion["ID"];
}

$aMenu = array(
    array(
        'parent_menu' => 'global_menu_marketing',
        'sort' => 0,
        'text' => GetMessage("logictim.balls_BONUS_ADMIN_MENU_MAIN"),
        'title' => GetMessage("logictim.balls_BONUS_ADMIN_MENU_MAIN"),
        'url' => '',
        'items_id' => 'logictim.balls',
		"icon"        => "logictim_balls_menu_icon",
		"items" => array(
							array(
								"url" => "/bitrix/admin/logictim_balls_profiles.php?lang=".LANGUAGE_ID,
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_PROFILES"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_PROFILES")
							),
							array(
								"url" => "/bitrix/admin/logictim_balls_hand_operations.php?lang=".LANGUAGE_ID,
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_HAND_OPERATIONS"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_HAND_OPERATIONS")
							),
							array(
								"url" => "/bitrix/admin/logictim_balls_exit_bonus.php?lang=".LANGUAGE_ID,
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_EXIT_BONUS"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_EXIT_BONUS")
							),
							array(
								"url" => "/bitrix/admin/settings.php?lang=ru&mid=logictim.balls&mid_menu=1",
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_OPTIONS"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_OPTIONS")
							),
							array(
								"url" => "/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=".$iblokOperationsId."&type=LOGICTIM_BONUS_STATISTIC&lang=".LANGUAGE_ID."",
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_HISTORY"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_HISTORY")
							),
							array(
								"url" => "/bitrix/admin/logictim_balls_information.php?lang=".LANGUAGE_ID,
								"title" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_INSTRUCTIONS"),
								"text" => GetMessage("logictim.balls_BONUS_ADMIN_MENU_INSTRUCTIONS")
							),
						)
		
		
    )
);


return $aMenu;
?>