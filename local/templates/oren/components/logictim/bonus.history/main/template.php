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

?>
<div class="account-history">
    <div class="account-history__wrap">
        <button type="button" class="account-history__toggle">
            <span><?=GetMessage('ACCOUNT_BONUS_HISTORY_TITLE')?></span>
            <svg class="account-history__toggle-icon" width="14" height="8" viewBox="0 0 14 8" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round"></path>
            </svg>
        </button>
        <div class="account-history__container">
            <ul class="account-history__table">
                <li class="account-history__row">
                    <p class="account-history__item account-history__item_data_point"><?=GetMessage('ACCOUNT_BONUS_COUNT_TITLE')?></p>
                    <p class="account-history__item account-history__item_data_order"><?=GetMessage('ACCOUNT_BONUS_ORDER_NUM')?></p>
                    <p class="account-history__item"><?=GetMessage('ACCOUNT_BONUS_DATE')?></p>
                    <p class="account-history__item"><?=GetMessage('ACCOUNT_BONUS_EXPIRATION')?></p>
                </li>
                <?
                foreach ($arResult['ITEMS'] as $arItem)
                {
                    $insertDate = new \Bitrix\Main\Type\DateTime($arItem['DATE_CREATE']);
                    //echo '<pre>';print_r($arItem['PROPS']['OPERATION_TYPE']);echo '</pre>';
                    //var_dump($arItem['PROPS']['OPERATION_TYPE']['VALUE']);
                    ?>
                    <li class="account-history__row">
                        <p class="account-history__item account-history__item_data_point">
                            <?if ($arItem['PROPS']['OPERATION_TYPE']['VALUE_XML_ID'] == 'MINUS_FROM_ORDER'):?>
                                - <?=$arItem['PROPS']['OPERATION_SUM']['VALUE']?> <?=GetMessage('ACCOUNT_BONUS_BALLS_TITLE')?>
                            <?else:?>
                                <?=$arItem['PROPS']['OPERATION_SUM']['VALUE']?> <?=GetMessage('ACCOUNT_BONUS_BALLS_TITLE')?>
                            <?endif?>
                        </p>
                        <p class="account-history__item account-history__item_data_order">
                            <?if ($arItem['PROPS']['ORDER_ID']['VALUE']):?>
                            <?=GetMessage('ACCOUNT_BONUS_ROW_ORDER_NUM', array('#NUM#'=>$arItem['PROPS']['ORDER_ID']['VALUE']))?>
                            <?endif?>
                        </p>
                        <p class="account-history__item account-history__item_data_date">
                            <span class="account-history__date-label"><?=$arItem['PROPS']['OPERATION_TYPE']['VALUE']?></span>
                            <span><?=$insertDate->format('d.m.Y')?></span>
                        </p>
                        <p class="account-history__item account-history__item_data_date">
                            <span class="account-history__date-label"><?=GetMessage('ACCOUNT_BONUS_ROW_EXPIRATION')?></span>
                            <span><?=$arItem['PROPS']['LIVE_DATE']['VALUE']?></span>
                        </p>
                    </li>
                    <?
                }
                ?>
            </ul>
        </div>
    </div>
</div>
