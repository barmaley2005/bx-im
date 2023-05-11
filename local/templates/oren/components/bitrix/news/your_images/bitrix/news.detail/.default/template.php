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
$arPicture = is_array($arResult['DETAIL_PICTURE']) ? $arResult['DETAIL_PICTURE'] : $arResult['PREVIEW_PICTURE'];

$templateData['PRODUCTS'] = [];
?>
<div class="look-main">
    <div class="look-main__box">
        <div class="look-main__img">

            <img src="<?= $arPicture['SRC'] ?>" alt="">
        </div>

        <?
        $n = 0;
        foreach ($arResult['PRODUCTS'] as $arProduct) {
            $templateData['PRODUCTS'][] = $arProduct['ID'];
            $n++;
            ?>
            <div data-id-look="<?= $arProduct['ID'] ?>" data-anchor="anchor-<?= $n ?>" class="look-point"
                 style="left: <?= $arProduct['X'] ?>%; top: <?= $arProduct['Y'] ?>%;">
                <span><?= $n ?></span>
            </div>
            <?
        }
        ?>
    </div>
    <div class="look-main__content">
        <p class="look-main__name"><?= $arResult['NAME'] ?></p>
        <p class="look-main__description">
            <?= $arResult['DETAIL_TEXT'] ?: $arResult['PREVIEW_TEXT'] ?>
        </p>
    </div>
</div>
