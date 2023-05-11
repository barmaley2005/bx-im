<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");


if(CModule::IncludeModule("iblock")):
	CIBlockType::Delete('LOGICTIM_BONUS_STATISTIC');
endif;
?>