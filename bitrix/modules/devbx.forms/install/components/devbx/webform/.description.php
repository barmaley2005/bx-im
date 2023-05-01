<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => GetMessage("DEVBX_FORMS_COMPONENT_WEB_FORM_NAME"),
    "DESCRIPTION" => GetMessage("DEVBX_FORMS_COMPONENT_WEB_FORM_DESCRIPTION"),
    "SORT" => 100,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "devbx",
        "NAME" => GetMessage("DEVBX_COMPONENTS_NAME"),
        "SORT" => 500,
        "CHILD" => array(
            "ID" => "devbx_forms",
            "NAME" => GetMessage("DEVBX_FORMS_COMPONENTS_NAME"),
            "SORT" => 500,
        )
    ),
);