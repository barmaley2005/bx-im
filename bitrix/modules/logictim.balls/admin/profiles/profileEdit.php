<? require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

use \Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc;
	

if($request['id'] == 'new')
{
	$profileId = 'new';
	if($request['type'])
		$profileType = $request['type'];
	else
		$profileType = 'order';
}
else
{
	$profileId = (int)$request['id'];
	$res=$DB->Query('select * from '.$arTable["TABLE_NAME"].' where id='.$profileId.';');
	$arProfile = $res->Fetch();
	$profileType = $arProfile["type"];
	
	if($request['action'] == 'copy')
		$profileId = 'new';
}
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/admin/header.php');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/logictim.balls/admin/profiles/profile_types/".$profileType.".php");





