<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$asset = \Bitrix\Main\Page\Asset::getInstance();

$asset->addCss(SITE_TEMPLATE_PATH.'/plugins/swiper/swiper.min.css');
$asset->addCss(SITE_TEMPLATE_PATH.'/plugins/animate/animate.min.css');
$asset->addCss(SITE_TEMPLATE_PATH.'/css/style.css');

$asset->addJs(SITE_TEMPLATE_PATH.'/js/jquery-3.6.0.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/plugins/swiper/swiper.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/plugins/wow/wow.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/plugins/particleground/jquery.particleground.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/script.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/custom.js');

\Bitrix\Main\Loader::includeModule('local.lib');

$settings = \Local\Lib\Settings::getInstance();

$curPage = $APPLICATION->GetCurPage(false);
$isHome = $curPage == SITE_DIR;

$arSites = \Local\Lib\Utils::getSites();

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$scheme = $request->isHttps() ? 'https' : 'http';

?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?$APPLICATION->ShowTitle();?></title>
    <?$APPLICATION->ShowHead()?>
</head>

<body>
<?$APPLICATION->ShowPanel()?>
<!--svg Sprite-->
<svg width="0" height="0" class="hidden" style="display: none">
    <symbol viewBox="0 0 29 14" xmlns="http://www.w3.org/2000/svg" id="sort">
        <path
            d="M6.84673 0.680458L3.0693 4.46549C2.97955 4.55707 2.97878 4.71366 3.05891 4.80194C3.14029 4.89154 3.28955 4.89814 3.37495 4.813L6.77745 1.40502L6.77745 12.7634C6.77745 12.8941 6.87696 13 6.99972 13C7.12248 13 7.22198 12.8941 7.22198 12.7634L7.22198 1.40502L10.6245 4.813C10.7099 4.89814 10.8605 4.89286 10.9405 4.80194C11.0205 4.71102 11.0225 4.56449 10.9301 4.46549L7.15271 0.680458C7.0487 0.590527 6.92081 0.614124 6.84673 0.680458Z" />
        <path fill-rule="evenodd" clip-rule="evenodd"
              d="M7.37186 12.7633C7.37186 12.9679 7.2139 13.15 6.99959 13.15C6.78528 13.15 6.62732 12.9679 6.62732 12.7633L6.62732 1.76754L3.48097 4.91895C3.33229 5.06718 3.08408 5.05289 2.94774 4.90276C2.81357 4.75495 2.81737 4.5081 2.96203 4.36047L2.96299 4.3595L6.74638 0.568501C6.86739 0.460161 7.08064 0.419926 7.25069 0.566968L7.25904 0.574186L11.0397 4.36307C11.1842 4.51794 11.1844 4.75162 11.053 4.90099C10.9163 5.05638 10.6656 5.06589 10.5184 4.91919L7.37186 1.76754L7.37186 12.7633ZM6.99959 12.85C7.0308 12.85 7.07186 12.8202 7.07186 12.7633L7.07186 1.04245L10.7303 4.70674C10.7303 4.70677 10.7302 4.70671 10.7303 4.70674C10.754 4.73024 10.8045 4.72926 10.8278 4.70284C10.856 4.67074 10.8605 4.61228 10.8217 4.56927L7.05105 0.791046C7.03031 0.774359 7.01178 0.770782 6.99652 0.771611C6.97889 0.772571 6.96155 0.779862 6.94897 0.790197L3.1763 4.57044C3.17618 4.57056 3.17642 4.57032 3.1763 4.57044C3.14187 4.60606 3.14385 4.67241 3.16982 4.70107C3.19623 4.73014 3.2468 4.72879 3.26891 4.70674L6.92732 1.04245L6.92732 12.7633C6.92732 12.8202 6.96838 12.85 6.99959 12.85Z" />
        <path
            d="M21.8467 13.3195L18.0693 9.53451C17.9796 9.44293 17.9788 9.28634 18.0589 9.19806C18.1403 9.10846 18.2895 9.10186 18.3749 9.187L21.7774 12.595V1.23663C21.7774 1.10594 21.877 1 21.9997 1C22.1225 1 22.222 1.10594 22.222 1.23663V12.595L25.6245 9.187C25.7099 9.10186 25.8605 9.10714 25.9405 9.19806C26.0205 9.28898 26.0225 9.43551 25.9301 9.53451L22.1527 13.3195C22.0487 13.4095 21.9208 13.3859 21.8467 13.3195Z" />
        <path fill-rule="evenodd" clip-rule="evenodd"
              d="M22.3719 1.23666C22.3719 1.03211 22.2139 0.850037 21.9996 0.850037C21.7853 0.850037 21.6273 1.03211 21.6273 1.23666V12.2325L18.481 9.08105C18.3323 8.93282 18.0841 8.94711 17.9477 9.09724C17.8136 9.24505 17.8174 9.4919 17.962 9.63953L17.963 9.6405L21.7464 13.4315C21.8674 13.5398 22.0806 13.5801 22.2507 13.433L22.259 13.4258L26.0397 9.63693C26.1842 9.48206 26.1844 9.24838 26.053 9.09901C25.9163 8.94362 25.6656 8.93411 25.5184 9.08081L22.3719 12.2325V1.23666ZM21.9996 1.15004C22.0308 1.15004 22.0719 1.17983 22.0719 1.23666V12.9575L25.7303 9.29326C25.7303 9.29323 25.7302 9.29329 25.7303 9.29326C25.754 9.26976 25.8045 9.27074 25.8278 9.29716C25.856 9.32926 25.8605 9.38772 25.8217 9.43073L22.0511 13.209C22.0303 13.2256 22.0118 13.2292 21.9965 13.2284C21.9789 13.2274 21.9615 13.2201 21.949 13.2098L18.1763 9.42956C18.1762 9.42944 18.1764 9.42968 18.1763 9.42956C18.1419 9.39394 18.1439 9.32759 18.1698 9.29893C18.1962 9.26986 18.2468 9.27121 18.2689 9.29326L21.9273 12.9575V1.23666C21.9273 1.17983 21.9684 1.15004 21.9996 1.15004Z" />
    </symbol>
    <symbol viewBox="0 0 26 13" xmlns="http://www.w3.org/2000/svg" id="arrow">
        <path
            d="M25.0705 5.77154L19.3929 0.105159C19.2556 -0.0294609 19.0207 -0.0306206 18.8883 0.0895815C18.7539 0.211648 18.744 0.435549 18.8717 0.563657L23.9836 5.66762L0.354939 5.66762C0.158904 5.66762 0 5.81689 0 6.00103C0 6.18518 0.158904 6.33445 0.354939 6.33445L23.9836 6.33445L18.8717 11.4384C18.744 11.5665 18.7519 11.7925 18.8883 11.9125C19.0246 12.0325 19.2444 12.0355 19.3929 11.8969L25.0705 6.23053C25.2054 6.07452 25.17 5.88268 25.0705 5.77154Z" />
    </symbol>
    <symbol viewBox="0 0 28 18" xmlns="http://www.w3.org/2000/svg" id="arrow-back">
        <path
            d="M9.86353 16.5814L9.14857 17.2964L0.875 9.0232L9.14857 0.75L9.86353 1.46496L2.81581 8.5123H27.125V9.53397H2.81581L9.86353 16.5814Z" />
    </symbol>
    <symbol viewBox="0 0 13 14" xmlns="http://www.w3.org/2000/svg" id="arrow-up">
        <path
            d="M12.7479 0.71966L1.98967 0.71966L1.98967 2.08491L10.3994 2.08491L0.433083 12.0513L1.41606 13.0342L11.3824 3.06789L11.3826 11.4779L12.7479 11.4779L12.7479 0.71966Z" />
    </symbol>
</svg>
<!--svg Sprite-->

<div class="wrapper">
    <header class="header js-header">
        <div class='container'>
            <div class="header__row">
                <div class="header__left">
                    <a href="<?=SITE_DIR?>" class="logo">
                        <img src="<?=$settings->headerLogo['SRC']?>" alt="" />
                        <img src="<?=$settings->headerLogoMob['SRC']?>" alt="" />
                    </a>
                </div>
                <div class="header__center">
                    <?$APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "header",
                        Array(
                            "ALLOW_MULTI_SELECT" => "N",
                            "CHILD_MENU_TYPE" => "",
                            "DELAY" => "N",
                            "MAX_LEVEL" => "1",
                            "MENU_CACHE_GET_VARS" => array(""),
                            "MENU_CACHE_TIME" => "3600",
                            "MENU_CACHE_TYPE" => "N",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "ROOT_MENU_TYPE" => "top",
                            "USE_EXT" => "N"
                        )
                    );?>
                </div>
                <div class="header__right">
                    <div class="header__body">
                        <div class="header__actions">
                            <div class="languages-menu">
                                <?
                                foreach ($arSites as $arSite)
                                {
                                    ?>
                                    <a href="<?=$scheme?>://<?=$arSite['SERVER_NAME'].$arSite['DIR']?>" class="languages-menu__item active">
                                        <div class="languages-menu__img">
                                            <img src="<?=SITE_TEMPLATE_PATH?>/img/<?=$arSite['LANGUAGE_ID']?>.png" alt="#" />
                                        </div>
                                        <div class="languages-menu__text">
                                            <?=ucfirst($arSite['LANGUAGE_ID'])?>
                                        </div>
                                    </a>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                        <div class="header__menu-icon">
                            <div class="icon-menu js-menu__btn">
                                <div class="icon-menu__body">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="main">
        <div class='container'>

            <?if (!$isHome):?>
            <div class="content">
            <?endif?>
<?
\Local\Lib\Utils::includePageFile('header.php');
\Local\Lib\Utils::includePageFile('index.php');
?>