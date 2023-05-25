<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

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

\Bitrix\Main\Loader::includeModule('devbx.core');
CJSCore::Init('devbx_core_utils');

$containerId = $this->GetEditAreaId('quickBasket');
?>
<div class="modal-dialog">
    <div class="container" id="<?=$containerId?>">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-header__title">
                    <?=GetMessage('BASKET_TITLE')?> <span class="modal-header__count" data-entity="buy-count"><?=$arResult['BUY_COUNT']?></span>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?=GetMessage('MODAL_CLOSE')?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="cart-container">
                    <?
                    //echo '<pre>'; print_r($arResult['BASKET_ITEM_RENDER_DATA']); echo '</pre>';
                    ?>
                    <?
                    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $row)
                    {
                        if ($row['DELAYED'])
                            continue;

                        ?>
                        <div class="goods-item" data-entity="basket-item" data-item-id="<?=$row['ID']?>">
                            <button class="goods-item__close" data-action="removeBasketItem" data-item-id="<?=$row['ID']?>" data-product-id="<?=$row['PARENT_PRODUCT_ID'] ?: $row['PRODUCT_ID']?>">
                                <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.71875 3.71875L13.2812 13.2812" stroke="#C5A994" stroke-linecap="round"
                                          stroke-linejoin="round" />
                                    <path d="M3.71875 13.2812L13.2812 3.71875" stroke="#C5A994" stroke-linecap="round"
                                          stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="goods-item__left">
                                <div class="bestseller-head__like" data-item-id="<?=$row['PARENT_PRODUCT_ID'] ?: $row['PRODUCT_ID']?>" data-action="favorite">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z" />
                                    </svg>
                                </div>
                                <div class="goods-item__img">
                                    <img src="<?=$row['IMAGE_URL']?>" alt="">
                                </div>

                                <div class="goods-item__content">
                                    <a href="<?=$row['DETAIL_PAGE_URL']?>" class="goods-item__article">Арт: <?=$row['PROPS_ALL']['ARTNUMBER']['VALUE']?></a>
                                    <a href="<?=$row['DETAIL_PAGE_URL']?>" class="goods-item__title"><?=$row['NAME']?></a>

                                    <div class="goods-item__box">
                                        <?
                                        foreach ($row['SKU_BLOCK_LIST'] as $arSku)
                                        {
                                            if ($arSku['IS_IMAGE'])
                                            {
                                                foreach ($arSku['SKU_VALUES_LIST'] as $skuValue)
                                                {
                                                    if ($skuValue['SELECTED'])
                                                    {
                                                        ?>
                                                        <div class="goods-item__row">
                                                            <div class="goods-item__color">
                                                                <img src="<?=$skuValue['PICT']?>">
                                                            </div>
                                                            <p class="goods-item__name"><?=$skuValue['NAME']?></p>
                                                        </div>
                                                        <?
                                                    }
                                                }
                                            } else
                                            {
                                                foreach ($arSku['SKU_VALUES_LIST'] as $skuValue)
                                                {
                                                    if ($skuValue['SELECTED'])
                                                    {
                                                        ?>
                                                        <div class="goods-item__row">
                                                            <div class="goods-item__icon">
                                                                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                    <rect x="0.5" y="0.5" width="14" height="14" rx="7" stroke="#D2C7BC" />
                                                                    <g clip-path="url(#clip0_516_18503)">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                                                              d="M4.49959 5.04791V6.375C4.49959 6.58211 4.33179 6.75 4.1248 6.75C3.9178 6.75 3.75 6.58211 3.75 6.375V3.75H6.37358C6.58057 3.75 6.74838 3.91789 6.74838 4.125C6.74838 4.33211 6.58057 4.5 6.37358 4.5H5.01207L6.92147 6.41044C7.06784 6.55689 7.06784 6.79432 6.92147 6.94077C6.77511 7.08722 6.5378 7.08722 6.39143 6.94077L4.49959 5.04791ZM10.5004 9.95233V8.62548C10.5004 8.41841 10.6682 8.25055 10.8752 8.25055C11.0822 8.25055 11.25 8.41841 11.25 8.62548V11.25H8.62642C8.41943 11.25 8.25162 11.0821 8.25162 10.8751C8.25162 10.668 8.41943 10.5001 8.62642 10.5001H9.98793L8.07853 8.59005C7.93216 8.44363 7.93216 8.20623 8.07853 8.05981C8.22489 7.91339 8.4622 7.91339 8.60857 8.05981L10.5004 9.95233Z"
                                                                              fill="#D2C7BC" />
                                                                    </g>
                                                                    <defs>
                                                                        <clipPath id="clip0_516_18503">
                                                                            <rect width="9" height="9" fill="white" transform="translate(3 3)" />
                                                                        </clipPath>
                                                                    </defs>
                                                                </svg>
                                                            </div>
                                                            <p class="goods-item__size"><?=$skuValue['NAME']?></p>
                                                        </div>
                                                        <?
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>

                                <div class="goods-item__footer">
                                    <div class="goods-item__price">
                                        <div class="price-box">
                                            <div class="price-minus" data-action="quantityChange" data-value="-1">
                                                <svg width="6" height="3" viewBox="0 0 6 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M0.2 2.2V0.96H5.44V2.2H0.2Z" />
                                                </svg>
                                            </div>
                                            <input class="price-input" type="text" value="<?=$row['QUANTITY']?>" name="QUANTITY_<?=$row['ID']?>" data-entity="quantity">
                                            <div class="price-plus" data-action="quantityChange" data-value="1"> <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                                                          xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4.12 9.24V0.759999H5.38V9.24H4.12ZM0.4 5.6V4.42H9.1V5.6H0.4Z" />
                                                </svg> </div>
                                        </div>

                                        <p class="goods-item__divider">x</p>

                                        <div class="goods-item__count<?if ($row['SUM_DISCOUNT_PRICE']):?> _discount<?endif?>" data-entity="basket-item-price">
                                            <p class="goods-item__new" data-entity="basket-item-price-new"><?=$row['PRICE_FORMATED']?></p>
                                            <p class="goods-item__old" data-entity="basket-item-price-old"><?=$row['FULL_PRICE_FORMATED']?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="goods-item__right">
                                <p class="goods-item__total" data-entity="basket-item-full-price"><?=$row['SUM_PRICE_FORMATED']?></p>
                                <p class="goods-item__points" data-entity="basket-item-bonus"><?=$row['BONUS_FORMATED']?></p>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>

            </div>
            <div class="modal-footer">
                <div class="design-list__container">
                    <div class="design-list__row" data-entity="total">
                        <p class="design-list__name"><?=GetMessage('BASKET_QUICK_TOTAL_PRODUCTS')?> (<span data-entity="total-buy-count"><?=$arResult['BUY_COUNT']?></span>)</p>
                        <p class="design-list__count" data-entity="total-sum"><?=$arResult['PRICE_WITHOUT_DISCOUNT']?></p>
                    </div>
                    <div class="design-list__row _discount" data-entity="total-discount"<?if (!$arResult['DISCOUNT_PRICE_ALL']):?> style="display:none;"<?endif?>>
                        <p class="design-list__name"><?=GetMessage('BASKET_DISCOUNT')?></p>
                        <p class="design-list__count" data-entity="total-discount-sum"> - <?=$arResult['DISCOUNT_PRICE_ALL_FORMATED']?> </p>
                    </div>
                    <div class="design-list__row _cashback" data-entity="bonus">
                        <p class="design-list__name"><?=GetMessage('BASKET_CASHBACK')?></p>
                        <p class="design-list__count" data-entity="bonus-sum"><?=$arResult['TOTAL_BONUS_FORMATED']?></p>
                    </div>
                    <div class="design-list__row" data-entity="full-total">
                        <p class="design-list__name"><?=GetMessage('BASKET_SUM')?></p>
                        <p class="design-list__total" data-entity="full-total-sum"><?=$arResult['allSum_FORMATED']?></p>
                    </div>
                </div>

                <div class="modal-footer__button">
                    <?if ($USER->IsAuthorized()):?>
                        <button class="submit" data-bs-dismiss="modal" data-action="submitOrder"><?=GetMessage('BASKET_QUICK_ORDER')?></button>
                    <?else:?>
                        <button class="submit" data-bs-dismiss="modal" data-action="showAuthQuestionForm"><?=GetMessage('BASKET_QUICK_ORDER')?></button>
                    <?endif?>
                    <button class="view" data-bs-dismiss="modal"><?=GetMessage('BASKET_QUICK_CONTINUE_BUY')?></button>
                </div>

                <a href="<?=SITE_DIR?>personal/cart/" class="modal-footer__link"><?=GetMessage('BASKET_QUICK_VIEW_FULL_BASKET')?></a>
            </div>
        </div>
    </div>
</div>

<?
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
$messages = Loc::loadLanguageFile(__FILE__);

$arJSParams = array(
    'containerId' => $containerId,
    'result' => $arResult,
    'params' => $arParams,
    'template' => $signedTemplate,
    'signedParamsString' => $signedParams,
    'siteId' => $component->getSiteId(),
    'siteTemplateId' => $component->getSiteTemplateId(),
    'templateFolder' => $templateFolder,
);

?>

<script>
    BX.message(<?=CUtil::PhpToJSObject($messages)?>);

    quickSaleBasket = new DevBxQuickSaleBasket(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);
</script>