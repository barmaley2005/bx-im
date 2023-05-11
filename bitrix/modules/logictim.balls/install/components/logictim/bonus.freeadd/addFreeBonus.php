<? 
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

CModule::IncludeModule('logictim.balls');
CModule::IncludeModule('iblock');

use Bitrix\Main\Application; 
$request = Application::getInstance()->getContext()->getRequest(); 

$groupId = $request->getPost("select1");
$addBonus = $request->getPost("balls");

if($addBonus > 0 && !empty($groupId)):

echo Loc::getMessage("logictim.balls_ADD_FREE_BONUS_FOR_USERS").'<br /><br />';

//Opredelyaem ID ibfobloka s operaciyami
	$iblokOperationsId = cHelper::IblokOperationsId();

//Poluchaem vozmojnie znacheniya svoystava "OPERATION_TYPE"
	$operationsType = cHelper::OperationsType();

$filter = Array 
( 
	"GROUPS_ID"=> $groupId,
); 
$rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter);
$count = 0;
while($arItem = $rsUsers->GetNext()) 
{ 
	
	echo "[". $arItem['ID']."] (".$arItem['LOGIN'].") ".$arItem['NAME']." ".$arItem['LAST_NAME'].' + '.$addBonus."<br>";	
	
	//Nachislyaem bonusi useru
	
	if($addBonus > 0)
	{
		$arAddBonus = array(
			  "ADD_BONUS" => $addBonus,
			  "USER_ID" => $arItem['ID'],
			  "OPERATION_TYPE" => 'USER_BALLANCE_CHANGE',
			  "OPERATION_NAME" => Loc::getMessage("logictim.balls_ADD_FREE_BONUS").$arItem['ID'],
			  "MAIL_EVENT" => array(
								  "EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD",
									),
			   "SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_FREE_ADD_SMS",
                                        ),
			);

		logictimBonusApi::AddBonus($arAddBonus);
		
	}
	
	$count++;
}

echo '<br />'.Loc::getMessage("logictim.balls_ADD_FREE_COUNT_USERS").$count;

endif;

?>