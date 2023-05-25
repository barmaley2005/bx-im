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

$statusName = $arResult['STATUS']['NAME'];

$dateFormated = CIBlockFormatProperties::DateFormat("j F Y", $arResult['DATE_INSERT']->GetTimestamp());
$fullDateFormated = CIBlockFormatProperties::DateFormat("j F Y, H:m", $arResult['DATE_INSERT']->GetTimestamp());

$statusClass = '_on-way';

if ($arResult['CANCELED'] == 'Y')
{
    $statusClass = '_cancelled';
} else {
    if ($arResult['STATUS_ID'] == 'F')
    {
        $statusClass = '_received';
    }
}

?>
<div class="account-block__pages">
    <div id="order-info" class="account-block__page account-page">
        <div class="order-info">
            <div class="order-info__headline">
                <a href="<?=$arResult['URL_TO_LIST']?>" class="order-info__back">
                    <svg class="order-info__back-icon" width="17" height="12" viewBox="0 0 17 12" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 6L17 6M1 6L7.29333 1M1 6L7.29333 11" stroke="currentColor" stroke-width="0.75" />
                    </svg>
                    <span><?=GetMessage('BACK')?></span>
                </a>
                <h2 class="order-info__id">
                    <span class="order-info__id-label"><?=GetMessage('ORDER_NUM')?></span>
                    <span><?=$arResult['ACCOUNT_NUMBER']?></span>
                </h2>
                <p class="order-info__date"><?=GetMessage('ORDER_FROM')?> <?=$dateFormated?></p>
            </div>
            <div class="order-info">
                <div class="order-info__block">
                    <h3 class="order-info__title"><?=GetMessage('GENERAL_INFO')?></h3>
                    <ul class="order-info__list">
                        <li class="order-info__row">
                            <p class="order-info__name"><?=GetMessage('ORDER_DATE')?></p>
                            <p class="order-info__value"><?=$fullDateFormated?></p>
                        </li>
                        <?
                        foreach ($arResult['ORDER_PROPS'] as $arProp)
                        {
                            ?>
                            <li class="order-info__row">
                                <p class="order-info__name"><?=$arProp['NAME']?>:</p>
                                <p class="order-info__value"><?=$arProp['VALUE']?></p>
                            </li>
                            <?
                        }
                        ?>
                        <?
                        foreach ($arResult['SHIPMENT'] as $arShipment)
                        {
                            if ($arShipment['PRICE_DELIVERY']>0)
                            {
                                $displayDeliveryPrice = $arShipment['PRICE_DELIVERY_FORMATTED'];
                            } else {
                                $displayDeliveryPrice = GetMessage('FREE_DELIVERY');
                            }
                            ?>
                            <li class="order-info__row">
                                <p class="order-info__name"><?=GetMessage('DELIVERY')?></p>
                                <p class="order-info__value"><?=$arShipment['DELIVERY_NAME']?></p>
                            </li>
                            <li class="order-info__row">
                                <p class="order-info__name"><?=GetMessage('DELIVERY_COST')?></p>
                                <p class="order-info__value"><?=$displayDeliveryPrice?></p>
                            </li>
                            <?
                        }
                        ?>
                        <li class="order-info__row">
                            <p class="order-info__name"><?=GetMessage('ORDER_STATUS')?></p>
                            <p class="order-info__value order-status <?=$statusClass?>"><?=$statusName?></p>
                        </li>
                    </ul>
                </div>
                <div class="order-info__block">
                    <?
                    foreach ($arResult['PAYMENT'] as $arPayment)
                    {
                        ?>
                        <h3 class="order-info__title"><?=GetMessage('PAY_SYSTEM_TYPE')?></h3>
                        <ul class="order-info__list _two-colunm">
                            <li class="order-info__row">
                                <p class="order-info__name"><?=$arPayment['PAY_SYSTEM_NAME']?>:</p>
                                <p class="order-info__value"><?=$arPayment['PRICE_FORMATED']?></p>
                            </li>
                            <?/*
                            <li class="order-info__row">
                                <p class="order-info__name">Вернется кэшбэк:</p>
                                <p class="order-info__value">200 баллов</p>
                            </li>
                            */?>
                        </ul>
                        <?
                    }
                    ?>
                </div>
                <div class="order-info__block">
                    <h3 class="order-info__title"><?=GetMessage('PRODUCTS_INFO')?></h3>
                    <ul class="order-info__list _two-colunm">
                        <li class="order-info__row">
                            <p class="order-info__name"><?=GetMessage('PRODUCTS_TOTAL_COUNT')?></p>
                            <p class="order-info__value"><?=count($arResult['BASKET'])?></p>
                        </li>
                        <li class="order-info__row">
                            <p class="order-info__name"><?=GetMessage('PRODUCTS_SUM')?></p>
                            <p class="order-info__value"><?=$arResult['PRICE_FORMATED']?></p>
                        </li>
                    </ul>
                </div>
            </div>
            <ul class="order-info__product-list">
                <?
                foreach ($arResult['BASKET'] as $arBasketItem)
                {
                    ?>
                    <li class="order-info__product">
                        <a href="<?=$arBasketItem['DETAIL_PAGE_URL']?>" class="order-info__product-img">
                            <img src="<?=$arBasketItem['PICTURE']['SRC']?>" alt="" loading="lazy" />
                        </a>
                        <a href="<?=$arBasketItem['DETAIL_PAGE_URL']?>" class="order-info__product-link"><?=$arBasketItem['NAME']?></a>
                        <p class="order-info__product-cost">
                            <span class="order-info__product-price"><?=$arBasketItem['PRICE_FORMATED']?></span>
                            <span class="order-info__product-quantity">x <?=$arBasketItem['QUANTITY']?></span>
                        </p>
                        <?
                        foreach ($arBasketItem['PROPS'] as $arSkuProp)
                        {
                            if ($arSkuProp['SKU_TYPE'] == 'IMAGE')
                            {
                                ?>
                                <div class="order-info__product-color">
                                    <img src="<?=$arSkuProp['SKU_VALUE']['PICT']['SRC']?>">
                                </div>
                                <?
                            }
                        }
                        ?>
                        <button type="button" class="order-info__product-feedback feedback"
                                data-action="writeReview" data-product-id="<?=$arBasketItem['PRODUCT_ID']?>"><?=GetMessage('PRODUCT_WRITE_REVIEW')?></button>
                    </li>
                    <?
                }
                ?>
            </ul>
            <div class="order-info__buttons">
                <a href="<?=$arResult['URL_TO_COPY']?>" class="order-info__button _repeat"><?=GetMessage('ORDER_COPY')?></a>
                <button type="button" class="order-info__button _question" data-action="showQuestionForm"><?=GetMessage('ASK_QUESTION')?></button>
            </div>
        </div>
    </div>
</div>

<?
$this->SetViewTarget('BEFORE_FOOTER');
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/buy_with_product.php');
include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/watch_recently.php');
$this->EndViewTarget();
?>