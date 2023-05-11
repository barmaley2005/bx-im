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

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));
?>
<div class="look-description">
    <p class="look-description__title">Товары на фотографии</p>
    <div class="look-description__container">
    <?

    $n=0;

    foreach ($arResult['ITEMS'] as $arItem) {
        $n++;

        $uniqueId = $arItem['ID'] . '_' . md5($this->randString() . $component->getAction());
        $areaId = $this->GetEditAreaId($uniqueId);

        $this->AddEditAction($uniqueId, $arItem['EDIT_LINK'], $elementEdit);
        $this->AddDeleteAction($uniqueId, $arItem['DELETE_LINK'], $elementDelete, $elementDeleteParams);

        ?>

        <?
        $APPLICATION->IncludeComponent(
            'bitrix:catalog.item',
            'main',
            array(
                'RESULT' => array(
                    'ITEM' => $arItem,
                    'AREA_ID' => $areaId,
                ),
                'PARAMS' => $arParams+array('LOOK_ID'=>$n),
            ),
            $component,
            array('HIDE_ICONS' => 'Y')
        );
        ?>

        <?
    }
    ?>
    </div>
</div>
