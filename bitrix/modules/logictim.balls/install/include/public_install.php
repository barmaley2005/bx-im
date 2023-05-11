<?
IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("main");

$content = '<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("'.GetMessage("logictim.balls_BONUS_PAGE_TITLE").'");
?><?$APPLICATION->IncludeComponent(
	"logictim:bonus.history",
	"",
	Array(
		"FIELDS" => array("ID","DATE","NAME","OPERATION_SUM","BALLANCE_BEFORE","BALLANCE_AFTER"),
		"ORDER_LINK" => "N",
		"OPERATIONS_WAIT" => "Y",
		"ORDER_URL" => "/personal/order/",
		"PAGE_NAVIG_LIST" => "30",
		"PAGE_NAVIG_TEMP" => "arrows",
		"SORT" => "DESC"
	)
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>';
\Bitrix\Main\IO\File::putFileContents($_SERVER['DOCUMENT_ROOT']."/personal_bonus/index.php", $content);

?>