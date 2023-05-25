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

\Bitrix\Main\Loader::includeModule('devbx.core');

$APPLICATION->SetPageProperty('PERSONAL_CONTENT_CLASS', 'account-block__content _orders');
?>
<div class="account-block__pages">
    <div id="my-orders" class="account-block__page account-page">
        <a href="<?= SITE_DIR ?>personal/" class="account-page__back">
            <svg class="account-block__back-icon" width="8" height="14" viewBox="0 0 8 14" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M7 13L1 7L7 1" stroke="#877569" stroke-linecap="round"/>
            </svg>
        </a>
        <h2 class="account-page__title"><?=GetMessage('PERSONAL_ORDER_LIST_TITLE')?></h2>
        <div class="my-orders">
            <ul class="my-orders-list my-orders__list">
                <?
                if (!empty($arResult['ORDERS'])) {

                foreach ($arResult['ORDERS'] as $ar) {
                    $arOrder = $ar['ORDER'];

                    $statusName = $arResult['INFO']['STATUS'][$arOrder['STATUS_ID']]['NAME'];

                    $dateFormated = CIBlockFormatProperties::DateFormat("j F Y", $arOrder['DATE_INSERT']->GetTimestamp());

                    $statusClass = '_on-way';

                    if ($arOrder['CANCELED'] == 'Y')
                    {
                        $statusClass = '_cancelled';
                    } else {
                        if ($arOrder['STATUS_ID'] == 'F')
                        {
                            $statusClass = '_received';
                        }
                    }

                    ?>
                    <li class="my-orders__item">
                        <a href="<?=$arOrder['URL_TO_DETAIL']?>" class="my-orders__order">
                            <div class="my-orders__row">
                                <p class="order-status <?=$statusClass?>"><?=$statusName?></p>
                                <p>
                                    <span class="my-orders__label"><?=GetMessage('PERSONAL_ORDER_LIST_ORDER_NUM')?></span>
                                    <span class="my-orders__id"><?=$arOrder['ACCOUNT_NUMBER']?></span>
                                </p>
                            </div>
                            <div class="my-orders__row">
                                <p class="my-orders__info">
                                    <span class="my-orders__number"><?=\DevBx\Core\Utils::numWord(count($ar['BASKET_ITEMS']),
                                            array(GetMessage('PERSONAL_ORDER_LIST_PRODUCT_CNT_1'),GetMessage('PERSONAL_ORDER_LIST_PRODUCT_CNT_2_4'),GetMessage('PERSONAL_ORDER_LIST_PRODUCT_CNT_5')))?></span>
                                    <span class="my-orders__costing"><?=$arOrder['FORMATED_PRICE']?></span>
                                </p>
                                <p class="my-orders__date"><?=GetMessage('PERSONAL_ORDER_LIST_ORDER_DATE_FROM', array('#DATE#'=>$dateFormated))?></p>
                            </div>
                        </a>
                    </li>
                    <?
                }
                ?>
            </ul>
            <?
            } else {
                ?>
                <div class="my-orders-empty">
                    <p class="my-orders-empty__text"><?=GetMessage('PERSONAL_ORDER_LIST_EMPTY_TEXT_1')?></p>
                    <p class="my-orders-empty__text"><?=GetMessage('PERSONAL_ORDER_LIST_EMPTY_TEXT_2',array('#LINK#'=>SITE_DIR.'catalog/'))?></p>
                </div>
                <?
            }
            ?>
        </div>
    </div>
</div>
<?
if (!empty($arResult['ORDERS'])) {
    $this->SetViewTarget('PERSONAL_FOOTER_CONTENT');
    ?>
    <aside class="account-block__help">
        <h3 class="account-block__title"><?=GetMessage('PERSONAL_ORDER_LIST_HELP_TITLE')?></h3>
        <ul class="account-block__help-list">
            <li class="account-block__help-item">
                <a href="" class="account-block__help-link"><?=GetMessage('PERSONAL_ORDER_LIST_HELP_LINK_TITLE_1')?></a>
            </li>
            <li class="account-block__help-item">
                <a href="" class="account-block__help-link"><?=GetMessage('PERSONAL_ORDER_LIST_HELP_LINK_TITLE_2')?></a>
            </li>
            <li class="account-block__item">
                <a href="" class="account-block__help-link"><?=GetMessage('PERSONAL_ORDER_LIST_HELP_LINK_TITLE_3')?></a>
            </li>
        </ul>
        <p class="account-block__question"><?=GetMessage('PERSONAL_ORDER_LIST_HELP_QUESTION_WRITE')?></p>
        <button class="account-block__ask subscribe" data-action="showQuestionForm"><?=GetMessage('PERSONAL_ORDER_LIST_ASK_QUESTION')?></button>
    </aside>
    <?
    $this->EndViewTarget();
}
?>