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

if (empty($arResult['SECTIONS']))
    return;

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));

?>
<section class="section catalog-home">
    <div class="container">
        <h1 class="title"><?=GetMessage('HOME_SECTION_LIST_BLOCK_TITLE')?></h1>

        <div class="catalog-home__container">
            <div class="catalog-home__row">
                <?
                foreach ($arResult['SECTIONS'] as $arSection)
                {
                    $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
                    $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
                    ?>
                    <a href="<?=$arSection['SECTION_PAGE_URL']?>" class="catalog-home__col" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
                        <div class="catalog-home__img">
                            <img src="<?=$arSection['PICTURE']['SRC']?>" loading="lazy" alt="">
                        </div>
                        <div class="catalog-home__description">
                            <span><?=$arSection['NAME']?></span>
                        </div>
                    </a>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</section>
