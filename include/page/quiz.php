<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?
$APPLICATION->IncludeComponent(
	"devbx:quiz", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "2",
		"PROPERTY_WRAP_TYPE" => "QUIZ_WRAP_TYPE",
		"PROPERTY_WRAP_FORM" => "QUIZ_WRAP_FORM",
		"PROPERTY_WRAP_SIZE" => "QUIZ_WRAP_SIZE",
		"PROPERTY_WRAP_COLOR" => "QUIZ_WRAP_COLOR"
	),
	false
);
?>