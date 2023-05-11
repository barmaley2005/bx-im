<? if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();



$arComponentParameters = array(
"GROUPS" => array(
	"DATA" => array(
         "NAME" => GetMessage("LOGICTIM_BONUS_DATA"),
      ),
   ),
	"PARAMETERS" => array(
		"FIELDS" => Array(
			"PARENT" => "DATA",
			"NAME" => GetMessage("LOGICTIM_BONUS_SOCIALS_SELECT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"SIZE" => 7,
			"VALUES" => array(
							"VK" => GetMessage("LOGICTIM_BONUS_SOCIALS_VK"),
							"FB" => GetMessage("LOGICTIM_BONUS_SOCIALS_FB"),
							"OK" => GetMessage("LOGICTIM_BONUS_SOCIALS_OK"),
							"TW" => GetMessage("LOGICTIM_BONUS_SOCIALS_TW"),
						),
			"DEFAULT" => array("VK", "FB", "OK")
			),
	),
);
?>
