<?
IncludeModuleLangFile(__FILE__);
if (CModule::IncludeModule("sale"))
{
	//Add Order Props and Groups
	$dbPersonTypes = CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
	while ($ptype = $dbPersonTypes->Fetch())
	{
		$PropGroupID = CSaleOrderPropsGroup::Add(array("PERSON_TYPE_ID" => $ptype["ID"], "NAME" => GetMessage("logictim.balls_ORDER_GROUP_NAME"), "SORT" => 100));
		$arFields = array(
		   "PERSON_TYPE_ID" => $ptype["ID"],
		   "NAME" => GetMessage("logictim.balls_ORDER_PROP_PAYMENT_BONUS_NAME"),
		   "TYPE" => "TEXT",
		   "REQUIED" => "N",
		   "DEFAULT_VALUE" => "-",
		   "SORT" => 100,
		   "CODE" => "LOGICTIM_PAYMENT_BONUS",
		   "USER_PROPS" => "N",
		   "IS_LOCATION" => "N",
		   "IS_LOCATION4TAX" => "N",
		   "PROPS_GROUP_ID" => $PropGroupID,
		   "SIZE1" => 0,
		   "SIZE2" => 0,
		   "DESCRIPTION" => "",
		   "IS_EMAIL" => "N",
		   "IS_PROFILE_NAME" => "N",
		   "IS_PAYER" => "N",
		);
		if($info = CModule::CreateModuleObject('sale'))
		{
			$testVersion = '18.6.350';
			$moduleVersion = $info->MODULE_VERSION;
			if(CheckVersion($moduleVersion, $testVersion) == true)
				$arFields["UTIL"] = "Y";
		}
		CSaleOrderProps::Add($arFields);
		$arFields = array(
		   "PERSON_TYPE_ID" => $ptype["ID"],
		   "NAME" => GetMessage("logictim.balls_ORDER_PROP_ADD_BONUS_NAME"),
		   "TYPE" => "TEXT",
		   "REQUIED" => "N",
		   "DEFAULT_VALUE" => "0",
		   "SORT" => 100,
		   "CODE" => "LOGICTIM_ADD_BONUS",
		   "USER_PROPS" => "N",
		   "IS_LOCATION" => "N",
		   "IS_LOCATION4TAX" => "N",
		   "PROPS_GROUP_ID" => $PropGroupID,
		   "SIZE1" => 0,
		   "SIZE2" => 0,
		   "DESCRIPTION" => "",
		   "IS_EMAIL" => "N",
		   "IS_PROFILE_NAME" => "N",
		   "IS_PAYER" => "N",
		);
		if($info = CModule::CreateModuleObject('sale'))
		{
			$testVersion = '18.6.350';
			$moduleVersion = $info->MODULE_VERSION;
			if(CheckVersion($moduleVersion, $testVersion) == true)
				$arFields["UTIL"] = "Y";
		}
		CSaleOrderProps::Add($arFields);
	}
}
?>