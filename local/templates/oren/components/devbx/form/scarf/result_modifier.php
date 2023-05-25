<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var CBitrixComponentTemplate $this */

\Bitrix\Main\Loader::includeModule('currency');

$arResult['CURRENCIES'] = array();

$currencyIterator = \Bitrix\Currency\CurrencyTable::getList(array(
    'select' => array('CURRENCY')
));
while ($currency = $currencyIterator->fetch())
{
    $currencyFormat = CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
    $arResult['CURRENCIES'][] = array(
        'CURRENCY' => $currency['CURRENCY'],
        'FORMAT' => array(
            'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
            'DEC_POINT' => $currencyFormat['DEC_POINT'],
            'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
            'DECIMALS' => $currencyFormat['DECIMALS'],
            'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
            'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
        )
    );
}
unset($currencyFormat, $currency, $currencyIterator);
