<?php

if (CSite::InDir(SITE_DIR.'personal/cart/') || CSite::InDir(SITE_DIR.'personal/order/'))
    return;

$APPLICATION->SetPageProperty('MAIN_BLOCK_CLASS', 'account-block');
?>
<div class="container account-block__container">
    <aside class="account-block__menu-desktop">
        <?
        $APPLICATION->IncludeComponent('devbx:simple','personal-avatar',array());
        ?>
        <?$APPLICATION->IncludeComponent(
            "bitrix:menu",
            "personal",
            Array(
                "ALLOW_MULTI_SELECT" => "N",
                "CHILD_MENU_TYPE" => "",
                "DELAY" => "N",
                "MAX_LEVEL" => "1",
                "MENU_CACHE_GET_VARS" => array(""),
                "MENU_CACHE_TIME" => "3600",
                "MENU_CACHE_TYPE" => "N",
                "MENU_CACHE_USE_GROUPS" => "Y",
                "ROOT_MENU_TYPE" => "personal",
                "USE_EXT" => "N"
            )
        );?>
        <?if ($USER->IsAuthorized()):?>
        <a href="" class="account-block__logout" data-action="logout">Выход</a>
        <?endif?>
    </aside>
    <div class="<?$APPLICATION->ShowProperty('PERSONAL_CONTENT_CLASS','account-block__content');?>">

