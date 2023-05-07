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

$text = $arResult['DETAIL_TEXT'] ?: $arResult['PREVIEW_TEXT'];
if (!$text)
    return;
?>
    <div class="buyers-content__title">
        <a class="buyers-content__back" href="">
            <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 13L1 7L7 1" stroke="#877569" stroke-linecap="round"/>
            </svg>
        </a>
        <h2 class="title"><?=$arResult['NAME']?></h2>
    </div>
<?=$arResult['DETAIL_TEXT'] ?: $arResult['PREVIEW_TEXT']?>