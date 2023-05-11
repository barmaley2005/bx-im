<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$checkOptions = \COption::GetOptionString("logictim.balls", "DISCOUNT_TO_PRODUCTS", 'no_set');
if($checkOptions == 'no_set'):
	//Set default options
	include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/logictim.balls/options.php');
	foreach($arOptions as $optionName => $arOption):
			$val = $arOption["DEFAULT"];
			\COption::SetOptionString($this->MODULE_ID, $optionName, $val, $arOption["TITLE"]);
	endforeach;
endif;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/update_client_partner.php');
$moduleID = "logictim.balls";
$arRequestedModules = array($moduleID);
$arUpdateList = CUpdateClientPartner::GetUpdatesList($errorMessage, LANG, 'Y', $arRequestedModules, array('fullmoduleinfo' => 'Y'));
if($arUpdateList && isset($arUpdateList['MODULE'])):
	foreach($arUpdateList['MODULE'] as $arModule):
		if($arModule['@']['ID'] === $moduleID)
			$myModuleInfo = $arModule['@'];
	endforeach;
endif;

if(!empty($myModuleInfo)):
	if($dateTo = strtotime($arModuleInfo['DATE_TO']))
	{
		if($dateTo <= time())
			$supportEnd = 'Y';
	}
	else
	{
		//$supportEnd = 'Y';
	}
else:
	$pirat = 'Y';
endif;

if(CModule::IncludeModuleEx($moduleID) == 3)//demo end
	$demoEnd = 'Y';
elseif(CModule::IncludeModuleEx($moduleID) == 2)//demo
	$demo = 'Y';

if($demoEnd == 'Y' || $demo == 'Y' || $supportEnd == 'Y'):
	
?>
	<div class="demo_info">
    	<? 
		if($demo == 'Y') echo GetMessage("logictim.balls_BONUS_ADMIN_DEMO");
    	if($demoEnd == 'Y' || $pirat == 'Y') echo GetMessage("logictim.balls_BONUS_ADMIN_DEMO_END");
		if($supportEnd == 'Y') echo GetMessage("logictim.balls_BONUS_ADMIN_SUPPORT_END");
		?>
        <? if($demo == 'Y' || $demoEnd == 'Y') {?>
        	<a target="_blank" href="http://marketplace.1c-bitrix.ru/solutions/logictim.balls/" class="glo"><?=GetMessage("logictim.balls_BONUS_ADMIN_BUY")?></a>
        <? }
		if($supportEnd == 'Y') {?>
        	<a target="_blank" href="http://marketplace.1c-bitrix.ru/solutions/logictim.balls/" class="glo"><?=GetMessage("logictim.balls_BONUS_ADMIN_BUY_UPDATE")?></a>
        <? }?>
    </div>
<? endif;
?>