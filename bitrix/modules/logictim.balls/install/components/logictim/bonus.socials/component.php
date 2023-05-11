<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CModule::IncludeModule("logictim.balls");
global $USER;



$userId = $USER->GetID();
$rsUser = CUser::GetByID($userId);
$profileParams = array(
					"PROFILE_TYPE" => 'sharing',
					//"USER_GROUPS" => \CUser::GetUserGroup($userId),
					"SITE_ID" => SITE_ID,
					"LIMIT" => 1,
					"SORT_FIELD_1" => 'sort',
					"SORT_ORDER_1" => 'DESC',
					"IGNORE_COND_TYPES" => array('ALL')
				);


$arProfiles = \Logictim\Balls\Profiles::getProfiles($profileParams);
$arProfile = end($arProfiles);
$arOptions = unserialize($arProfile["conditions"]);

$arResult = array();
if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
{
	$arResult["VK_APP_ID"] = COption::GetOptionString("logictim.balls", "VK_APP_ID", '');
	$arResult["FB_APP_ID"] = COption::GetOptionString("logictim.balls", "FB_APP_ID", '');
}
else
{
	$arResult["VK_APP_ID"] = $arOptions["vk_app_id"];
	$arResult["FB_APP_ID"] = $arOptions["fb_app_id"];
}

$this->IncludeComponentTemplate();
?>

