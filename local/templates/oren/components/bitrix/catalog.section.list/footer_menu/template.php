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

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));

?>
<div class="footer-menu order-1">
    <?
    foreach ($arResult['SECTIONS'] as $arSection)
    {
        $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
        $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

        ?>
        <div class="<?=$arSection['UF_CSS_CLASS']?>">
            <div class="menu-head" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
                <p class="menu-head__link"><?=$arSection['NAME']?></p>
                <div class="menu-head__arrow">
                    <div class="footer-arrow">
                        <div class="footer-arrow__icon"></div>
                    </div>
                </div>
            </div>
            <div class="menu-container">
                <ul class="menu-box">
                    <?
                    foreach ($arSection['ITEMS'] as $arItem)
                    {
                        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], $elementEdit);
                        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], $elementDelete, $elementDeleteParams);

                        $class = $arItem['PROPERTIES']['CSS_CLASS']['VALUE'];
                        ?>
                        <li<?if ($class):?> class="<?=$class?>"<?endif?> id="<?=$this->GetEditAreaId($arItem['ID'])?>">
                            <a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>"><?=$arItem['NAME']?></a>
                        </li>
                        <?
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?
    }
    ?>
</div>
