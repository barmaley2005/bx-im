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
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
    <?=$arResult["NAV_STRING"]?>
<?endif;?>
<div class="weblog-box">
<?foreach($arResult["ITEMS"] as $arItem):?>
    <?
    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
    ?>
    <div class="blog-col" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
        <?if (is_array($arItem['PREVIEW_PICTURE'])):?>
        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-img">
            <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
        </a>
        <?endif?>

        <div class="blog-content">
            <div class="blog-date">
                <span><?=$arItem['DISPLAY_ACTIVE_FROM']?></span>
            </div>
            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-description">
                <?=$arItem['NAME']?>
            </a>
        </div>
    </div>
<?endforeach;?>
</div>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
    <?=$arResult["NAV_STRING"]?>
<?endif;?>
