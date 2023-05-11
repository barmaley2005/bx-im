<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();



$arComponentParameters = array(
"GROUPS" => array(
	"DATA" => array(
         "NAME" => GetMessage("LOGICTIM_BONUS_DATA"),
      ),
   ),
	"PARAMETERS" => array(
		"OPERATIONS_WAIT" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_OPERATIONS_WAIT"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "Y"
			),
		"FIELDS" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_SEE_FIELDS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"SIZE" => 7,
			"VALUES" => array(
							"ID" => GetMessage("LOGICTIM_BONUS_PROPERTY_ID"),
							"DATE" => GetMessage("LOGICTIM_BONUS_DATE"),
							"NAME" => GetMessage("LOGICTIM_BONUS_PROPERTY_NAME"),
							"OPERATION_SUM" => GetMessage("LOGICTIM_BONUS_PROPERTY_OPERATION_SUM"),
							"BALLANCE_BEFORE" => GetMessage("LOGICTIM_BONUS_BALLANCE_BEFORE"),
							"BALLANCE_AFTER" => GetMessage("LOGICTIM_BONUS_BALLANCE_AFTER"),
							"ADD_DETAIL" => GetMessage("LOGICTIM_BONUS_ADD_DETAIL"),
						),
			"DEFAULT" => array("ID", "DATE", "NAME", "OPERATION_SUM", "BALLANCE_BEFORE", "BALLANCE_AFTER")
			),
		"SORT" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_SORT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"DEFAULT" => "DESC",
			"VALUES" => array(
								"ASC" => GetMessage("LOGICTIM_BONUS_SORT_ASC"),
								"DESC" => GetMessage("LOGICTIM_BONUS_SORT_DESC")
							)
			),
		"ORDER_LINK" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_ORDER_LINK"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => ""
			),
		"ORDER_URL" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_ORDER_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/"
			),
		"PAGE_NAVIG_LIST" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_PAGE_NAVIG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "30"
			),
		"PAGE_NAVIG_TEMP" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_PAGE_NAVIG_TEMP"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "arrows"
			),
			
	),
);
?>
