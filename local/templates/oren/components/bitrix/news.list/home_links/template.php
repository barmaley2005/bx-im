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
<section class="section links">
    <div class="container">
        <div class="links-container">
            <div class="links-row">
                <?
                foreach ($arResult['ITEMS'] as $arItem)
                {
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>" class="links-col" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                        <div class="links-box">
                            <div class="links-content">
                                <?if (is_array($arItem['PREVIEW_PICTURE'])):?>
                                    <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="">
                                <?else:?>
                                    <?=$arItem['PROPERTIES']['IMAGE_TEXT']['VALUE'] ?: $arItem['NAME']?>
                                <?endif?>
                            </div>
                        </div>
                        <?=$arItem['NAME']?>
                    </a>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</section>
