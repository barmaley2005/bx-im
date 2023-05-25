<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформление сертификата");
?>
<?
$APPLICATION->IncludeComponent("devbx:simple", "gift_order", array());
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>