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
<h1 class="buyers-sidebar__title"><?=$arResult['NAME']?></h1>

<ul class="buyers-menu">
    <?
    foreach ($arResult['ITEMS'] as $arItem)
    {
        $uniqId = $arItem['ID'].'_sidebar';
        $this->AddEditAction($uniqId, $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($uniqId, $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <li class="buyers-link" id="<?=$this->GetEditAreaId($uniqId);?>">
            <a href="<?=$arItem['PROPERTIES']['URL']['VALUE']?>">
                <span style="max-width:38px;max-height:38px;display:flex;align-items: center;">
                <?=$arItem['PROPERTIES']['ICON']['~VALUE']?>
                </span>
                <?=$arItem['NAME']?>
            </a>
        </li>
        <?
    }
    ?>
</ul>