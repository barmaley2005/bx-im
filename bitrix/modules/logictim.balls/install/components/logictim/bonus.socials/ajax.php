<? 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $USER;
if(!empty($_POST) && $USER->IsAuthorized()){}
else{die('ZAPRET');}

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

use Bitrix\Main\Loader;
Loader::includeModule("logictim.balls"); 
Loader::includeModule("iblock"); 

//Poluchaem dannie iz zaprosa
use Bitrix\Main\Application; 
$request = Application::getInstance()->getContext()->getRequest();
$social_network = $request->getPost("social_network"); //(VK, FB, TW)
$page = $request->getPost("page");

$post_date = time();
$user_id = $GLOBALS['USER']->GetID(); //id usera na saite

$iblokRepostsId = cHelper::IblokRepostsId(); //id infobloka repostov
$SocalNetworks = cHelper::SocalNetworks(); //tipy soc setey iz svoystva infobloka
$PostStatuses = cHelper::PostStatuses(); //statusi repostov iz svoystva infobloka


if(COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
{
	$arResult = array(
						"BONUS_REPOST_VK" => COption::GetOptionString("logictim.balls", "BONUS_REPOST_VK", 0),
						"BONUS_REPOST_FB" => COption::GetOptionString("logictim.balls", "BONUS_REPOST_FB", 0),
						"BONUS_REPOST_OK" => COption::GetOptionString("logictim.balls", "BONUS_REPOST_OK", 0),
						"BONUS_REPOST_TW" => COption::GetOptionString("logictim.balls", "BONUS_REPOST_TW", 0),
						"BONUS_REPOST_ALL_TIME" => (int)COption::GetOptionString("logictim.balls", "BONUS_REPOST_ALL_TIME", 60)*60,//in seconds
						"BONUS_REPOST_ALL_COUNT" => (int)COption::GetOptionString("logictim.balls", "BONUS_REPOST_ALL_COUNT", 10),
						"BONUS_REPOST_PAGE_TIME" => (int)COption::GetOptionString("logictim.balls", "BONUS_REPOST_PAGE_TIME", 60)*60,//in seconds
						"BONUS_REPOST_PAGE_COUNT" => (int)COption::GetOptionString("logictim.balls", "BONUS_REPOST_PAGE_COUNT", 10),
						"BONUS_ALL_REPOST_SAVE" => COption::GetOptionString("logictim.balls", "BONUS_ALL_REPOST_SAVE", 'Y'),
						);
}
else
{
	$profileParams = array(
						"PROFILE_TYPE" => 'sharing',
						"USER_GROUPS" => \CUser::GetUserGroup($user_id),
						"SITE_ID" => SITE_ID,
						"LIMIT" => 1,
						"SORT_FIELD_1" => 'sort',
						"SORT_ORDER_1" => 'DESC',
						"IGNORE_COND_TYPES" => array('ALL')
					);
	$arProfiles = \Logictim\Balls\Profiles::getProfiles($profileParams);
	$arProfile = end($arProfiles);
	$arOptions = unserialize($arProfile["conditions"]);
	
	$reposrPageTime = 3600;
	if($arOptions["repost_page_type"] == 'MIN')
	{
		$reposrPageTime = $arOptions["repost_page_time"] * 60;
	}
	if($arOptions["repost_page_type"] == 'H')
	{
		$reposrPageTime = $arOptions["repost_page_time"] * 3600;
	}
	if($arOptions["repost_page_type"] == 'D')
	{
		$reposrPageTime = $arOptions["repost_page_time"] * 86400;
	}
	if($arOptions["repost_page_type"] == 'M')
	{
		$reposrPageTime = $arOptions["repost_page_time"] * 2592000;
	}
	
	$reposrAllTime = 3600;
	if($arOptions["repost_all_type"] == 'MIN')
	{
		$reposrAllTime = $arOptions["repost_all_time"] * 60;
	}
	if($arOptions["repost_all_type"] == 'H')
	{
		$reposrAllTime = $arOptions["repost_all_time"] * 3600;
	}
	if($arOptions["repost_all_type"] == 'D')
	{
		$reposrAllTime = $arOptions["repost_all_time"] * 86400;
	}
	if($arOptions["repost_all_type"] == 'M')
	{
		$reposrAllTime = $arOptions["repost_all_time"] * 2592000;
	}
	
	$arResult = array(
						"BONUS_REPOST_VK" => ((int)$arOptions["bonus_repost_vk"] > 0 ? $arOptions["bonus_repost_vk"] : 0),
						"BONUS_REPOST_FB" => ((int)$arOptions["bonus_repost_fb"] > 0 ? $arOptions["bonus_repost_fb"] : 0),
						"BONUS_REPOST_OK" => ((int)$arOptions["bonus_repost_ok"] > 0 ? $arOptions["bonus_repost_ok"] : 0),
						"BONUS_REPOST_TW" => 0,
						"BONUS_REPOST_ALL_TIME" => $reposrAllTime,//in seconds
						"BONUS_REPOST_ALL_COUNT" => ((int)$arOptions["repost_all_count"] > 0 ? $arOptions["repost_all_count"] : 10),
						"BONUS_REPOST_PAGE_TIME" => $reposrPageTime,//in seconds
						"BONUS_REPOST_PAGE_COUNT" => ((int)$arOptions["repost_page_count"] > 0 ? $arOptions["repost_page_count"] : 10),
						"BONUS_ALL_REPOST_SAVE" => $arOptions["repost_all_save"],
						);
}






//Skol'ko bonusov nachislyat'
$bonusAdd = 0;
if($social_network == 'VK')
{
	$bonusAdd = $arResult["BONUS_REPOST_VK"];
}
elseif($social_network == 'FB')
{
	$bonusAdd = $arResult["BONUS_REPOST_FB"];
}
elseif($social_network == 'TW')
{
	$bonusAdd = $arResult["BONUS_REPOST_TW"];
}
elseif($social_network == 'OK')
{
	$bonusAdd = $arResult["BONUS_REPOST_OK"];
}

//Nazvanie soc seti iz infobloka
$arSocials = CIBlockProperty::GetPropertyEnum('SOCIAL_NETWORK', array(), array("ID" => $SocalNetworks[$social_network]));
if($arSocial = $arSocials->GetNext())
	$socialNetworkName = $arSocial['VALUE'];


$CanAddBonus = 'Y'; //Peremennaya, dlya proverki razresheniya nachisleniya
$comment = '';

//CHECK ALL REPOSTS COUNT
if($CanAddBonus == 'Y'):
	$date = date('Y-m-d H:i:s', time() - $arResult["BONUS_REPOST_ALL_TIME"]);
	$arFilterCheckAllReposts = Array(
				'IBLOCK_CODE' => 'logictim_bonus_reposts',
				'>=PROPERTY_SOCIAL_POST_DATE' => $date,
				'PROPERTY_SITE_USER' => $user_id,
				'PROPERTY_POST_STATUS' => $PostStatuses["BONUS_ADD"],
				);
	$resCheckAll = CIBlockElement::GetList(array("ID" => "ASC"), $arFilterCheckAllReposts, false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" ));
	$allRepostsCount = 0;
	while($obCheckAll = $resCheckAll->GetNextElement()) {
		$arCheckAll = $obCheckAll->GetFields();
		$allRepostsCount++;
	}
	if($allRepostsCount >= $arResult["BONUS_REPOST_ALL_COUNT"])
	{
		$CanAddBonus = 'N';
		$comment .= Loc::getMessage("LOGICTIM_BONUS_ERROR_COUNT_ALL_REPOST");
	}
endif;
//CHECK ALL REPOSTS COUNT

//CHECK ONE NETWORK REPOSTS COUNT
if($CanAddBonus == 'Y'):
	$date = date('Y-m-d H:i:s', time() - $arResult["BONUS_REPOST_PAGE_TIME"]);
	$arFilterCheckAllReposts = Array(
				'IBLOCK_CODE' => 'logictim_bonus_reposts',
				'>=PROPERTY_SOCIAL_POST_DATE' => $date,
				'PROPERTY_SITE_USER' => $user_id,
				'PROPERTY_POST_STATUS' => $PostStatuses["BONUS_ADD"],
				'PROPERTY_PAGE' => $page,
				);
	$resCheckAll = CIBlockElement::GetList(array("ID" => "ASC"), $arFilterCheckAllReposts, false, Array("nPageSize"=>PHP_INT_MAX), array("ID", "IBLOCK_ID", "NAME", "DATE_CREATE", "PROPERTY_*" ));
	$allRepostsCount = 0;
	while($obCheckAll = $resCheckAll->GetNextElement()) {
		$arCheckAll = $obCheckAll->GetFields();
		$allRepostsCount++;
	}
	if($allRepostsCount >= $arResult["BONUS_REPOST_PAGE_COUNT"])
	{
		$CanAddBonus = 'N';
		if($comment != '')
			$comment .= ', ';
		$comment .= Loc::getMessage("LOGICTIM_BONUS_ERROR_COUNT_PAGE_REPOST");
	}
endif;
//CHECK ONE NETWORK REPOSTS COUNT

if($CanAddBonus == 'Y' || $arResult["BONUS_ALL_REPOST_SAVE"] == 'Y'):

	//Add Repost
		$newOperation = new CIBlockElement;
		$PROP = array();
		$PROP["SOCIAL_NETWORK"] = Array("VALUE" => $SocalNetworks[$social_network]);
		$PROP["SITE_USER"] = $user_id;
		$PROP["SOCIAL_POST_DATE"] = ConvertTimeStamp($post_date,'FULL');
		$PROP["POST_STATUS"] = $PostStatuses["REPOST"];
		$PROP["PAGE"] = $page;
		$PROP["COMMENT"] = $comment;
		
		$newOperationArray = Array(
								"MODIFIED_BY"    =>  $GLOBALS['USER']->GetID(), 
								"IBLOCK_SECTION" => false,          
								"IBLOCK_ID"      => $iblokRepostsId,
								"IBLOCK_CODE "   => 'logictim_bonus_reposts',
								"PROPERTY_VALUES"=> $PROP,
								"NAME"           => Loc::getMessage("LOGICTIM_BONUS_REPOST").$socialNetworkName,
								"ACTIVE"         => "Y"
								);
		if($postId = $newOperation->Add($newOperationArray));
	
	if($CanAddBonus == 'Y' && $postId > 0 && $bonusAdd > 0):
			$arFields = array(
						"ADD_BONUS" => $bonusAdd,
						"USER_ID" => $user_id,
						"OPERATION_TYPE" => 'ADD_FROM_REPOST',
						"OPERATION_NAME" => Loc::getMessage("LOGICTIM_BONUS_REPOST_ADD_BONUS").$socialNetworkName,
						"ACTIVE_AFTER" => $arProfile["active_after_period"],
						"ACTIVE_AFTER_TYPE" => $arProfile["active_after_type"],
						"DEACTIVE_AFTER" => $arProfile["deactive_after_period"],
						"DEACTIVE_AFTER_TYPE" => $arProfile["deactive_after_type"],
						"REPOST_ID" => $postId,
						"DETAIL_TEXT" => '',
						"MAIL_EVENT" => array(
												"EVENT_NAME" => "LOGICTIM_BONUS_FROM_REPOST",
												"CUSTOM_FIELDS" => array(
																		"SOCIAL_NETWORK" => $socialNetworkName,
																		"PAGE" => 'http://'.$_SERVER["SERVER_NAME"].$page,
																		"SOCIAL_POST_DATE" => ConvertTimeStamp($post_date,'FULL'),
																		),
											),
						"SMS_EVENT" => array(
                                      "EVENT_NAME" => "LOGICTIM_BONUS_FROM_REPOST_SMS",
									  "CUSTOM_FIELDS" => array(
																"SOCIAL_NETWORK" => $socialNetworkName,
																"PAGE" => 'http://'.$_SERVER["SERVER_NAME"].$page,
																"SOCIAL_POST_DATE" => ConvertTimeStamp($post_date,'FULL'),
																),
                                        )
					);
			$operationId = logictimBonusApi::AddBonus($arFields);
		if($operationId > 0)
		{
			CIBlockElement::SetPropertyValuesEx($postId, false, array("POST_STATUS" => $PostStatuses["BONUS_ADD"], "OPERATION_ID" => $operationId));
		}
	endif;
	if($CanAddBonus == 'N' && $postId > 0):
		CIBlockElement::SetPropertyValuesEx($postId, false, array("POST_STATUS" => $PostStatuses["LIMIT"], "COMMENT" => $comment));
	endif;
endif;
?>

