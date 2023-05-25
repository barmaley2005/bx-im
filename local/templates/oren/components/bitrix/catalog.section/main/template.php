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
<div class="catalog-content__box" data-ajax-items>
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

<?if ($arParams['DISPLAY_BOTTOM_PAGER']):?>
    <?=$arResult['NAV_STRING']?>
<?endif?>

<?

if ($arResult['DESCRIPTION'])
{
    $this->SetViewTarget('CATALOG_DESCRIPTION');

    ?>
    <section class="section election">
        <div class="container">
            <h3 class="election-title"><?=$arResult['UF_CATALOG_TITLE']?></h3>

            <div class="election-container">
                <div class="election-box">
                    <?=$arResult['DESCRIPTION']?>
                </div>
            </div>

            <p class="election-more"><?=GetMessage('CATALOG_SECTION_READ_MORE')?></p>

        </div>
    </section>
    <?

    $this->EndViewTarget();
}

?>
