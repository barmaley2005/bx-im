<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


$arTable = array("TABLE_NAME" => "logictim_balls_profiles");
$pageLink = 'logictim_balls_profiles.php';

use Bitrix\Main,
	Bitrix\Main\Application;


$module_id='logictim.balls';
\Bitrix\Main\Loader::includeModule($module_id);

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

global $APPLICATION, $DB;

$rights = $APPLICATION->GetGroupRight($module_id);

if($rights == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(empty($request['id'])):
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/logictim.balls/admin/profiles/profilesList.php");
else:
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/logictim.balls/admin/profiles/profileEdit.php");
endif;


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>