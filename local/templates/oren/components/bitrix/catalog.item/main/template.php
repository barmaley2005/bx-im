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

//echo '<pre>';print_r($arResult);echo '</pre>';

$arItem = $arResult['ITEM'];

$arLabels = array();

if ($arItem['PROPERTIES']['NEWPRODUCT']['VALUE'])
{
    $arLabels[] = array('TEXT'=>'new');
}

if (is_array($arItem['OFFERS']) && !empty($arItem['OFFERS']))
{
    $firstOffer = reset($arItem['OFFERS']);

    $arPrice = $firstOffer['ITEM_PRICES'][$firstOffer['ITEM_PRICE_SELECTED']];
} else {
    $arPrice = $arItem['ITEM_PRICES'][$arItem['ITEM_PRICE_SELECTED']];
}

if ($arPrice['PERCENT']>0)
{
    $arLabels[] = array('TEXT'=>$arPrice['PERCENT'].'%');
}

?>
<div class="bestseller-box<?if ($arPrice['DISCOUNT']>0):?> _discount<?endif?>" id="<?=$arResult['AREA_ID']?>">
    <?
    if ($arParams['LOOK_ID'])
    {
        ?>
        <div id="anchor-<?=$arItem['ID']?>" class="anchor-block"></div>
        <?
    }
    ?>
    <div class="bestseller-head">
        <?if ($arParams['LOOK_ID']):?>
            <div class="look-description__point" data-id="<?=$arItem['ID']?>">
                <span><?=$arParams['LOOK_ID']?></span>
            </div>
        <?endif?>
        <div class="bestseller-head__left">
            <?
            foreach ($arLabels as $arLabel)
            {
                ?>
                <div class="bestseller-head__info">
                    <p><?=$arLabel['TEXT']?></p>
                </div>
                <?
            }
            ?>
        </div>
        <div class="bestseller-head__like" data-item-id="<?=$arItem['ID']?>" data-action="favorite">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z"/>
            </svg>
        </div>
    </div>

    <?
    if (is_array($arItem['OFFERS']) && !empty($arItem['OFFERS']))
    {
        ?>
        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="bestseller-link">
            <?

            $arPictures = array();

            foreach ($arItem['OFFERS'] as $arOffer)
            {
                $arPicture = false;
                if (is_array($arOffer['PREVIEW_PICTURE']))
                {
                    $arPicture = $arOffer['PREVIEW_PICTURE'];
                } elseif (is_array($arOffer['DETAIL_PICTURE'])) {
                    $arPicture = $arOffer['DETAIL_PICTURE'];
                }

                if ($arPicture)
                {
                    $arPictures[$arPicture['ID']] = $arPicture;
                }
            }

            if (empty($arPictures))
            {
                if (is_array($arItem['PREVIEW_PICTURE']))
                    $arPictures[] = $arItem['PREVIEW_PICTURE'];
                elseif (is_array($arItem['DETAIL_PICTURE']))
                    $arPictures[] = $arItem['DETAIL_PICTURE'];
            }

            foreach ($arPictures as $arFile)
            {
                ?>
                <img src="<?=$arFile['SRC']?>">
                <?
            }

            ?>
        </a>
        <div class="bestseller-colors">
            <?
            foreach ($arResult['OFFER_COLOR'] as $arColor)
            {
                ?>
                <a href="<?=$arColor['DETAIL_PAGE_URL']?>" class="bestseller-colors_circle">
                    <img src="<?=$arColor['UF_FILE']['SRC']?>" alt="">
                </a>
                <?
            }
            ?>
        </div>
        <?
    } else {
        ?>
    <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="bestseller-link">
        <?
        foreach ($arItem['MORE_PHOTO'] as $arFile)
        {
            ?>
            <img src="<?=$arFile['SRC']?>">
            <?
        }
        ?>
    </a>
        <?
    }
    ?>

    <div class="bestseller-content">
        <div class="bestseller-content__main">
            <h4 class="bestseller-content__title"><?=$arItem['NAME']?></h4>
            <div class="bestseller-content__price">
                <p class="bestseller-content__old">
                    <?=$arPrice['PRINT_BASE_PRICE']?>
                </p>
                <?if ($arPrice['DISCOUNT']>0):?>
                    <p class="bestseller-content__new">
                        <?=$arPrice['PRINT_PRICE']?>
                    </p>
                <?else:?>
                <?endif?>
            </div>
        </div>
        <div class="bestseller-content__button view" data-action="quickView" data-product-id="<?=$arItem['ID']?>">
            <?=GetMessage('ITEM_QUICK_VIEW_TEXT')?>
        </div>
    </div>

    <?if ($arParams['DISABLE_JS'] != 'Y'):?>
        <script>
            $('#<?=$arResult['AREA_ID']?> .bestseller-link').brazzersCarousel();
        </script>
    <?endif?>
</div>
