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

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('currency');

$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$offerId = intval($request['giftId']);

$arFilter = array(
    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
    '=CODE' => 'gift',
    '=ACTIVE' => 'Y',
);

$arBasePrice = \Bitrix\Catalog\GroupTable::getBasePriceType();
$arBasePrice['CAN_VIEW'] = true;

$arPrices = array($arBasePrice['NAME'] => $arBasePrice);

$arSelect = array(
    'ID',
    'IBLOCK_ID',
    'NAME',
    'PREVIEW_PICTURE',
    'DETAIL_PICTURE',
    'CATALOG_PRICE_'.$arBasePrice['ID'],
    'CATALOG_CURRENCY_'.$arBasePrice['ID'],
);


$obElement = \CIBlockElement::GetList([], $arFilter)->GetNextElement();
if (!$obElement)
{
    \Bitrix\Iblock\Component\Tools::process404("", true, true, true);
    return;
}

$arElement = $obElement->GetFields();
$arElement['PROPERTIES'] = $obElement->GetProperties();

$arOffers = \CCatalogSku::getOffersList($arElement['ID'],0,array(), $arSelect);

$arElement['OFFERS'] = $arOffers[$arElement['ID']];

if (!array_key_exists($offerId, $arElement['OFFERS']))
{
    \Bitrix\Iblock\Component\Tools::process404("", true, true, true);
    return;
}

foreach ($arElement['OFFERS'] as &$arOffer)
{
    $offerIblockId = $arOffer['IBLOCK_ID'];

    $arOffer['PRICES'] = \CIBlockPriceTools::GetItemPrices(
        $arOffer['IBLOCK_ID'],
        $arPrices,
        $arOffer
    );

    $arOffer['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($arOffer['PRICES']);
}

$customNominal = false;

if ($request['nominal']>=5000)
{
    $nominal = doubleval($request['nominal']);
    $customNominal = true;
} else {
    $nominal = $arElement['OFFERS'][$offerId]['MIN_PRICE']['VALUE'];
}
?>
<section class="section placement" id="vue-app-gift-order">
</section>

<script type="text/html" id="gift-order-tpl">
    <div class="container">
        <h1 class="title text-left">Оформление сертификата</h1>

        <form action="" class="certificate-form" @submit.stop.prevent>
            <div class="certificate-form__item">
                <h2 class="title">Ваши данные</h2>
                <div class="certificate-form__box">
                    <field-input v-model.trim="form.senderName" input-type="text" label="Имя" v-model:error="formErrors.senderName"></field-input>
                    <field-input v-model.trim="form.senderSecondName" input-type="text" label="Отчество" v-model:error="formErrors.senderSecondName"></field-input>
                    <field-input v-model.trim="form.senderLastName" input-type="text" label="Фамилия" v-model:error="formErrors.senderLastName"></field-input>
                    <field-input v-model.trim="form.senderEmail" input-type="email" label="E-mail" v-model:error="formErrors.senderEmail"></field-input>
                    <field-input v-model.trim="form.senderPhone" input-type="tel" label="Номер телефона" v-model:error="formErrors.senderPhone"></field-input>
                </div>
            </div>
            <div class="certificate-form__item">
                <h2 class="title">Данные получателя</h2>
                <div class="certificate-form__box">
                    <field-input v-model.trim="form.receiverName" input-type="text" label="Имя" v-model:error="formErrors.receiverName"></field-input>
                    <field-input v-model.trim="form.receiverSecondName" input-type="text" label="Отчество" v-model:error="formErrors.receiverSecondName"></field-input>
                    <field-input v-model.trim="form.receiverLastName" input-type="text" label="Фамилия" v-model:error="formErrors.receiverLastName"></field-input>
                    <field-input v-model.trim="form.receiverEmail" input-type="email" label="E-mail" v-model:error="formErrors.receiverEmail"></field-input>
                    <field-input v-model.trim="form.receiverPhone" input-type="tel" label="Номер телефона" v-model:error="formErrors.receiverPhone"></field-input>
                </div>
            </div>
            <div class="certificate-form__item">
                <h2 class="title">Сообщение получателю</h2>
                <div class="placement-item__comment">
                    <textarea placeholder="Ваше сообщение" v-model="form.message"></textarea>
                </div>
            </div>

            <div class="certificate-form__footer">
                <p class="certificate-form__total">
                    Итого: <span class="certificate-form__count"><?=CCurrencyLang::CurrencyFormat($nominal, 'RUB', true)?></span>
                </p>

                <div class="certificate-form__pay">
                    <div class="certificate-form__button">
                        <button class="submit" @click.stop.prevent="submitOrder">Оплатить</button>
                    </div>
                    <div class="certificate-form__check">
                        <label class="check">
                            <input class="check__input" type="checkbox" checked ref="policy">
                            <span class="check__box"></span>
                            <p>
                                Я даю своё согласие на <a href="<?=SITE_DIR?>customers/policy/">обработку персональных данных</a> и согласен с
                                <a href="<?=SITE_DIR?>customers/policy/">условиями политики конфиденциальности</a>
                            </p>
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<?
\Bitrix\Main\UI\Extension::load("ui.vue3");

$arJSParams = array(
    'container' => '#vue-app-gift-order',
    'template' => '#gift-order-tpl',
    'offerId' => $offerId,
    'nominal' => $nominal,
    'customNominal' => $customNominal,
    'orderPath' => SITE_DIR.'personal/order/make/'
);
?>

<script>
    (function() {

        window.giftOrder = createVueGiftOrder(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);

    })();
</script>