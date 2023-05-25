<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery;

if(!Loader::includeModule("sale"))
    return;


$arAvailableDelivery = array(
    'all' => Loc::getMessage('DEVBX_CMP_PARAM_DELIVERY_CALC_ALL_DELIVERY')
);

$services = Delivery\Services\Manager::getActiveList();

foreach ($services as $srvParams)
{
    if($srvParams["CLASS_NAME"]::canHasProfiles())
        continue;

    if(is_callable($srvParams["CLASS_NAME"]."::canHasChildren") && $srvParams["CLASS_NAME"]::canHasChildren())
        continue;

    $service = Delivery\Services\Manager::getPooledObject($srvParams);

    if(!$service)
        continue;

    $arAvailableDelivery[$service->getId()] = $service->getNameWithParent();
}


$arComponentParameters = array(
    "GROUPS" => array(
    ),
    "PARAMETERS" => array(
        "ACTION_VARIABLE" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_ACTION_VARIABLE"),
            "TYPE" => "STRING",
            "DEFAULT" => "delivery-calc-action",
        ),
        "CALC_DELIVERY" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_CALC_DELIVERY"),
            "TYPE" => "LIST",
            "VALUES" => array(
                "N" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_CALC_DELIVERY_NO"),
                "A" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_CALC_DELIVERY_ALL"),
                "C" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_CALC_DELIVERY_CALCULABLE"),
                "I" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_CALC_DELIVERY_IMMEDIATELY"),
            ),
            "DEFAULT" => "N",
        ),
        "ALLOW_DELIVERY_ID" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_ALLOW_DELIVERY"),
            "TYPE" => "LIST",
            "VALUES" => $arAvailableDelivery,
            "MULTIPLE" => "Y",
            "SIZE" => 10,
            "DEFAULT" => array("all")
        ),
        "PRODUCT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("DEVBX_CMP_PARAM_DELIVERY_CALC_PRODUCT_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}',
        ),
    )
);