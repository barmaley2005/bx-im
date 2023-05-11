<?
IncludeModuleLangFile(__FILE__);
//add prop Bonus ball for product
$catalogsId = array();

if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog"))
{
	//take id of catalogs, and offers
	$dbCatalogs = CIBlock::GetList(
		Array(), 
		Array(
			'ACTIVE'=>'Y', 
		), false
	);
	//take only torgoviy catalog
	while($arCatalog = $dbCatalogs->Fetch())
	{
		$catDb = CCatalog::GetByID($arCatalog["ID"]);
		if($catDb) {
			$catalogsId[] = $catDb["ID"];
			}
	}
	
	if($catalogsId)
	{
		foreach($catalogsId as $infoblok):
			$arFields = array(
						  "NAME" => GetMessage("logictim.balls_CATALOG_PROP_BONUS"),
						  "ACTIVE" => "Y",
						  "SORT" => "100",
						  "CODE" => "LOGICTIM_BONUS_BALLS",
						  "PROPERTY_TYPE" => "N",
						  "IBLOCK_ID" => $infoblok,
						  "DEFAULT_VALUE" => 0,
						  "WITH_DESCRIPTION" => "N",
						);
			$iblockproperty = new CIBlockProperty;
  			$PropertyID = $iblockproperty->Add($arFields);
			
			$arFields = array(
						  "NAME" => GetMessage("logictim.balls_BONUS_NO"),
						  "ACTIVE" => "Y",
						  "SORT" => "101",
						  "CODE" => "LOGICTIM_BONUS_NO",
						  "PROPERTY_TYPE" => "L",
						  "LIST_TYPE" => "C",
						  "IBLOCK_ID" => $infoblok,
						  "WITH_DESCRIPTION" => "N",
						  "VALUES" => array("0" => array("XML_ID" => "Y", "VALUE" => "Y", "DEF" => "N", "SORT" => "100"))
						);
			$iblockproperty = new CIBlockProperty;
  			$PropertyID = $iblockproperty->Add($arFields);
			
			$arFields = array(
						  "NAME" => GetMessage("logictim.balls_BONUS_NO_PAY"),
						  "ACTIVE" => "Y",
						  "SORT" => "102",
						  "CODE" => "LOGICTIM_BONUS_NO_PAY",
						  "PROPERTY_TYPE" => "L",
						  "LIST_TYPE" => "C",
						  "IBLOCK_ID" => $infoblok,
						  "WITH_DESCRIPTION" => "N",
						  "VALUES" => array("0" => array("XML_ID" => "Y", "VALUE" => "Y", "DEF" => "N", "SORT" => "100"))
						);
			$iblockproperty = new CIBlockProperty;
  			$PropertyID = $iblockproperty->Add($arFields);
			
			//Add props for categories
			$arFields = Array(
				"ENTITY_ID" => "IBLOCK_".$infoblok."_SECTION",
				"FIELD_NAME" => "UF_LOGICTIM_BONUS",
				"USER_TYPE_ID" => "double",
				"EDIT_FORM_LABEL" => Array("ru"=>GetMessage("logictim.balls_BONUS_CAT"))
				);
			$obUserField  = new CUserTypeEntity;
			$obUserField->Add($arFields);
			
			$arFields = Array(
				"ENTITY_ID" => "IBLOCK_".$infoblok."_SECTION",
				"FIELD_NAME" => "UF_LOGICTIM_BONUS_NO",
				"USER_TYPE_ID" => "boolean",
				"EDIT_FORM_LABEL" => Array("ru"=>GetMessage("logictim.balls_BONUS_NO"))
				);
			$obUserField  = new CUserTypeEntity;
			$obUserField->Add($arFields);
			
			$arFields = Array(
				"ENTITY_ID" => "IBLOCK_".$infoblok."_SECTION",
				"FIELD_NAME" => "UF_LOGICTIM_BONUS_NP",
				"USER_TYPE_ID" => "boolean",
				"EDIT_FORM_LABEL" => Array("ru"=>GetMessage("logictim.balls_BONUS_NO_PAY"))
				);
			$obUserField  = new CUserTypeEntity;
			$obUserField->Add($arFields);
			
		endforeach;
	}

}
?>