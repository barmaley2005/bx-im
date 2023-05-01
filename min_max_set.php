<?php
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__));
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

set_time_limit(0);

CModule::IncludeModule("iblock");

$dbRes = CIBlockElement::GetList(array(),array("IBLOCK_ID"=>2),false,false,array("ID"));
while ($arRes = $dbRes->Fetch())
{
	DoIBlockAfterSave(array("ID"=>$arRes["ID"],"IBLOCK_ID"=>2),false);
}

echo 'finished';

?>