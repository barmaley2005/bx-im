<? require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");


$arTable = array("TABLE_NAME" => "logictim_balls_exit_bonus");
$pageLink = 'logictim_balls_exit_bonus.php';

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
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/logictim.balls/admin/exit_bonus/list.php");
else:
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/logictim.balls/admin/exit_bonus/edit.php");
endif;


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>