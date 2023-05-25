<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("DEVBX_OBJECTS_COMPONENT_SIMPLE_NAME"),
    "DESCRIPTION" => GetMessage("DEVBX_OBJECTS_COMPONENT_SIMPLE_DESCRIPTION"),
    "SORT" => 500,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "devbx",
        "NAME" => GetMessage("DEVBX_OBJECTS_COMPONENTS_NAME"),
        "SORT" => 500,
    ),
);