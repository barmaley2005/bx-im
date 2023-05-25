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

$containerId = $this->GetEditAreaId($arResult['ID']);
?>
<section class="section product" id="<?=$containerId?>">
    <div class="container">
        <div class="product-container">
            <div class="giftCerf-wrap">
                <div id="giftCerf-top-wrap" class="giftCerf-top-wrap">
                    <div class="giftCerf-top giftCerf-top-swiper">
                        <div class="swiper-wrapper">
                            <?
                            foreach ($arResult['MORE_PHOTO'] as $arFile)
                            {
                                ?>
                                <div class="swiper-slide">
                                    <img
                                        src="<?=$arFile['SRC']?>" alt="" loading="lazy">
                                </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="giftCerf-thumbs-wrap">
                    <div class="giftCerf-thumbs-inner">
                        <div class="giftCerf-thumbs giftCerf-thumbs-swiper">
                            <div class="swiper-wrapper" id="img-container">
                                <?
                                foreach ($arResult['MORE_PHOTO'] as $arFile)
                                {
                                    ?>
                                    <div class="swiper-slide">
                                        <img src="<?=$arFile['PREVIEW_SRC']?>" alt="" loading="lazy">
                                    </div>
                                    <?
                                }
                                ?>
                            </div>

                        </div>

                        <div class="giftCerf-thumbs-navigation">
                            <div class="giftCerf-thumbs-next giftCerf-next">
                                <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L8.29289 8.29289C8.68342 8.68342 9.31658 8.68342 9.70711 8.29289L17 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="giftCerf-thumbs-prev giftCerf-prev">
                                <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17 9L9.70711 1.70711C9.31658 1.31658 8.68342 1.31658 8.29289 1.70711L1 9"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-info _giftCerf">
                <div class="product-info__col">
                    <h1 class="product-info__title"><?=$arResult['NAME']?></h1>

                    <?
                    $arSelOffer = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']];
                    ?>

                    <div class="product-box">
                        <?
                        foreach ($arResult['SKU_PROPS'] as $propCode => $skuProp) {
                            $curValue = $arSelOffer['TREE']['PROP_' . $skuProp['ID']];

                            if ($skuProp['USER_TYPE'] == 'directory') {
                                ?>
                                <div class="product-box__item" data-entity="tree_prop"
                                     data-value="<?= $skuProp['ID'] ?>">
                                    <p class="product-box__title"><?= $skuProp['NAME'] ?>:
                                        <span
                                            class="product-box__name"><?= $skuProp['VALUES'][$curValue]['NAME'] ?></span>
                                    </p>

                                    <div class="product-box__check">
                                        <?
                                        foreach ($skuProp['VALUES'] as $value) {
                                            ?>
                                            <div
                                                class="product-box__col" <? if ($value['ID'] <= 0): ?> style="display: none;" <? endif ?>>
                                                <label class="radio">
                                                    <input class="radio__input" type="radio"
                                                           value="<?= $value['ID'] ?>" name="tree_<?= $skuProp['ID'] ?>"
                                                        <? if ($value['ID'] == $curValue): ?> checked<? endif ?>
                                                    >
                                                    <span class="radio__box"><img src="<?= $value['PICT']['SRC'] ?>"
                                                                                  alt=""></span>
                                                </label>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?
                            } else {
                                ?>
                                <div class="product-box__item" data-entity="tree_prop"
                                     data-value="<?= $skuProp['ID'] ?>">
                                    <p class="product-box__title"><?= $skuProp['NAME'] ?>:</p>

                                    <div class="calculation-size">
                                        <?
                                        foreach ($skuProp['VALUES'] as $value) {
                                            ?>
                                            <label
                                                class="radio" <? if ($value['ID'] <= 0): ?> style="display: none;" <? endif ?>>
                                                <input class="radio__input" type="radio"
                                                       value="<?= $value['ID'] ?>" name="tree_<?= $skuProp['ID'] ?>"
                                                    <? if ($value['ID'] == $curValue): ?> checked<? endif ?>
                                                >
                                                <div class="radio__box">
                                                    <p class="radio__text"><?= $value['NAME'] ?></p>
                                                </div>
                                            </label>
                                            <?
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?
                            }
                        }
                        ?>
                    </div>

                    <p class="product-description">
                        <?=$arResult['PREVIEW_TEXT']?>
                    </p>
                </div>

                <div class="product-info__col">
                    <div class="product-box _giftCerf">
                        <div class="product-box__item">
                            <p class="product-box__title"><?=GetMessage('GIFT_CERTIFICATE_PRICE')?></p>

                            <div class="calculation-size">

                                <?
                                $checked = true;
                                foreach ($arResult['AVAILABLE_PRICES'] as $price=>$priceFormatted)
                                {
                                    ?>
                                    <label class="radio">
                                        <input data-entity="nominal" class="radio__input" type="radio" value="<?=$price?>" name="nominal"<?if ($checked):?> checked<?endif?>>
                                        <div class="radio__box">
                                            <p class="radio__text"><?=$priceFormatted?></p>
                                        </div>
                                    </label>
                                    <?
                                    $checked = false;
                                }
                                ?>
                                <div class="product-own d-lg-none">
                                    <input type="text" placeholder="<?=GetMessage('GIFT_CERTIFICATE_CUSTOM_NOMINAL')?>" name="custom_nominal">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="product-button" class="product-button _giftCerf">
                        <div class="product-own d-none d-lg-block">
                            <input type="text" placeholder="<?=GetMessage('GIFT_CERTIFICATE_CUSTOM_NOMINAL')?>" data-entity="custom-nominal">
                        </div>

                        <?
                        $basketProductId = $arSelOffer ? $arSelOffer['ID'] : $arResult['ID'];
                        ?>

                        <button class="product-button__add" data-entity="gift-order" data-product-id="<?= $basketProductId ?>">
                            <?=GetMessage('GIFT_CERTIFICATE_ORDER')?>
                        </button>

                    </div>
                </div>

            </div>
        </div>

        <div class="giftCerf-description">
            <h2 class="giftCerf-description__title"><?=GetMessage('GIFT_CERTIFICATE_DESCRIPTION')?></h2>
            <div class="giftCerf-description__content">
                <?=$arResult['DETAIL_TEXT']?>
            </div>
        </div>

    </div>
</section>

<?
$obName = 'catalogElemenGift'.$arResult['ID'];

$arJSParams = array(
    'CONTAINER_ID' => $containerId,
    'ID' => $arResult['ID'],
    'SITE_ID' => $component->getSiteId(),
    'OFFERS' => array(),
    'SKU_PROPS' => array(),
    'ORDER_PAGE' => SITE_DIR.'gift-certificate/order/',
);

if (!empty($arResult['OFFERS'])) {

    foreach ($arResult['OFFERS'] as $arOffer) {
        $arJSOffer = array_intersect_key($arOffer, array_flip(array('ID', 'NAME', 'DISPLAY_PRICE', 'TREE')));

        $arJSParams['OFFERS'][] = $arJSOffer;
    }

    foreach ($arResult['SKU_PROPS'] as $skuProp) {
        $jsSkuProp = array_intersect_key($skuProp, array_flip(array('ID', 'CODE', 'NAME', 'PROPERTY_TYPE', 'USER_TYPE', 'VALUES')));

        $arJSParams['SKU_PROPS'][] = $jsSkuProp;
    }
}
?>
    <script>
        <?echo $obName?> = new DevBxCatalogElementGift(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);
    </script>
<?