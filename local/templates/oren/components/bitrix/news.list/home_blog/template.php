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
<section class="section blog">
    <div class="container">
        <h2 class="title text-center"><?=GetMessage('BLOG_BLOCK_TITLE')?></h2>

        <div class="blog-container">
            <div class="blog-row">
                <?
                foreach ($arResult['ITEMS'] as $arItem)
                {
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <div class="blog-col" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-img">
                            <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
                        </a>

                        <div class="blog-content">
                            <div class="blog-date">
                                <span><?=$arItem['DISPLAY_ACTIVE_FROM']?></span>
                            </div>
                            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="blog-description">
                                <?=$arItem['NAME']?>
                            </a>
                        </div>
                    </div>
                    <?
                }
                ?>
            </div>
        </div>

        <div class="blog-button">
            <div class="button-box">
                <a href="<?=$arResult['ITEMS'][0]['LIST_PAGE_URL']?>" class="button"><?=GetMessage('BLOG_READ_LINK_TEXT')?></a>
                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                        stroke-linecap="round" class="button-bg__elem" />
                </svg>
            </div>
        </div>
    </div>
</section>
