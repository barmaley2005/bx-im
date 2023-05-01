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

if (empty($arResult['ITEMS']))
    return;

?>
<h3 class="article-sidebar__title"><?=GetMessage('BLOG_SIDEBAR_TITLE')?></h3>

<div class="article-sidebar__box">
    <?
    foreach ($arResult['ITEMS'] as $arItem)
    {
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <div class="article-sidebar__row" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="article-sidebar__img">
                <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
            </a>
            <div class="article-sidebar__text">
                <p class="article-sidebar__date"><?=$arItem['DISPLAY_ACTIVE_FROM']?></p>
                <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="article-sidebar__link"><?=$arItem['NAME']?></a>
            </div>
        </div>
        <?
    }
    ?>
</div>
