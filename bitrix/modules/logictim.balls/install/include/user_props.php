<?
IncludeModuleLangFile(__FILE__);
if (CModule::IncludeModule("main")):

	$arLangs = array();
	$rsLang = CLanguage::GetList($by="lid", $order="desc", array());
	while ($arLang = $rsLang->Fetch())
	{
		$arLangs[$arLang["LANGUAGE_ID"]] = $arLang;
	}
	
	foreach($arLangs as $lang):
		$arlangBonusCount[$lang["LANGUAGE_ID"]] = 'Bonus count';
		$arlangBonusCountEr[$lang["LANGUAGE_ID"]] = 'An error in completing the user field';
		
		$arlangBonusSubscr[$lang["LANGUAGE_ID"]] = 'To receive alerts on the combustion of bonuses';
		$arlangBonusSubscrEr[$lang["LANGUAGE_ID"]] = 'An error in completing the user field';
	endforeach;
	
	$arlangBonusCount['ru'] = GetMessage("logictim.balls_USER_PROP_BONUS_NAME");
	$arlangBonusCountEr['ru'] = GetMessage("logictim.balls_ORDER_PROP_PAYMENT_BONUS_ERROR");
	$arlangBonusSubscr['ru'] = GetMessage("logictim.balls_USER_PROP_UF_LGB_SUBSCRIBE");
	$arlangBonusSubscrEr['ru'] = GetMessage("logictim.balls_USER_PROP_UF_LGB_SUBSCRIBE_ERROR");

	$oUserTypeEntity    = new CUserTypeEntity();
		$aUserFields    = array(
		'ENTITY_ID'         => 'USER',
		'FIELD_NAME'        => 'UF_LOGICTIM_BONUS',
		'USER_TYPE_ID'      => 'double',
		'XML_ID'            => '',
		'SORT'              => 500,
		'MULTIPLE'          => 'N',
		'MANDATORY'         => 'N',
		'SHOW_FILTER'       => 'I',
		'SHOW_IN_LIST'      => '',
		'EDIT_IN_LIST'      => '',
		'IS_SEARCHABLE'     => 'N',
		'SETTINGS'          => array(
			'DEFAULT_VALUE' => '0',
			'SIZE'          => '20',
			'PRECISION'     => '2',
			'MIN_VALUE'    => '0',
			'MAX_VALUE'    => '0',
		),
			'EDIT_FORM_LABEL'   => $arlangBonusCount,
			'LIST_COLUMN_LABEL' => $arlangBonusCount,
			'LIST_FILTER_LABEL' => $arlangBonusCount,
			'ERROR_MESSAGE'     => $arlangBonusCountEr,
			'HELP_MESSAGE'      => $arlangBonusCount,
		);
	$iUserFieldId   = $oUserTypeEntity->Add( $aUserFields ); // int
	
	
	//SUBSCTIBE
	$oUserTypeEntity    = new CUserTypeEntity();
		$aUserFields    = array(
		'ENTITY_ID'         => 'USER',
		'FIELD_NAME'        => 'UF_LGB_SUBSCRIBE',
		'USER_TYPE_ID'      => 'boolean',
		'XML_ID'            => '',
		'SORT'              => 500,
		'MULTIPLE'          => 'N',
		'MANDATORY'         => 'N',
		'SHOW_FILTER'       => 'I',
		'SHOW_IN_LIST'      => '',
		'EDIT_IN_LIST'      => '',
		'IS_SEARCHABLE'     => 'N',
		'SETTINGS'          => array(
			'DEFAULT_VALUE' => '1',
		),
			'EDIT_FORM_LABEL'   => $arlangBonusSubscr,
			'LIST_COLUMN_LABEL' => $arlangBonusSubscr,
			'LIST_FILTER_LABEL' => $arlangBonusSubscr,
			'ERROR_MESSAGE'     => $arlangBonusSubscrEr,
			'HELP_MESSAGE'      => $arlangBonusSubscr,
		);
	$iUserFieldId   = $oUserTypeEntity->Add( $aUserFields ); // int
	
	
	/*global $USER;
	global $DB, $USER_FIELD_MANAGER;
	$rsUsers = CUser::GetList(($by="id"), ($order="desc"), array());
	while($arItem = $rsUsers->GetNext()) 
	{
		$USER_FIELD_MANAGER->Update("USER", $arItem['ID'], array("UF_LGB_SUBSCRIBE" => 1));
	}*/
	
	
	
endif;
				
				
				
				
				
	
?>