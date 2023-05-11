<?
$showRightsTab = false;
$arSel = array(
				'REFERENCE_ID' => array(5, 6), 
				'REFERENCE' => array(
									GetMessage("logictim.balls_SELECT_METOD_5"), 
									GetMessage("logictim.balls_SELECT_METOD_6"), 
									)
				);
$arSelMinPaymentBonus = array(
				'REFERENCE_ID' => array(1, 2, 3, 4),
				'REFERENCE' => array(
									GetMessage("logictim.balls_SELECT_MIN_BONUS_TYPE_1"),
									GetMessage("logictim.balls_SELECT_MIN_BONUS_TYPE_2"),
									GetMessage("logictim.balls_SELECT_MIN_BONUS_TYPE_3"),
									GetMessage("logictim.balls_SELECT_MIN_BONUS_TYPE_4"),
									)
				);

$arSelMaxPaymentBonus = array(
				'REFERENCE_ID' => array(1, 2, 3, 4), 
				'REFERENCE' => array(
									GetMessage("logictim.balls_SELECT_MAX_BONUS_TYPE_1"),
									GetMessage("logictim.balls_SELECT_MAX_BONUS_TYPE_2"),
									GetMessage("logictim.balls_SELECT_MAX_BONUS_TYPE_3"),
									GetMessage("logictim.balls_SELECT_MAX_BONUS_TYPE_4"),
									)
				);
$arSelMailFormat = array(
				'REFERENCE_ID' => array(1, 2), 
				'REFERENCE' => array(
									'text',
									'html'
									)
				);
$arSelBonusProdType = array(
					'REFERENCE_ID' => array(1, 2), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_TYPE_PROC"),
										GetMessage("logictim.balls_TYPE_FIX"),
										)
					);
$arSelReferalSocials = array(
					'REFERENCE_ID' => array('vkontakte','facebook','odnoklassniki','moimir','gplus','twitter','lj','viber','whatsapp','skype','telegram'), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_VK"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_FB"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_OK"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_MM"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_GP"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_TW"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_LJ"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_VB"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_WA"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_SK"),
										GetMessage("logictim.balls_BONUS_REFERAL_NAME_TE"),
										)
					);
//select  all user groups
$userGrups = array();
$rsGroups = CGroup::GetList(($by="id"), ($order="asc"), array("ACTIVE"  => "Y"));
while($arUserGroups = $rsGroups->Fetch()) {
	$userGrups["REFERENCE_ID"][] = $arUserGroups["ID"];
	$userGrups["REFERENCE"][] = $arUserGroups["NAME"];
}

//select all sites
$sites = array();
$rsSites = CSite::GetList($by="sort", $order="desc", array());
while($arSite = $rsSites->Fetch())
{
  $sites["REFERENCE_ID"][] = $arSite["ID"];
  $sites["REFERENCE"][] = $arSite["NAME"];
}

//Select discounts
$arDiscounts = array();
$discountIterator = \Bitrix\Sale\Internals\DiscountTable::getList(array('filter' => array()));
$arDiscounts["REFERENCE_ID"][] = 0;
$arDiscounts["REFERENCE"][] = GetMessage("logictim.referals_REFERAL_COUPON_DISCOUNT_NO");
while($discount = $discountIterator->fetch())
{
	$arDiscounts["REFERENCE_ID"][] = $discount["ID"];
	$arDiscounts["REFERENCE"][] = $discount["NAME"];
}

//Select all blogs
if(CModule::IncludeModule("blog"))
{
	$arBlogs = array("REFERENCE_ID" => array('0'), "REFERENCE" => array('-'));
	$dbBlogs = CBlog::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
	while ($arBlog = $dbBlogs->Fetch())
	{
		$arBlogs["REFERENCE_ID"][] = $arBlog["ID"];
		$arBlogs["REFERENCE"][] = $arBlog["NAME"];
	}
}
else
{
	$arBlogs = array();
}
//Select all iblocks
if(CModule::IncludeModule("iblock"))
{
	$arIblocks = array("REFERENCE_ID" => array('0'), "REFERENCE" => array('-'));
	$dbIblocks = CIBlock::GetList(array("SORT"=>"NAME"), array("ACTIVE" => "Y"), false);
	while($arIblock = $dbIblocks->Fetch())
	{
		$arIblocks["REFERENCE_ID"][] = $arIblock["ID"];
		$arIblocks["REFERENCE"][] = $arIblock["NAME"];
	}
}
else
{
	$arIblocks = array();
}
//Select all forums
if(CModule::IncludeModule("forum"))
{
	$arForums = array("REFERENCE_ID" => array('0'), "REFERENCE" => array('-'));
	$dbForums = CForumNew::GetList($arOrder, array("ACTIVE" => "Y"));
	while ($arForum = $dbForums->Fetch())
	{
		$arForums["REFERENCE_ID"][] = $arForum["ID"];
		$arForums["REFERENCE"][] = $arForum["NAME"];
	}
}
else
{
	$arForums = array();
}

//Select currencies
//$arCurrency = array("REFERENCE_ID" => array('0'), "REFERENCE" => array('-'));
if(CModule::IncludeModule("sale"))
{
	$lcur = CCurrency::GetList(($by="sort"), ($order="asc"));
	while($lcur_res = $lcur->Fetch())
	{
		$arCurrency["REFERENCE_ID"][] = $lcur_res["CURRENCY"];
		$arCurrency["REFERENCE"][] = $lcur_res["FULL_NAME"];
		if($lcur_res["BASE"] == 'Y')
			$defaultCurrency = $lcur_res["CURRENCY"];
	}

}


$arTabs = array(
   array(
      'DIV' => 'edit1',
      'TAB' => GetMessage("logictim.balls_OPTIONS"),
      'ICON' => '',
      'TITLE' => GetMessage("logictim.balls_OPTIONS")
   ),
   array(
      'DIV' => 'edit2',
      'TAB' => GetMessage("logictim.balls_TAB_2"),
      'ICON' => '',
      'TITLE' => GetMessage("logictim.balls_TAB_2"),
   ),
   array(
      'DIV' => 'edit4',
      'TAB' => GetMessage("logictim.balls_TAB_4"),
      'ICON' => '',
      'TITLE' => GetMessage("logictim.balls_TAB_4"),
   ),
   array(
      'DIV' => 'edit3',
      'TAB' => GetMessage("logictim.balls_TAB_3"),
      'ICON' => '',
      'TITLE' => GetMessage("logictim.balls_TAB_3"),
   ),
    array(
      'DIV' => 'edit5',
      'TAB' => GetMessage("logictim.balls_TAB_5"),
      'ICON' => '',
      'TITLE' => GetMessage("logictim.balls_TAB_5"),
   ),
);

$arGroups = array(
   'MAIN' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MAIN"), 'TAB' => 0),
   'BONUS_TIME' => array('TITLE' => GetMessage("logictim.balls_BONUS_TIME_GROUP"), 'TAB' => 0),
   'EVENTS_ORDER_TO_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_EVENTS_ORDER"), 'TAB' => 0),
   'MEDOD_5_PROPERTY' => array('TITLE' => GetMessage("logictim.balls_MEDOD_5_PROPERTY"), 'TAB' => 1, 'CLASS' => 'BONUS_METOD BONUS_METOD_5'),
   'MEDOD_3_4_PROPERTY' => array('TITLE' => GetMessage("logictim.balls_MEDOD_3_4_PROPERTY"), 'TAB' => 1, 'CLASS' => 'BONUS_METOD BONUS_METOD_6'),
   'ORDERS_SUM_VARIANTS' => array('TITLE' => GetMessage("logictim.balls_ORDERS_SUM_VARIANTS"), 'TAB' => 1),
   'CART_SUM_VARIANTS' => array('TITLE' => GetMessage("logictim.balls_CART_SUM_VARIANTS"), 'TAB' => 1),
   'MIN_PAYMENT_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MIN_PAYMENT_BONUS"), 'TAB' => 0),
   'MAX_PAYMENT_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MAX_PAYMENT_BONUS"), 'TAB' => 0),
   'TEXT' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT"), 'TAB' => 0),

   'OTHER_BONUS' => array('TITLE' => GetMessage("logictim.balls_TAB_4"), 'TAB' => 2),
   'REFERAL_SYSTEM' => array('TITLE' => GetMessage("logictim.balls_BONUS_REFERAL_SYSTEM"), 'TAB' => 2),
   'REVIEWS_BONUS' => array('TITLE' => GetMessage("logictim.balls_REVIEWS_BONUS_GROUP"), 'TAB' => 2),
   'REFERAL_BONUS' => array('TITLE' => GetMessage("logictim.balls_BONUS_REFERAL_GROUP"), 'TAB' => 2),
   'SOCIALS_BONUS' => array('TITLE' => GetMessage("logictim.balls_BONUS_REPOST_GROUP"), 'TAB' => 2),
   
   'EVENTS_MAIL' => array('TITLE' => GetMessage("logictim.balls_EVENTS_MAIL_GROUP"), 'TAB' => 3),
   'SEND_MAIL' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_GROUP"), 'TAB' => 3),
   'SEND_MAIL_REGISTER' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_REGISTER_GROUP"), 'TAB' => 3),
   'SEND_MAIL_BIRTHDAY' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_BIRTHDAY_GROUP"), 'TAB' => 3),
   'FREE_BONUS' => array('TITLE' => GetMessage("logictim.balls_FREE_BONUS"), 'TAB' => 2),
   'ORDER_FORM' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_ORDER_FORM"), 'TAB' => 5),
   'BASKET_INTAGRATE' => array('TITLE' => GetMessage("logictim.balls_INTEGRATE_IN_SALE_BASKET"), 'TAB' => 5),
);

$arOptions = array(
	'MIN_PAYMENT_TYPE' => array(
      'GROUP' => 'MIN_PAYMENT_BONUS',
      'TITLE' => GetMessage("logictim.balls_MIN_PAYMENT_BONUS_TYPE"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelMinPaymentBonus,
	  'DEFAULT' => '1',
      'SORT' => '1'
   ),
	'MIN_PAYMENT_BONUS' => array(
      'GROUP' => 'MIN_PAYMENT_BONUS',
      'TITLE' => GetMessage("logictim.balls_MIN_PAYMENT_BONUS_LABEL"),
      'TYPE' => 'INT',
      'DEFAULT' => '0',
      'SORT' => '2',
      'REFRESH' => 'Y',
   ),
    'MAX_PAYMENT_TYPE' => array(
      'GROUP' => 'MAX_PAYMENT_BONUS',
      'TITLE' => GetMessage("logictim.balls_MAX_PAYMENT_BONUS_TYPE"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelMaxPaymentBonus,
	  'DEFAULT' => '1',
      'SORT' => '1'
   ),
   'MAX_PAYMENT_SUM' => array(
      'GROUP' => 'MAX_PAYMENT_BONUS',
      'TITLE' => GetMessage("logictim.balls_MAX_PAYMENT_BONUS_SUM"),
      'TYPE' => 'INT',
      'DEFAULT' => '0',
      'SORT' => '2',
      'REFRESH' => 'Y',
   ),
   'MAX_PAYMENT_DISCOUNT' => array(
      'GROUP' => 'MAX_PAYMENT_BONUS',
      'TITLE' => GetMessage("logictim.balls_MAX_PAYMENT_DISCOUNT"),
      'TYPE' => 'CHECKBOX',
      'DEFAULT' => 'N',
      'SORT' => '3',
   ),
   'EVENT_ORDER_END' => array(
      'GROUP' => 'EVENTS_ORDER_TO_BONUS',
      'TITLE' => GetMessage("logictim.balls_EVENT_ORDER_END"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '1'
   ),
   'EVENT_ORDER_PAYED' => array(
      'GROUP' => 'EVENTS_ORDER_TO_BONUS',
      'TITLE' => GetMessage("logictim.balls_EVENT_ORDER_PAYED"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '2'
   ),
   'BONUS_ORDER_WAIT' => array(
      'GROUP' => 'EVENTS_ORDER_TO_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_DELAY"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '3',
	  'REFRESH' => 'Y',
   ),
   'MODULE_VERSION' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_MODULE_VERSION"),
      'TYPE' => 'SELECT',
      'VALUES' => array('REFERENCE_ID' => array('3', '4'), 'REFERENCE' => array(GetMessage("logictim.balls_MODULE_VERSION_3"), GetMessage("logictim.balls_MODULE_VERSION_4"))),
	  'DEFAULT' => '4',
      'SORT' => '0',
	  'CLASS' => '',
	  'NOTES' => GetMessage("logictim.balls_VERSION_CHANGE_NOTES")
   ),
    'BONUS_BILL_DESCRIPTION' => array(
      'GROUP' => 'MAIN',
      'TYPE' => 'CUSTOM',
      'SORT' => '0',
	  'NOTES' => GetMessage("logictim.balls_BONUS_BILL_DESCRIPTION"),
   ),
   'BONUS_BILL' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_SELECT_BONUS_BILL"),
      'TYPE' => 'SELECT',
      'VALUES' => array('REFERENCE_ID' => array(1, 2), 'REFERENCE' => array(GetMessage("logictim.balls_BILL_BONUS"), GetMessage("logictim.balls_BILL_BITRIX"),)),
	  'DEFAULT' => '1',
      'SORT' => '1',
	  'CLASS' => 'LGB_PARENT_SELECT'
   ),
   'BONUS_CURRENCY' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_SELECT_BONUS_CURRENCY"),
      'TYPE' => 'SELECT',
      'VALUES' => $arCurrency,
	  'DEFAULT' => $defaultCurrency,
      'SORT' => '2',
	  'CLASS' => 'BONUS_BILL BONUS_BILL_2'
   ),
   'BONUS_METOD' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_SELECT_METOD_LABEL"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSel,
	  'DEFAULT' => '5',
      'SORT' => '3',
	  'CLASS' => 'LGB_PARENT_SELECT'
   ),
   'BONUS_ROUND' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_BONUS_ROUND"),
      'TYPE' => 'INT',
	  'MAX' => 4,
	  'DEFAULT' => '2',
      'SORT' => '4',
	  'REFRESH' => 'Y',
   ),
   'USER_GROUPS_TYPE' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_USER_GROUPS_TYPE"),
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array(1, 2), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_USER_GROUPS_TYPE_1"),
										GetMessage("logictim.balls_USER_GROUPS_TYPE_2")
										)
					),
	  'DEFAULT' => 1,
      'SORT' => '5',
   ),
   'USER_GROUPS' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_USER_GROUPS"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $userGrups,
	  'DEFAULT' => 2,
      'SORT' => '6',
   ),
   'SITES' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_SITE_SELECT"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $sites,
	  'DEFAULT' => '',
      'SORT' => '7',
   ),
   'LIVE_BONUS' => array(
      'GROUP' => 'BONUS_TIME',
      'TITLE' => GetMessage("logictim.balls_LIVE_BONUS"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '7',
   ),
   'LIVE_BONUS_TIME' => array(
      'GROUP' => 'BONUS_TIME',
      'TITLE' => GetMessage("logictim.balls_LIVE_BONUS_TIME"),
      'TYPE' => 'INT',
	  'DEFAULT' => '365',
      'SORT' => '8',
	  'REFRESH' => 'Y',
   ),
   'LIVE_BONUS_ALL' => array(
      'GROUP' => 'BONUS_TIME',
      'TITLE' => GetMessage("logictim.balls_LIVE_BONUS_ALL"),
      'TYPE' => 'CALENDAR',
      'VALUES' => '',
	  'DEFAULT' => '',
      'SORT' => '9',
	  'HIDE' => 'Y'
   ),
    'DISCOUNT_TO_PRODUCTS' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_DISCOUNT_METOD"),
      'TYPE' => 'SELECT',
	  'DEFAULT' => 'N',
	  'VALUES' => array(
					'REFERENCE_ID' => array('Y', 'B', 'N'), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_DISCOUNT_METOD_DISCOUNT"),
										GetMessage("logictim.balls_DISCOUNT_METOD_MOMENT_DISCOUNT"),
										GetMessage("logictim.balls_DISCOUNT_METOD_PAYMENT"),
										),
						),
      'SORT' => '10',
   ),
   
   'INTEGRATE_IN_SALE_ORDER_AJAX' => array(
      'GROUP' => 'ORDER_FORM',
      'TITLE' => GetMessage("logictim.balls_INTEGRATE_IN_SALE_ORDER_AJAX"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '1',
	  'NOTES' => GetMessage("logictim.balls_INTEGRATE_IN_SALE_ORDER_AJAX_NOTE")
   ),
   'INTEGRATE_IN_SALE_BASKET' => array(
      'GROUP' => 'BASKET_INTAGRATE',
      'TITLE' => GetMessage("logictim.balls_INTEGRATE_IN_SALE_BASKET_Y"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '1',
	  'NOTES' => GetMessage("logictim.balls_INTEGRATE_IN_SALE_ORDER_AJAX_NOTE")
   ),
   'ORDER_TOTAL_BONUS' => array(
      'GROUP' => 'ORDER_FORM',
      'TITLE' => GetMessage("logictim.balls_ORDER_TOTAL_BONUS"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '7',
   ),
   'ORDER_PAY_BONUS_AUTO' => array(
      'GROUP' => 'ORDER_FORM',
      'TITLE' => GetMessage("logictim.balls_ORDER_PAY_BONUS_AUTO"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'Y',
      'SORT' => '8',
   ),
   'BONUS_PROC' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_PROC"),
      'TYPE' => 'INT',
	  'STEP' => '0.01',
	  'DEFAULT' => '10',
      'SORT' => '0',
	  'REFRESH' => 'Y',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_6'
   ),
   'BONUS_MINUS_DISCOUNT_PROD' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_DISCOUNT_PROD"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '2',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_6'
   ),
   'BONUS_FOR_DELIVERY' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_FOR_DELIVERY"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '1',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_6'
   ),
   'BONUS_MINUS_BONUS' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_BONUS"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '3',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_6'
   ),
   'BONUS_FOR_PRODUCT_TYPE' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_FOR_PRODUCT_TYPE"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelBonusProdType,
	  'DEFAULT' => '1',
      'SORT' => '1',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_5'
   ),
   'BONUS_ALL_PRODUCTS' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_MEDOD_5_ALL_PRODUCT"),
      'TYPE' => 'INT',
	  'STEP' => '0.01',
	  'DEFAULT' => '0',
      'SORT' => '2',
	  'REFRESH' => 'Y',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_5'
   ),
   'BONUS_MINUS_DISCOUNT_PROD_METOD_5' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_DISCOUNT_PROD"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '3',
	  'CLASS' => 'BONUS_METOD BONUS_METOD_5'
   ),
   'ORDERS_SUM_RANGE_TYPE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_TYPE"),
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array(0, 1), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_ORDERS_SUM_RANGE_TYPE_NO_USE"),
										GetMessage("logictim.balls_ORDERS_SUM_RANGE_TYPE_PLUS"),
										)
									),
	  'DEFAULT' => '0',
      'SORT' => '0',
	  'CLASS' => 'LGB_PARENT_SELECT'
   ),
   'ORDERS_SUM_RANGE_PERIOD' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_PERIOD"),
      'TYPE' => 'INT',
	  'DEFAULT' => '240',
      'SORT' => '01',
	  'ROW' => 'BEGIN',
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_PERIOD_TYPE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => '',
	  'TITLE_2' => '',
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array('D', 'M'), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_ORDERS_SUM_RANGE_PERIOD_TYPE_D"),
										GetMessage("logictim.balls_ORDERS_SUM_RANGE_PERIOD_TYPE_M"),
										)
									),
	  'DEFAULT' => 'M',
      'SORT' => '02',
	  'ROW' => 'END'
   ),
   'ORDERS_SUM_RANGE_1' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_1"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '1',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_PLACE_1"),
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_1_RATE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '10',
	  'ROW' => 'END',
   ),
   'ORDERS_SUM_RANGE_2' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_2"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '2',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_PLACE_2"),
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_2_RATE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '20',
	  'ROW' => 'END',
   ),
   'ORDERS_SUM_RANGE_3' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_3"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '3',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_PLACE_3"),
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_3_RATE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '30',
	  'ROW' => 'END',
   ),
   'ORDERS_SUM_RANGE_4' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_4"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '4',
	  'ROW' => 'BEGIN',
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_4_RATE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '40',
	  'ROW' => 'END',
   ),
   'ORDERS_SUM_RANGE_5' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_5"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '5',
	  'ROW' => 'BEGIN',
	  'CLASS' => 'ORDERS_SUM_RANGE_TYPE ORDERS_SUM_RANGE_TYPE_1'
   ),
   'ORDERS_SUM_RANGE_5_RATE' => array(
      'GROUP' => 'ORDERS_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_ORDERS_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '50',
	  'ROW' => 'END',
   ),
   
   'CART_SUM_RANGE_TYPE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_TYPE"),
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array(0, 1), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_CART_SUM_RANGE_TYPE_NO_USE"),
										GetMessage("logictim.balls_CART_SUM_RANGE_TYPE_PLUS"),
										)
									),
	  'DEFAULT' => '0',
      'SORT' => '0',
	  'CLASS' => 'LGB_PARENT_SELECT'
   ),
   'CART_SUM_RANGE_1' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_1"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '1',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_CART_SUM_RANGE_PLACE_1"),
	  'CLASS' => 'CART_SUM_RANGE_TYPE CART_SUM_RANGE_TYPE_1'
   ),
   'CART_SUM_RANGE_1_RATE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '10',
	  'ROW' => 'END',
   ),
   'CART_SUM_RANGE_2' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_2"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '2',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_CART_SUM_RANGE_PLACE_2"),
	  'CLASS' => 'CART_SUM_RANGE_TYPE CART_SUM_RANGE_TYPE_1'
   ),
   'CART_SUM_RANGE_2_RATE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '20',
	  'ROW' => 'END',
   ),
   'CART_SUM_RANGE_3' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_3"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '3',
	  'ROW' => 'BEGIN',
	  'PLACE_HOLDER' => GetMessage("logictim.balls_CART_SUM_RANGE_PLACE_3"),
	  'CLASS' => 'CART_SUM_RANGE_TYPE CART_SUM_RANGE_TYPE_1'
   ),
   'CART_SUM_RANGE_3_RATE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '30',
	  'ROW' => 'END',
   ),
   'CART_SUM_RANGE_4' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_4"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '4',
	  'ROW' => 'BEGIN',
	  'CLASS' => 'CART_SUM_RANGE_TYPE CART_SUM_RANGE_TYPE_1'
   ),
   'CART_SUM_RANGE_4_RATE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '40',
	  'ROW' => 'END',
   ),
   'CART_SUM_RANGE_5' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_5"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '',
      'SORT' => '5',
	  'ROW' => 'BEGIN',
	  'CLASS' => 'CART_SUM_RANGE_TYPE CART_SUM_RANGE_TYPE_1'
   ),
   'CART_SUM_RANGE_5_RATE' => array(
      'GROUP' => 'CART_SUM_VARIANTS',
      'TITLE' => GetMessage("logictim.balls_CART_SUM_RANGE_RATE"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '',
      'SORT' => '50',
	  'ROW' => 'END',
   ),
   
   
   'EVENTS_MAIL_DESCRIPTION' => array(
      'GROUP' => 'EVENTS_MAIL',
      'TITLE' => GetMessage("logictim.balls_EVENTS_MAIL_DESCRIPTION_LABEL"),
	  'VALUE' => GetMessage("logictim.balls_EVENTS_MAIL_DESCRIPTION"),
      'TYPE' => 'CUSTOM',
      'SORT' => '1',
	  'NOTES' => ''
   ),
   'COUNT_DAY_WARNING' => array(
      'GROUP' => 'EVENTS_MAIL',
      'TITLE' => GetMessage("logictim.balls_COUNT_DAY_WARNING"),
      'TYPE' => 'STRING',
	  'DEFAULT' => '0',
      'SORT' => '2',
	  'REFRESH' => 'Y',
   ),
   
   'BONUS_REGISTRATION' => array(
      'GROUP' => 'OTHER_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REGISTRATION"),
      'TYPE' => 'STRING',
      'DEFAULT' => 0,
	  'SIZE' => 10,
      'SORT' => '1',
   ),
   'BONUS_BIRTHDAY' => array(
      'GROUP' => 'OTHER_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_BIRTHDAY"),
      'TYPE' => 'STRING',
      'DEFAULT' => 0,
	  'SIZE' => 10,
      'SORT' => '2',
   ),
   'FREE_BONUS_GROUPS' => array(
      'GROUP' => 'FREE_BONUS',
      'TITLE' => GetMessage("logictim.balls_FREE_BONUS_GROUPS"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $userGrups,
	  'DEFAULT' => 2,
      'SORT' => '1',
   ),
   'FREE_BONUS_BONUS' => array(
      'GROUP' => 'FREE_BONUS',
      'TITLE' => GetMessage("logictim.balls_FREE_BONUS_BONUS"),
      'TYPE' => 'STRING',
      'DEFAULT' => 0,
	  'SIZE' => 10,
      'SORT' => '2',
   ),
   'FREE_BONUS_BUTTON' => array(
      'GROUP' => 'FREE_BONUS',
	  'TITLE' => ' ',
      'VALUE' => GetMessage("logictim.balls_FREE_BONUS_GO"),
      'TYPE' => 'AJAX',
      'SORT' => '3',
	  'LINK' => '/bitrix/modules/logictim.balls/classes/module_sale_16/addBonus/addFreeBonus.php'
   ),
   'BONUS_REFERAL' => array(
      'GROUP' => 'REFERAL_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REFERAL"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '1',
   ),
   'SOCIALS_NETWORK' => array(
      'GROUP' => 'REFERAL_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_SOCIALS_NETWORK"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $arSelReferalSocials,
      'SORT' => '2',
   ),
   'BONUS_ALL_REPOST_SAVE' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_ALL_REPOST_SAVE"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '1',
   ),
   'BONUS_REPOST_VK' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_VK"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '2',
   ),
   'BONUS_REPOST_FB' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_FB"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '3',
   ),
   'BONUS_REPOST_TW' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_TW"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '4',
   ),
   'BONUS_REPOST_OK' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_OK"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '5',
   ),
   'BONUS_REPOST_ALL_COUNT' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_WORD"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '10',
      'SORT' => '6',
	  'ROW' => 'BEGIN',
	  'HEADER' => GetMessage("logictim.balls_BONUS_REPOST_ALL_COUNT")
   ),
   'BONUS_REPOST_ALL_TIME' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_FROM_WORD"),
	  'TITLE_2' => GetMessage("logictim.balls_BONUS_MINUTE_WORD"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '60',
      'SORT' => '7',
	  'ROW' => 'END'
   ),
   'BONUS_REPOST_PAGE_COUNT' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_WORD"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '10',
      'SORT' => '8',
	  'ROW' => 'BEGIN',
	  'HEADER' => GetMessage("logictim.balls_BONUS_REPOST_PAGE_COUNT"),
   ),
   'BONUS_REPOST_PAGE_TIME' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_FROM_WORD"),
	  'TITLE_2' => GetMessage("logictim.balls_BONUS_MINUTE_WORD"),
      'TYPE' => 'STRING',
	  'SIZE' => 5,
	  'DEFAULT' => '60',
      'SORT' => '9',
	  'ROW' => 'END'
   ),
   'VK_APP_ID' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_VK_APP_ID"),
      'TYPE' => 'STRING',
	  'SIZE' => 20,
	  'DEFAULT' => '',
      'SORT' => '94',
   ),
   'FB_APP_ID' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_FACEBOOK_APP_ID"),
      'TYPE' => 'STRING',
	  'SIZE' => 20,
	  'DEFAULT' => '',
      'SORT' => '95',
   ),
   'REVIEW__DESCRIPTION' => array(
      'GROUP' => 'REVIEWS_BONUS',
      'TYPE' => 'CUSTOM',
      'SORT' => '0',
	  'NOTES' => GetMessage("logictim.balls_BONUS_REVIEW_COMMENT"),
   ),
   'REVIEW_BLOG' => array(
      'GROUP' => 'REVIEWS_BONUS',
      'TITLE' => GetMessage("logictim.balls_REVIEW_BLOG"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $arBlogs,
      'SORT' => '1',
   ),
   'REVIEW_IBLOCK' => array(
      'GROUP' => 'REVIEWS_BONUS',
      'TITLE' => GetMessage("logictim.balls_REVIEW_IBLOCK"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $arIblocks,
      'SORT' => '2',
   ),
   'REVIEW_FORUM' => array(
      'GROUP' => 'REVIEWS_BONUS',
      'TITLE' => GetMessage("logictim.balls_REVIEW_FORUM"),
      'TYPE' => 'MSELECT',
	  'VALUES' => $arForums,
      'SORT' => '3',
   ),
   'BONUS_REVIEW' => array(
      'GROUP' => 'REVIEWS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REVIEW"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '4',
   ),
   
   'REFERAL_SYSTEM_TYPE' => array(
      'GROUP' => 'REFERAL_SYSTEM',
      'TITLE' => GetMessage("logictim.balls_REFERAL_SYSTEM_TYPE"),
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array(0, 1, 2, 3), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_REFERAL_SYSTEM_TYPE_NO_USE"),
										GetMessage("logictim.balls_REFERAL_SYSTEM_TYPE_FIX_ORDER"),
										GetMessage("logictim.balls_REFERAL_SYSTEM_TYPE_FIX_CART"),
										GetMessage("logictim.balls_REFERAL_SYSTEM_TYPE_MODULE"),
										)
									),
	  'DEFAULT' => '0',
      'SORT' => '0',
	  'CLASS' => 'LGB_PARENT_SELECT'
   ),
   'REFERAL_SYSTEM_BONUS' => array(
      'GROUP' => 'REFERAL_SYSTEM',
      'TITLE' => GetMessage("logictim.balls_REFERAL_SYSTEM_BONUS"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '1',
	  'REFRESH' => 'Y',
	  'CLASS' => 'REFERAL_SYSTEM_TYPE REFERAL_SYSTEM_TYPE_1 REFERAL_SYSTEM_TYPE_2 REFERAL_SYSTEM_TYPE_3'
   ),
   'REFERAL_USE_COUPONS' => array(
      'GROUP' => 'REFERAL_SYSTEM',
      'TITLE' => GetMessage("logictim.balls_REFERAL_USE_COUPONS"),
      'TYPE' => 'SELECT',
	  'VALUES' => array(
					'REFERENCE_ID' => array('Y', 'N'), 
					'REFERENCE' => array(
										GetMessage("logictim.balls_REFERAL_USE_COUPONS_Y"),
										GetMessage("logictim.balls_REFERAL_USE_COUPONS_N"),
										)
									),
	  'DEFAULT' => 'N',
      'SORT' => '2',
	  'CLASS' => 'LGB_PARENT_SELECT REFERAL_SYSTEM_TYPE REFERAL_SYSTEM_TYPE_1 REFERAL_SYSTEM_TYPE_2 REFERAL_SYSTEM_TYPE_3'
   ),
   'REFERAL_COUPON_DISCOUNT' => array(
      'GROUP' => 'REFERAL_SYSTEM',
      'TITLE' => GetMessage("logictim.referals_REFERAL_COUPON_DISCOUNT"),
      'TYPE' => 'SELECT',
      'VALUES' => $arDiscounts,
	  'DEFAULT' => '0',
      'SORT' => '3',
	  'CLASS' => 'REFERAL_USE_COUPONS REFERAL_USE_COUPONS_Y REFERAL_SYSTEM_TYPE REFERAL_SYSTEM_TYPE_1 REFERAL_SYSTEM_TYPE_2 REFERAL_SYSTEM_TYPE_3'
   ),
   'REFERAL_COUPON_PREFIX' => array(
      'GROUP' => 'REFERAL_SYSTEM',
      'TITLE' => GetMessage("logictim.balls_REFERAL_COUPON_PREFIX"),
      'TYPE' => 'STRING',
	  'DEFAULT' => 'PARTNER_#USER_ID#',
      'SORT' => '4',
	  'CLASS' => 'REFERAL_USE_COUPONS REFERAL_USE_COUPONS_Y REFERAL_SYSTEM_TYPE REFERAL_SYSTEM_TYPE_1 REFERAL_SYSTEM_TYPE_2 REFERAL_SYSTEM_TYPE_3',
	  'NOTES' => GetMessage("logictim.balls_REFERAL_COUPON_PREFIX_NOTES"),
   ),
   
   'TEXT_BONUS_BALLS' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
      'DEFAULT' => GetMessage("logictim.balls_TEXT_BONUS_BALLS"),
	  'SIZE' => 50,
      'SORT' => '1',
   ),
   'HAVE_BONUS_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_HAVE_BONUS_TEXT"),
      'SORT' => '2',
   ),
   'CAN_BONUS_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_CAN_BONUS_TEXT"),
      'SORT' => '3',
   ),
   'MIN_BONUS_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
	  'SIZE' => 50,
      'TYPE' => 'STRING',
      'DEFAULT' => GetMessage("logictim.balls_MIN_BONUS_TEXT"),
      'SORT' => '4',
   ),
   'MAX_BONUS_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
	  'SIZE' => 50,
      'TYPE' => 'STRING',
      'DEFAULT' => GetMessage("logictim.balls_MAX_BONUS_TEXT"),
      'SORT' => '5',
   ),
   'PAY_BONUS_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_PAY_BONUS_TEXT"),
      'SORT' => '6',
   ),
   'TEXT_BONUS_PAY' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_TEXT_BONUS_PAY"),
      'SORT' => '7',
   ),
   'ERROR_1_TEXT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_ERROR_1_TEXT"),
      'SORT' => '8',
   ),
   'TEXT_BONUS_FOR_ITEM' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_TEXT_BONUS_FOR_ITEM"),
      'SORT' => '9',
   ),
   'TEXT_BONUS_FOR_PAYMENT' => array(
      'GROUP' => 'TEXT',
      'TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT_TEXT"),
      'TYPE' => 'STRING',
	  'SIZE' => 50,
      'DEFAULT' => GetMessage("logictim.balls_TEXT_BONUS_FOR_PAYMENT"),
      'SORT' => '10',
   ),
   
   
   
);
?>