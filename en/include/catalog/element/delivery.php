<?$APPLICATION->IncludeComponent(
    "devbx:delivery.calc",
    "main",
    Array(
        "ACTION_VARIABLE" => "delivery-calc-action",
        "ALLOW_DELIVERY_ID" => array("all"),
        "CALC_DELIVERY" => "N",
        "PRODUCT_ID" => $arResult['ID']
    )
);?>
