<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформление сертификата");
?><?$APPLICATION->IncludeComponent(
	"devbx:simple", 
	"gift-order", 
	array(
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"COMPONENT_TEMPLATE" => "gift-order",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "22"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>