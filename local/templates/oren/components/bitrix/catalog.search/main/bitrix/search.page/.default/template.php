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
?>
<form class="searchMy" action="" method="get">
    <input class="searchMy-input input" type="text" placeholder="Введите свой запрос" name="q" value="<?=$arResult["REQUEST"]["QUERY"]?>">
    <button class="searchMy-button">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.7646 12.9412L16.4705 17.647" stroke="#877569" stroke-miterlimit="10" stroke-linecap="round"
                  stroke-linejoin="round" />
            <path
                d="M8.23489 14.1172C11.4836 14.1172 14.1172 11.4836 14.1172 8.23489C14.1172 4.98616 11.4836 2.35254 8.23489 2.35254C4.98616 2.35254 2.35254 4.98616 2.35254 8.23489C2.35254 11.4836 4.98616 14.1172 8.23489 14.1172Z"
                stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>
</form>
