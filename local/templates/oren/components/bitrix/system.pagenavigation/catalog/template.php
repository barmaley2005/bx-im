<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);

if ($arResult["NavRecordCount"] == 0 || $arResult["NavPageCount"] == 1 || $arResult["NavPageNomer"] == $arResult["NavPageCount"])
    return;

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

$nextPage = $arResult["sUrlPath"].'?'.$strNavQueryString.'PAGEN_'.$arResult["NavNum"].'='.($arResult['NavPageNomer']+1);

?>
<div class="bestseller-button" data-ajax-navigation>
    <div class="button-box">
        <button class="button" data-ajax-nav-button data-ajax-url="<?=$nextPage?>"><?=GetMessage('NAV_LOAD_MORE_TEXT')?></button>
        <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
             xmlns="http://www.w3.org/2000/svg">
            <path
                d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                stroke-linecap="round" class="button-bg__elem" />
        </svg>
    </div>
</div>