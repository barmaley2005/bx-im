<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$asset = \Bitrix\Main\Page\Asset::getInstance();

$asset->addCss(SITE_TEMPLATE_PATH.'/css/style.css');
$asset->addCss(SITE_TEMPLATE_PATH.'/css/custom.css');

$asset->addJs(SITE_TEMPLATE_PATH.'/js/jquery.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/bootstrap.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/swiper.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/jQuery.Brazzers-Carousel.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/jquery.mask.min.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/nouislider.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/main.js');
$asset->addJs(SITE_TEMPLATE_PATH.'/js/oren.js');

\Bitrix\Main\Loader::includeModule('local.lib');

$settings = \Local\Lib\Settings::getInstance();

$curPage = $APPLICATION->GetCurPage(false);
$isHome = $curPage == SITE_DIR;
?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <?$APPLICATION->ShowHead();?>

    <title><?$APPLICATION->ShowTitle();?></title>
</head>

<body>
<?$APPLICATION->ShowPanel();?>
<header class="header">
    <div class="header-desctop<?if (!$isHome):?> _bg<?endif?>">
        <div class="container">
            <div class="header-desctop__container">
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
                <a href="<?=SITE_DIR?>" class="header-desctop__logo">
                    <?=$settings->headerLogo?>
                </a>
                <div class="header-desctop__right">
                    <?$APPLICATION->IncludeComponent(
	"bitrix:search.title", 
	"main", 
	array(
		"CATEGORY_0" => array(
			0 => "iblock_catalog",
			1 => "iblock_offers",
		),
		"CATEGORY_0_TITLE" => "",
		"CHECK_DATES" => "N",
		"CONTAINER_ID" => "title-search",
		"INPUT_ID" => "title-search-input",
		"NUM_CATEGORIES" => "1",
		"ORDER" => "date",
		"PAGE" => "#SITE_DIR#search/index.php",
		"SHOW_INPUT" => "Y",
		"SHOW_OTHERS" => "N",
		"TOP_COUNT" => "5",
		"USE_LANGUAGE_GUESS" => "Y",
		"COMPONENT_TEMPLATE" => "main",
		"CATEGORY_0_iblock_catalog" => array(
			0 => "2",
		),
		"CATEGORY_0_iblock_offers" => array(
			0 => "3",
		)
	),
	false
);?>
                    <div class="header-desctop__link">
                        <a href="<?=SITE_DIR?>personal/favorite/" class="header-desctop__item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10.2199 17.6172C13.6943 17.0584 18.8228 10.4115 18.8228 6.94077C18.8228 4.26438 16.9478 2.35254 14.4111 2.35254C12.2605 2.35254 10.4955 4.08775 9.99931 5.44085C9.50313 4.08775 7.73813 2.35254 5.58755 2.35254C3.05078 2.35254 1.17578 4.26438 1.17578 6.94077C1.17578 10.4115 6.3046 17.0584 9.77872 17.6172C9.85146 17.6329 9.92519 17.6427 9.99931 17.6467C10.0714 17.627 10.1455 17.6171 10.2199 17.6172V17.6172Z"
                                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="header-desctop__count" data-entity="favorite-count">
                                <span></span>
                            </div>
                        </a>
                        <button class="header-desctop__item" data-action="showBasket">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16.6181 18.272H3.38281L3.93428 6.13965H16.0666L16.6181 18.272Z" stroke-miterlimit="10"
                                      stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M7.24219 8.34519V4.48489C7.24219 3.7536 7.53269 3.05225 8.0498 2.53515C8.5669 2.01805 9.26824 1.72754 9.99954 1.72754C10.3616 1.72754 10.7202 1.79886 11.0547 1.93743C11.3893 2.076 11.6932 2.27911 11.9493 2.53515C12.2053 2.79119 12.4084 3.09516 12.547 3.4297C12.6856 3.76424 12.7569 4.12279 12.7569 4.48489V8.34519"
                                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="header-desctop__count" data-entity="basket-ready-count">
                                <span></span>
                            </div>
                        </button>
                        <?if ($USER->IsAuthorized()):?>
                            <a href="/personal/" class="header-desctop__item">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.0722 15.2387C12.3424 14.7148 12.2045 13.8601 12.2045 13.3086V12.9777C12.9468 12.3333 13.4916 11.4921 13.7762 10.5512H13.8038C14.631 10.5512 14.8516 8.8141 14.8516 8.53837C14.8516 8.26263 14.8791 7.24241 14.0244 7.24241C15.7891 2.27917 10.9637 0.293881 7.32399 2.83065C5.83502 2.83065 5.69715 5.03653 6.24862 7.24241C5.39384 7.24241 5.42142 8.29021 5.42142 8.53837C5.42142 8.78653 5.61443 10.5512 6.46921 10.5512C6.74494 11.5439 7.1034 12.4538 7.76517 13.0329V13.3637C7.76517 13.9152 7.90303 14.7424 5.14568 15.2387C2.38833 15.7351 1.72656 18.2718 1.72656 18.2718H18.2707C18.0902 17.51 17.6915 16.8173 17.1234 16.2786C16.5553 15.7399 15.8425 15.3785 15.0722 15.2387V15.2387Z"
                                        stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        <?else:?>
                            <a href="" class="header-desctop__item" data-action="showAuthForm">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M15.0722 15.2387C12.3424 14.7148 12.2045 13.8601 12.2045 13.3086V12.9777C12.9468 12.3333 13.4916 11.4921 13.7762 10.5512H13.8038C14.631 10.5512 14.8516 8.8141 14.8516 8.53837C14.8516 8.26263 14.8791 7.24241 14.0244 7.24241C15.7891 2.27917 10.9637 0.293881 7.32399 2.83065C5.83502 2.83065 5.69715 5.03653 6.24862 7.24241C5.39384 7.24241 5.42142 8.29021 5.42142 8.53837C5.42142 8.78653 5.61443 10.5512 6.46921 10.5512C6.74494 11.5439 7.1034 12.4538 7.76517 13.0329V13.3637C7.76517 13.9152 7.90303 14.7424 5.14568 15.2387C2.38833 15.7351 1.72656 18.2718 1.72656 18.2718H18.2707C18.0902 17.51 17.6915 16.8173 17.1234 16.2786C16.5553 15.7399 15.8425 15.3785 15.0722 15.2387V15.2387Z"
                                        stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        <?endif?>
                    </div>

                    <div class="header-desctop__locale">
                        <label class="check">
                            <input class="check__input" type="checkbox">
                            <span class="check__box">
                  <div class="check-item">
                    <span>Ru</span>
                  </div>
                  <div class="check-item">
                    <span>En</span>
                  </div>
                </span>
                        </label>
                    </div>

                    <!-- Для показа добавить класс  _show -->
                    <div class="search-container" data-entity="quick-search">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-mob">
        <div class="container">
            <div class="header-mob__container">
                <div class="header-mob__button burger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <a href="<?=SITE_DIR?>" class="header-mob__logo">
                    <?=$settings->headerLogo?>
                </a>

                <div class="header-desctop__locale">
                    <label class="check">
                        <input class="check__input" type="checkbox">
                        <span class="check__box">
                <div class="check-item">
                  <span>Ru</span>
                </div>
                <div class="check-item">
                  <span>En</span>
                </div>
              </span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <?$APPLICATION->IncludeComponent(
        "bitrix:menu",
        "header_full",
        Array(
            "ALLOW_MULTI_SELECT" => "N",
            "CHILD_MENU_TYPE" => "left",
            "DELAY" => "N",
            "MAX_LEVEL" => "3",
            "MENU_CACHE_GET_VARS" => array(""),
            "MENU_CACHE_TIME" => "3600",
            "MENU_CACHE_TYPE" => "N",
            "MENU_CACHE_USE_GROUPS" => "Y",
            "ROOT_MENU_TYPE" => "top",
            "USE_EXT" => "Y"
        )
    );?>
</header>

<div class="<?$APPLICATION->ShowProperty('MAIN_BLOCK_CLASS','main-block');?>">
<?
$APPLICATION->ShowViewContent('CONTENT_HEAD');
?>
    <?if (!$isHome):?>
    <?
    $APPLICATION->IncludeComponent("bitrix:breadcrumb", ".default", array(
        "START_FROM" => "0",
        "PATH" => "",
        "SITE_ID" => SITE_ID
    ),
        false,
        array('HIDE_ICONS' => 'Y')
    );
    ?>
    <?endif?>
<?
\Local\Lib\Utils::includePageFile('header.php');
\Local\Lib\Utils::includePageFile('index.php');
?>