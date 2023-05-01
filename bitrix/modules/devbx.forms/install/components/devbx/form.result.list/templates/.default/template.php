<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

//echo '<pre>';print_r($arResult);echo '</pre>';

?>
<table class="table">
    <thead>
    <tr>
        <? foreach ($arResult['COLUMN_NAME'] as $fieldName => $displayName): ?>
            <th><?= $displayName ?></th>
        <? endforeach; ?>
    </tr>
    </thead>

    <tbody>
    <?foreach ($arResult['ITEMS'] as $arItem):?>
        <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK']);
        ?>
    <tr id="<?=$this->GetEditAreaId($arItem['ID']);?>">
        <? foreach ($arResult['COLUMN_NAME'] as $fieldName => $displayName): ?>
        <td><?=$arItem['DISPLAY_FIELDS'][$fieldName]?></td>
        <?endforeach;?>
    </tr>
    <?endforeach;?>
    </tbody>
</table>

<?=$arResult['NAV_STRING']?>