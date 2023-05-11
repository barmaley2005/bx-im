<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//echo '<pre>'; print_r($arParams); echo '</pre>';

global $USER, $APPLICATION;
if ($USER->IsAuthorized()):
	CModule::IncludeModule("logictim.balls");
	
	$userId = $USER->GetID();
	$rsUser = CUser::GetByID($userId);
	$arUser = $rsUser->Fetch();
	$arUser["USER_GROUPS"] = \CUser::GetUserGroup($userId);
	
	//Vipolnyaem otpisku / podpisku na uvedomleniya o sgoranii bonusov
	$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	$subscribe = htmlspecialcharsbx($request->getQuery("lgb_subscribe"));
	$un_subscribe = htmlspecialcharsbx($request->getQuery("lgb_unsubscribe"));
	
	if($un_subscribe == 'Y')
	{
		global $USER_FIELD_MANAGER;
		$USER_FIELD_MANAGER->Update("USER", $USER->GetID(), array("UF_LGB_SUBSCRIBE" => 0));
	}
	if($subscribe == 'Y')
	{
		global $USER_FIELD_MANAGER;
		$USER_FIELD_MANAGER->Update("USER", $USER->GetID(), array("UF_LGB_SUBSCRIBE" => 1));
	}

	//Polucaem dostupnoe kolichestvo ballov
	$arParams["SELECT"] = array("UF_LOGICTIM_BONUS", "UF_LGB_SUBSCRIBE");
	$DBUserBonus = CUser::GetList(($by="ID"),($order="desc"),array("ID" => $USER->GetID()),$arParams);
	if ($arUserBonus = $DBUserBonus->Fetch()) {
		$arResult["UF_LGB_SUBSCRIBE"] = $arUserBonus["UF_LGB_SUBSCRIBE"];
	}
	
	$arResult["USER_BONUS"] = cHelper::UserBallance($USER->GetID());

	CModule::IncludeModule('iblock');
	$arIblocksCode = array('logictim_bonus_operations');
	if($arParams["OPERATIONS_WAIT"] == 'Y')
		$arIblocksCode = array('logictim_bonus_operations', 'logictim_bonus_wait');
	$arrSort = Array("ID" => $arParams["SORT"]);
	$arSelect = Array("ID", "IBLOCK_ID", "IBLOCK_CODE", "NAME", "DATE_CREATE", "PROPERTY_*" );
    $arFilter = Array(
				'IBLOCK_CODE' => $arIblocksCode,
				'PROPERTY_USER' => $USER->GetID()
				);

    $res = CIBlockElement::GetList($arrSort, $arFilter, false, Array("nPageSize"=>$arParams["PAGE_NAVIG_LIST"]), $arSelect);

	$arElements = array();
	while($ob = $res->GetNextElement()) {
		$arFields = $ob->GetFields();
		$arProps = $ob->GetProperties();
		$arElements[$arFields["ID"]] = $arFields;
		$arElements[$arFields["ID"]]["PROPS"] = $arProps;		
	}

	
	$arResult["ITEMS"] = $arElements;
	
	$arResult["NAV_STRING"] = $res->GetPageNavStringEx($navComponentObject, "", $arParams["PAGE_NAVIG_TEMP"]);
	
	//Ref ssilka usera
	$refLink = (CMain::IsHTTPS()) ? "https://" : "http://";
	$refLink .= $_SERVER["HTTP_HOST"].'/?'."ref=".$USER->GetID();
	
	$arResult["REF_LINK"] = $refLink;
	
	//Get partner coupon
	$partnerCoupon = LBReferalsApi::GetPartnerCoupon($USER->GetID());
	$arResult["COUPON"] = $partnerCoupon;
	
	//Proverka neobhodimosti pokaza ref ssilki i ref kuponov
	$refLinkView = 'N';
	$refCouponView = 'N';
	$subscribeView = 'N';
	if(\COption::GetOptionString('logictim.balls', 'MODULE_VERSION', '4') < 4)
	{
		if(COption::GetOptionString("logictim.balls", "BONUS_REFERAL", 0) > 0 || COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0) > 0)
			$refLinkView = 'Y';
		if(COption::GetOptionString("logictim.balls", "REFERAL_SYSTEM_TYPE", 0) > 0 && COption::GetOptionString("logictim.balls", "REFERAL_USE_COUPONS", 'N') == 'Y')
			$refCouponView = 'Y';
		if(COption::GetOptionString("logictim.balls", "LIVE_BONUS", 'N') == 'Y')
			$subscribeView = 'Y';
	}
	else
	{
		$subscribeView = 'Y';
		
		$profileParams = array(
								"PROFILE_TYPE" => 'order_referal',
								"PARTNER" => array("PARTNER_ID" => $userId),
								"SITE_ID" => $arUser["LID"],
								//"LIMIT" => 100,
								"SORT_FIELD_1" => 'sort',
								"SORT_ORDER_1" => 'ASC',
								"IGNORE_COND_TYPES" => array('ALL')
							);
		$arProfilesOrderRef = \Logictim\Balls\Profiles::getProfiles($profileParams);
		$arProfilesOrderRef = end($arProfilesOrderRef);
		$arOptions = unserialize($arProfilesOrderRef["other_conditions"]);

		$profileParams = array(
								"PROFILE_TYPE" => 'reflink',
								"USER_GROUPS" => $arUser["USER_GROUPS"],
								"SITE_ID" => $arUser["LID"],
								"LIMIT" => 1,
								"SORT_FIELD_1" => 'sort',
								"SORT_ORDER_1" => 'DESC',
							);
		$arProfilesRefLinlk = \Logictim\Balls\Profiles::getProfiles($profileParams);

		if(!empty($arProfilesOrderRef) || !empty($arProfilesRefLinlk))
			$refLinkView = 'Y';
			
		if($refLinkView == 'Y' && COption::GetOptionString("logictim.balls", "REFERAL_USE_COUPONS", 'N') == 'Y')
			$refCouponView = 'Y';
			
		$profileParams = array(
								"PROFILE_TYPE" => 'exit_bonus',
								"PARTNER" => array("PARTNER_ID" => $userId),
								"USER_GROUPS" => $arUser["USER_GROUPS"],
								"SITE_ID" => $arUser["LID"],
								"LIMIT" => 1,
								"SORT_FIELD_1" => 'sort',
								"SORT_ORDER_1" => 'DESC',
							);
		$arProfilesExitBonus = \Logictim\Balls\Profiles::getProfiles($profileParams);
		if(!empty($arProfilesExitBonus))
		{
			$arResult["EXIT_BONUS"]["CAN_EXIT"] = 'Y';
			$arProfileExitBonus = current($arProfilesExitBonus);
			$arProfileExitBonus["CONDITIONS"] = unserialize($arProfileExitBonus["other_conditions"]);
			$arResult["EXIT_BONUS"]["CONDITIONS"] = $arProfileExitBonus["CONDITIONS"];
		}
		
	}
	$arResult["VIEW_REF_LINK"] = $refLinkView;
	$arResult["VIEW_REF_COUPON"] = $refCouponView;
	$arResult["VIEW_SUBSCRIBE"] = $subscribeView;
	
	
	if($arResult["EXIT_BONUS"]["CAN_EXIT"] == 'Y'):
		global $DB;
		$sort = 'ORDER BY id DESC';
		
		$arNavParams = array("nPageSize" => $arParams["PAGE_NAVIG_TEMP"]);
		$arNavigation = CDBResult::GetNavParams($arNavParams);
		$cData = $DB->Query('SELECT * FROM logictim_balls_exit_bonus WHERE user='.$userId.' '.$sort.';', false, $err_mess.__LINE__);
		if(!empty($arNavParams['nPageSize'])){
			$cData->NavStart($arNavParams['nPageSize'],false);
		}
		
		$arResult["EXIT_BONUS"]["NAV_STRING"] = $cData->GetPageNavStringEx($navComponentObject, "", $arParams["PAGE_NAVIG_TEMP"]);
		
		while($exitQuery = $cData->Fetch())
		{
			$exitQuery["DATE_INSERT_FORMAT"] = $DB->FormatDate($exitQuery["date_insert"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat());
			$exitQuery["DATE_CLOSE_FORMAT"] = $DB->FormatDate($exitQuery["date_close"], "YYYY-MM-DD HH:MI:SS", \CSite::GetDateFormat());
			$arResult["EXIT_BONUS"]["ITEMS"][] = $exitQuery;
		}
		
		$arResult["EXIT_BONUS"]["STATUS"] = \Logictim\Balls\PayBonus\ExitBonus::Statuses();
	endif;
	
	
	
	
	//echo '<pre>'; print_r($arResult); echo '</pre>';
	
endif;
	
$this->IncludeComponentTemplate();

?>