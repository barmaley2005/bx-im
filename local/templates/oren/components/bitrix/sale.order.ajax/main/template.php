<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */

$this->addExternalJs(SITE_TEMPLATE_PATH.'/js/theia-sticky-sidebar.min.js');

\Bitrix\Main\UI\Extension::load("ui.vue3");

$documentRoot = Main\Application::getDocumentRoot();

$context = Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->get('ORDER_ID') <> '') {
    include(Main\Application::getDocumentRoot() . $templateFolder . '/confirm.php');
} elseif ($arParams['DISABLE_BASKET_REDIRECT'] === 'Y' && $arResult['SHOW_EMPTY_BASKET']) {
    include(Main\Application::getDocumentRoot() . $templateFolder . '/empty.php');
} else {

    $documentRoot = Main\Application::getDocumentRoot();

    \CJSCore::Init(array('fx', 'popup', 'ajax', 'currency'));

    $jsTemplates = new Main\IO\Directory($documentRoot . $templateFolder . '/vue-templates');
    /** @var Main\IO\File $jsTemplate */
    foreach ($jsTemplates->getChildren() as $jsTemplate) {
        include($jsTemplate->getPath());
    }

    $APPLICATION->SetAdditionalCSS($templateFolder . '/style.css', true);
    $this->addExternalJs($templateFolder . '/order_ajax.js');
    \Bitrix\Sale\PropertyValueCollection::initJs();
    $this->addExternalJs($templateFolder . '/script.js');

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

    ?>
        <div id="vue-order">
        </div>

    <div style="display: none">
        <?
        // we need to have all styles for sale.location.selector.steps, but RestartBuffer() cuts off document head with styles in it
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.steps',
            '.default',
            array(),
            false
        );
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '.default',
            array(),
            false
        );
        ?>
    </div>

    <?

    $signer = new Main\Security\Sign\Signer;
    $signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
    $signedBasketTemplate = $signer->sign('main', 'sale.basket.basket');
    $signedBasketParamsString = $signer->sign(base64_encode(serialize(array())), 'sale.basket.basket');
    $messages = Loc::loadLanguageFile(__FILE__);

    $arStoreList = \Bitrix\Catalog\StoreTable::getList([
        'filter' => [
            'ACTIVE' => 'Y',
        ],
    ])->fetchAll();

    $arJSParams = array(
        'result' => $arResult['JS_DATA'],
        'locations' => $arResult['LOCATIONS'],
        'params' => $arParams,
        'signedParamsString' => $signedParams,
        'signedBasketTemplate' => $signedBasketTemplate,
        'signedBasketParamsString' => $signedBasketParamsString,
        'siteID' => $component->getSiteId(),
        'ajaxUrl' => $component->getPath() . '/ajax.php',
        'templateFolder' => $templateFolder,
        'propertyValidation' => true,
        'showWarnings' => true,
        'pickUpMap' => array(
            'defaultMapPosition' => array(
                'lat' => 55.76,
                'lon' => 37.64,
                'zoom' => 7,
            ),
            'secureGeoLocation' => false,
            'geoLocationMaxTime' => 5000,
            'minToShowNearestBlock' => 3,
            'nearestPickUpsToShow' => 3,
        ),
        'propertyMap' => array(
            'defaultMapPosition' => array(
                'lat' => 55.76,
                'lon' => 37.64,
                'zoom' => 7,
            ),
        ),
        'storeList' => $arStoreList,
    );

    ?>
    <script>
        BX.Currency.setCurrencies(<? echo CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true); ?>);
        BX.message(<?= CUtil::PhpToJSObject($messages) ?>);

        BX.Sale.OrderAjaxComponent = createVueOrderAjaxComponent('#vue-order', '#order-tpl',<?= json_encode($arJSParams) ?>);
    </script>
    <script>
        <?
        // spike: for children of cities we place this prompt
        $city = \Bitrix\Sale\Location\TypeTable::getList(array('filter' => array('=CODE' => 'CITY'), 'select' => array('ID')))->fetch();
        ?>
        BX.saleOrderAjax.init(<?=CUtil::PhpToJSObject(array(
            'source' => $component->getPath() . '/get.php',
            'cityTypeId' => intval($city['ID']),
            'messages' => array(
                'otherLocation' => '--- ' . Loc::getMessage('SOA_OTHER_LOCATION'),
                'moreInfoLocation' => '--- ' . Loc::getMessage('SOA_NOT_SELECTED_ALT'), // spike: for children of cities we place this prompt
                'notFoundPrompt' => '<div class="-bx-popup-special-prompt">' . Loc::getMessage('SOA_LOCATION_NOT_FOUND') . '.<br />' . Loc::getMessage('SOA_LOCATION_NOT_FOUND_PROMPT', array(
                        '#ANCHOR#' => '<a href="javascript:void(0)" class="-bx-popup-set-mode-add-loc">',
                        '#ANCHOR_END#' => '</a>'
                    )) . '</div>'
            )
        ))?>);
    </script>
    <?

}
