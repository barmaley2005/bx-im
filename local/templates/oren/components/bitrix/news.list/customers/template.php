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
<div class="buyers-main__container">
    <?
    foreach ($arResult['ITEMS'] as $arItem)
    {
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>" class="buyers-main__col" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <?=$arItem['PROPERTIES']['ICON']['~VALUE']?>
            <?=$arItem['NAME']?>
        </a>
        <?
    }
    ?>
</div>
<div class="buyers-mob">
    <ul class="buyers-submenu">
        <?
        foreach ($arResult['ITEMS'] as $arItem)
        {
            ?>
            <li class="buyers-submenu__link">
                <a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>"><?=$arItem['NAME']?></a>
            </li>
            <?
        }
        ?>
    </ul>
</div>
