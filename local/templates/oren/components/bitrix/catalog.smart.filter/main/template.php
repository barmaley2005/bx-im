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

global $arCatalogAvailableSort;

$arAvailableSort = is_array($arCatalogAvailableSort) ? $arCatalogAvailableSort : [];
$sortMode = $arParams['~CATALOG_SORT_MODE'];
if (!array_key_exists($sortMode, $arAvailableSort))
    $sortMode = array_key_first($arAvailableSort);

?>

<?
$this->SetViewTarget('CATALOG_MOB_FILTER');
?>

<?
foreach ($arResult['ITEMS'] as $arItem)
{
    if ($arItem['PRICE'] || $arItem['DISPLAY_TYPE'] == 'A' || empty($arItem['VALUES']))
        continue;

    ?>
    <div class="menu-item">
        <div class="menu-head">
            <p>
                <span class="menu-head__link" href=""><?=$arItem['NAME']?></span>
            </p>
            <div class="menu-head__arrow">
                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                </svg>
            </div>
        </div>
        <div class="menu-container">
            <ul class="menu-box">
                <?
                foreach ($arItem['VALUES'] as $arValue)
                {
                    ?>
                    <li>
                        <label class="check">
                            <input class="check__input" type="checkbox" id="mob_<?=$arValue['CONTROL_ID']?>"
                                <?if ($arValue['CHECKED']):?> checked<?endif?>
                            >
                            <span class="check__box"></span>
                            <?=$arValue['VALUE']?>
                        </label>
                    </li>
                    <?
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="menu-item">
        <div class="menu-head">
            <p>
                <span class="menu-head__link" href=""><?=GetMessage('CATALOG_FILTER_SORT_BY')?></span>
            </p>
            <div class="menu-head__arrow">
                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                </svg>
            </div>
        </div>
        <div class="menu-container" data-catalog-sort>
            <ul class="menu-box">
                <?
                foreach ($arAvailableSort as $k=>$value)
                {
                    ?>
                    <li>
                        <label class="radio">
                            <input class="radio__input" type="radio" value="<?=htmlspecialcharsbx($k)?>" name="radio"<?if ($k == $sortMode):?> checked<?endif?>>
                            <span class="radio__box"></span>
                            <?=$value['NAME']?>

                            <?=$value['ICON'] ?? ''?>
                        </label>
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

<?
$this->EndViewTarget();
?>
<div class="catalog-filter">
    <?
    foreach ($arResult['ITEMS'] as $arItem)
    {
        if ($arItem['PRICE'] || $arItem['DISPLAY_TYPE'] == 'A' || empty($arItem['VALUES']))
            continue;

        ?>
        <div class="catalog-filter__item filter">
            <div class="filter-head">
                <p><?=$arItem['NAME']?></p>
                <div class="filter-arrow">
                    <svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 2L4.29289 5.29289C4.68342 5.68342 5.31658 5.68342 5.70711 5.29289L9 2"
                              stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            <div class="filter-container">
                <div class="filter-box">
                    <ul class="menu-box">
                        <?
                        foreach ($arItem['VALUES'] as $arValue)
                        {
                            ?>
                            <li>
                                <label class="check">
                                    <input class="check__input" type="checkbox" id="<?=$arValue['CONTROL_ID']?>"
                                        <?if ($arValue['CHECKED']):?> checked<?endif?>
                                    >
                                    <span class="check__box"></span>
                                    <?=$arValue['VALUE']?>
                                </label>
                            </li>
                            <?
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?
    }
    ?>
    <div class="catalog-filter__item filter">
        <div class="filter-head">
            <p><?=$arAvailableSort[$sortMode]['NAME']?></p>
            <div class="filter-arrow">
                <svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 2L4.29289 5.29289C4.68342 5.68342 5.31658 5.68342 5.70711 5.29289L9 2"
                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>
        <div class="filter-container _radio" data-catalog-sort>
            <div class="filter-box">
                <ul class="menu-box">
                    <?
                    foreach ($arAvailableSort as $k=>$value)
                    {
                        ?>
                        <li>
                            <label class="radio">
                                <input class="radio__input" type="radio" value="<?=htmlspecialcharsbx($k)?>" name="radio"<?if ($k==$sortMode):?> checked<?endif?>>
                                <span class="radio__text"><?=$value['NAME']?></span>

                                <?=$value['ICON'] ?? ''?>
                            </label>
                        </li>
                        <?
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?

$request = \Bitrix\Main\Context::getCurrent()->getRequest();
if ($request->getPost('ajaxCatalog') == 'y')
    return;

$arJSParams = array(
    'formAction' => $arResult["FORM_ACTION"],
    'items' => $arResult['ITEMS'],
);
?>

<script>
    if (typeof catalogFilter === 'undefined')
        catalogFilter = new DevBxCatalogFilter(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);
</script>
