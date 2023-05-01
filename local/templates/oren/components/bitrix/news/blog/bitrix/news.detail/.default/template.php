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
<section class="breadcrumbs-section">
    <div class="container">
        <a href="<?=$arResult['SECTION_URL']?>" class="article-back">
            <svg width="31" height="8" viewBox="0 0 31 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M0.646447 3.64645C0.451184 3.84171 0.451184 4.15829 0.646447 4.35355L3.82843 7.53553C4.02369 7.7308 4.34027 7.7308 4.53553 7.53553C4.7308 7.34027 4.7308 7.02369 4.53553 6.82843L1.70711 4L4.53553 1.17157C4.7308 0.976311 4.7308 0.659728 4.53553 0.464466C4.34027 0.269204 4.02369 0.269204 3.82843 0.464466L0.646447 3.64645ZM1 4.5L31 4.5V3.5L1 3.5L1 4.5Z"/>
            </svg>
            <?=GetMessage('BUTTON_BACK_TO_BLOG_TEXT')?>
        </a>
    </div>
</section>

<?

if (is_array($arResult['SECTION']) && is_array($arResult['SECTION']['PATH']) && !empty($arResult['SECTION']['PATH']))
{
    $firstSection = reset($arResult['SECTION']['PATH']);

    $templateData['HASH_TAG'] = $firstSection['NAME'];
}

$templateData['DISPLAY_ACTIVE_FROM'] = $arResult['DISPLAY_ACTIVE_FROM'];
$templateData['NAME'] = $arResult['NAME'];
$templateData['PREVIEW_TEXT'] = $arResult['PREVIEW_TEXT'];
$templateData['DETAIL_TEXT'] = $arResult['DETAIL_TEXT'];
$templateData['DETAIL_PICTURE'] = $arResult['DETAIL_PICTURE'];
$templateData['PROPERTIES'] = $arResult['PROPERTIES'];
?>