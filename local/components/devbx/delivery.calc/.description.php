<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("DEVBX_COMPONENT_DELIVERY_CALC_NAME"),
    "DESCRIPTION" => GetMessage("DEVBX_COMPONENT_DELIVERY_CALC_DESCRIPTION"),
    "SORT" => 500,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "devbx",
        "NAME" => GetMessage("DEVBX_COMPONENTS_NAME"),
        "SORT" => 500,
        "CHILD" => array(
            "ID" => "devbx_forms",
            "NAME" => GetMessage("DEVBX_COMPONENTS_SALE_NAME"),
            "SORT" => 500,
        )
    ),
);