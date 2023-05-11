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
<div class="elected-container<?if (empty($arResult['ITEMS'])):?> _empty<?endif?>">
    <div class="elected-content">
        <div class="elected-content__icon">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M20.432 35.2353C27.3808 34.1178 37.6379 20.824 37.6379 13.8825C37.6379 8.52974 33.8879 4.70605 28.8143 4.70605C24.5132 4.70605 20.9832 8.17649 19.9908 10.8827C18.9985 8.17649 15.4685 4.70605 11.1673 4.70605C6.09375 4.70605 2.34375 8.52974 2.34375 13.8825C2.34375 20.824 12.6014 34.1178 19.5496 35.2353C19.6951 35.2667 19.8426 35.2864 19.9908 35.2943C20.135 35.255 20.2832 35.2352 20.432 35.2353V35.2353Z"
                    stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>

        <p class="elected-content__text">Ваш список избранных товаров пока что пуст</p>

        <div class="elected-content__button">
            <div class="button-box">
                <a href="<?=SITE_DIR?>catalog/" class="button">Открыть каталог</a>
                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                        stroke-linecap="round" class="button-bg__elem" />
                </svg>
            </div>
        </div>
    </div>
    <div class="elected-box">
    <?

    foreach ($arResult['ITEMS'] as $arItem) {
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
                'PARAMS' => $arParams,
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
