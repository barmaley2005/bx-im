<?
$module_id = 'logictim.balls';

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/include.php');
IncludeModuleLangFile(__FILE__);

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

//select  all user groups
$userGrups = array();
$rsGroups = CGroup::GetList(($by="id"), ($order="asc"), array("ACTIVE"  => "Y"));
while($arUserGroups = $rsGroups->Fetch()) {
	$userGrups["REFERENCE_ID"][] = $arUserGroups["ID"];
	$userGrups["REFERENCE"][] = $arUserGroups["NAME"];
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
);

$arGroups = array(
   'MAIN' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MAIN"), 'TAB' => 0),
   'EVENTS_ORDER_TO_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_EVENTS_ORDER"), 'TAB' => 0),
   'MEDOD_5_PROPERTY' => array('TITLE' => GetMessage("logictim.balls_MEDOD_5_PROPERTY"), 'TAB' => 1),
   'MEDOD_3_4_PROPERTY' => array('TITLE' => GetMessage("logictim.balls_MEDOD_3_4_PROPERTY"), 'TAB' => 1),
   'MIN_PAYMENT_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MIN_PAYMENT_BONUS"), 'TAB' => 0),
   'MAX_PAYMENT_BONUS' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_MAX_PAYMENT_BONUS"), 'TAB' => 0),
   'ORDER_FORM' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_ORDER_FORM"), 'TAB' => 0),
   'TEXT' => array('TITLE' => GetMessage("logictim.balls_OPTIONS_TEXT"), 'TAB' => 0),
   'OTHER_BONUS' => array('TITLE' => GetMessage("logictim.balls_TAB_4"), 'TAB' => 2),
   'SOCIALS_BONUS' => array('TITLE' => '?????? ?? ???????', 'TAB' => 2),
   'SEND_MAIL' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_GROUP"), 'TAB' => 3),
   'SEND_MAIL_REGISTER' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_REGISTER_GROUP"), 'TAB' => 3),
   'SEND_MAIL_BIRTHDAY' => array('TITLE' => GetMessage("logictim.balls_SEND_MAIL_BIRTHDAY_GROUP"), 'TAB' => 3),
   'FREE_BONUS' => array('TITLE' => GetMessage("logictim.balls_FREE_BONUS"), 'TAB' => 2)
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
   'BONUS_METOD' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_SELECT_METOD_LABEL"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSel,
	  'DEFAULT' => '5',
      'SORT' => '2'
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
   'BONUS_ROUND' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_BONUS_ROUND"),
      'TYPE' => 'INT',
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
   'LIVE_BONUS' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_LIVE_BONUS"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '7',
   ),
   'LIVE_BONUS_TIME' => array(
      'GROUP' => 'MAIN',
      'TITLE' => GetMessage("logictim.balls_LIVE_BONUS_TIME"),
      'TYPE' => 'INT',
	  'DEFAULT' => '365',
      'SORT' => '8',
	  'REFRESH' => 'Y',
   ),
   'LIVE_BONUS_ALL' => array(
      'GROUP' => 'MAIN',
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
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '10',
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
	  'DEFAULT' => '10',
      'SORT' => '0',
	  'REFRESH' => 'Y',
   ),
   'BONUS_MINUS_DISCOUNT_PROD' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_DISCOUNT_PROD"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '2',
   ),
   'BONUS_FOR_DELIVERY' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_FOR_DELIVERY"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '1',
   ),
   'BONUS_MINUS_BONUS' => array(
      'GROUP' => 'MEDOD_3_4_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_BONUS"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '3',
   ),
   'BONUS_FOR_PRODUCT_TYPE' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_FOR_PRODUCT_TYPE"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelBonusProdType,
	  'DEFAULT' => '1',
      'SORT' => '1'
   ),
   'BONUS_ALL_PRODUCTS' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_MEDOD_5_ALL_PRODUCT"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '2',
	  'REFRESH' => 'Y',
   ),
   'BONUS_MINUS_DISCOUNT_PROD_METOD_5' => array(
      'GROUP' => 'MEDOD_5_PROPERTY',
      'TITLE' => GetMessage("logictim.balls_BONUS_MINUS_DISCOUNT_PROD"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '3',
   ),
   'SEND_MAIL' => array(
      'GROUP' => 'SEND_MAIL',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_CHECK"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '1',
   ),
   'SEND_MAIL_SUBJECT' => array(
      'GROUP' => 'SEND_MAIL',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT"),
      'TYPE' => 'STRING',
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT_DEFAULT"),
      'SORT' => '2',
   ),
   'SEND_MAIL_FROM' => array(
      'GROUP' => 'SEND_MAIL',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FROM"),
      'TYPE' => 'STRING',
	  'DEFAULT' => COption::GetOptionString("main", "email_from", ''),
      'SORT' => '3',
   ),
   'SEND_MAIL_FORMAT' => array(
      'GROUP' => 'SEND_MAIL',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FORMAT"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelMailFormat,
	  'DEFAULT' => '1',
      'SORT' => '4'
   ),
   'SEND_MAIL_TEXTAREA' => array(
      'GROUP' => 'SEND_MAIL',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA"),
      'TYPE' => 'TEXT',
	  'COLS' => 70,
	  'ROWS' => 15,
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_DEFAULT"),
      'SORT' => '5',
	  'NOTES' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_NOTES")
   ),
   
   
   'SEND_MAIL_REGISTER' => array(
      'GROUP' => 'SEND_MAIL_REGISTER',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_CHECK"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '0',
   ),
   'SEND_MAIL_SUBJECT_REGISTER' => array(
      'GROUP' => 'SEND_MAIL_REGISTER',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT"),
      'TYPE' => 'STRING',
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT_REGISTER_DEFAULT"),
      'SORT' => '6',
	  'SIZE' => 50,
   ),
   'SEND_MAIL_FROM_REGISTER' => array(
      'GROUP' => 'SEND_MAIL_REGISTER',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FROM"),
      'TYPE' => 'STRING',
	  'DEFAULT' => COption::GetOptionString("main", "email_from", ''),
      'SORT' => '7',
   ),
   'SEND_MAIL_FORMAT_REGISTER' => array(
      'GROUP' => 'SEND_MAIL_REGISTER',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FORMAT"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelMailFormat,
	  'DEFAULT' => '1',
      'SORT' => '8'
   ),
   'SEND_MAIL_TEXTAREA_REGISTER' => array(
      'GROUP' => 'SEND_MAIL_REGISTER',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA"),
      'TYPE' => 'TEXT',
	  'COLS' => 70,
	  'ROWS' => 15,
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_REGISTER_DEFAULT"),
      'SORT' => '9',
	  'NOTES' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_REGISTER_NOTES")
   ),
   
   //
   'SEND_MAIL_BIRTHDAY' => array(
      'GROUP' => 'SEND_MAIL_BIRTHDAY',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_CHECK"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '0',
   ),
   'SEND_MAIL_SUBJECT_BIRTHDAY' => array(
      'GROUP' => 'SEND_MAIL_BIRTHDAY',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT"),
      'TYPE' => 'STRING',
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_SUBJECT_BIRTHDAY_DEFAULT"),
      'SORT' => '6',
	  'SIZE' => 50,
   ),
   'SEND_MAIL_FROM_BIRTHDAY' => array(
      'GROUP' => 'SEND_MAIL_BIRTHDAY',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FROM"),
      'TYPE' => 'STRING',
	  'DEFAULT' => COption::GetOptionString("main", "email_from", ''),
      'SORT' => '7',
   ),
   'SEND_MAIL_FORMAT_BIRTHDAY' => array(
      'GROUP' => 'SEND_MAIL_BIRTHDAY',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_FORMAT"),
      'TYPE' => 'SELECT',
      'VALUES' => $arSelMailFormat,
	  'DEFAULT' => '1',
      'SORT' => '8'
   ),
   'SEND_MAIL_TEXTAREA_BIRTHDAY' => array(
      'GROUP' => 'SEND_MAIL_BIRTHDAY',
      'TITLE' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA"),
      'TYPE' => 'TEXT',
	  'COLS' => 70,
	  'ROWS' => 15,
	  'DEFAULT' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_BIRTHDAY_DEFAULT"),
      'SORT' => '9',
	  'NOTES' => GetMessage("logictim.balls_SEND_MAIL_TEXTAREA_BIRTHDAY_NOTES")
   ),
   //
   
   
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
   'BONUS_REPOST_CHECK' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_CHECK"),
      'TYPE' => 'CHECKBOX',
	  'DEFAULT' => 'N',
      'SORT' => '1',
   ),
   'BONUS_REPOST_TIME' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_TIME"),
      'TYPE' => 'INT',
	  'DEFAULT' => '4',
      'SORT' => '2',
   ),
   'BONUS_REPOST_VK' => array(
      'GROUP' => 'SOCIALS_BONUS',
      'TITLE' => GetMessage("logictim.balls_BONUS_REPOST_VK"),
      'TYPE' => 'INT',
	  'DEFAULT' => '0',
      'SORT' => '3',
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


$opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();

?>