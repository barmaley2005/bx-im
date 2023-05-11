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

$containerId = $this->GetEditAreaId('reviews');
$obName = 'reviews_'.$arParams['PRODUCT_ID'];

?>
<div class="accordeon-recall__container<?if (empty($arResult['ITEMS'])):?> _empty<?endif?>" id="<?=$containerId?>">
    <div class="accordeon-recall__box" data-entity="items">
        <?
        foreach ($arResult['ITEMS'] as $arItem)
        {
            $arDisplayName = [$arItem['UF_NAME']];

            if ($arItem['UF_CITY'])
                $arDisplayName[] = $arItem['UF_CITY'];

            $displayDate = CIBlockFormatProperties::DateFormat("j F Y", MakeTimeStamp($arItem["CREATED_DATE"], 'YYYY-MM-DD HH:MI:SS'));

            $arPhoto = false;

            $arUser = \Bitrix\Main\UserTable::getList([
                'filter' => array(
                    '=ID' => $arItem['CREATED_USER_ID'],
                ),
                'select' => array('PERSONAL_PHOTO')
            ])->fetch();

            if ($arUser)
            {
                $arPhoto = \CFile::ResizeImageGet($arUser['PERSONAL_PHOTO'], array('width'=>50,'height'=>50));
            }

            ?>
            <div class="accordeon-recall__row">
                <div class="accordeon-recall__head">
                    <div class="accordeon-recall__user">
                        <div class="accordeon-recall__img">
                            <?if ($arPhoto):?>
                            <img src="<?=$arPhoto['src']?>" alt="">
                            <?endif?>
                        </div>
                        <div class="accordeon-recall__info">
                            <p class="accordeon-recall__name"><?=implode(', ', $arDisplayName)?></p>
                            <?if (intval($arItem['UF_RECOMMEND'])):?>
                            <div class="accordeon-recall__subname">
                                <svg width="15" height="17" viewBox="0 0 15 17" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_507_26654)">
                                        <path
                                            d="M10.412 1.18214C10.6189 1.27772 10.7222 1.5075 10.6543 1.72104L9.27211 6.07118L12.2935 6.23385L12.3578 6.24178C13.6677 6.49624 14.2476 7.40333 14.0107 8.7737C13.8174 9.89189 13.6504 11.7477 13.5071 14.3762C13.3103 15.6401 12.4244 16.2387 11.0372 16.0804C9.90773 15.9514 7.68542 15.5995 4.36169 15.0238C4.13949 15.318 3.78211 15.5088 3.37913 15.5088H2.15831C1.48408 15.5088 0.9375 14.9747 0.9375 14.3158V6.56144C0.9375 5.90256 1.48408 5.36846 2.15831 5.36846H3.37913C3.75898 5.36846 4.09832 5.53798 4.3222 5.80384C5.71661 4.22507 6.69546 2.99416 7.25551 2.11786C8.02108 0.920032 9.12891 0.589164 10.412 1.18214ZM8.03476 2.59341C7.39017 3.60194 6.24956 5.0182 4.6083 6.85098V14.1548C7.86641 14.7186 10.0477 15.0635 11.1439 15.1887C12.0572 15.2929 12.4796 15.0076 12.5942 14.2852C12.7346 11.6688 12.9042 9.78464 13.1048 8.62416C13.2598 7.72752 12.9892 7.28964 12.2092 7.12846L8.63351 6.93592C8.33489 6.91987 8.13138 6.63367 8.22008 6.35464L9.64995 1.85456C8.9818 1.66582 8.47761 1.90046 8.03476 2.59341ZM3.37913 6.26319H2.15831C1.98976 6.26319 1.85311 6.39675 1.85311 6.56144V14.3158C1.85311 14.4805 1.98976 14.6141 2.15831 14.6141H3.37913C3.54769 14.6141 3.68433 14.4805 3.68433 14.3158V6.56144C3.68433 6.39675 3.54769 6.26319 3.37913 6.26319Z"
                                            fill="#04857F" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_507_26654">
                                            <rect width="15" height="17" fill="white" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                <p><?=GetMessage('REVIEWS_I_RECOMMEND')?></p>
                            </div>
                            <?endif?>
                        </div>
                    </div>
                    <div class="accordeon-recall__star">
                        <?
                        $rating = intval($arItem['UF_RATING']);

                        for ($i=1;$i<=5;$i++)
                        {
                            $fillColor = $rating>=$i ? '#F4AF48' : '#E6E2D9';
                            ?>
                            <svg width="21" height="20" viewBox="0 0 21 20" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                      d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"
                                      fill="<?=$fillColor?>" />
                            </svg>
                            <?
                        }

                        ?>
                    </div>
                </div>
                <div class="accordeon-recall__body">
                    <div class="accordeon-recall__description">
                        <p>
                            <?=$arItem['UF_COMMENT']?>
                        </p>
                    </div>

                    <div class="accordeon-recall__date">
                        <p><?=$displayDate?></p>
                    </div>
                </div>
            </div>
            <?
        }
        ?>
    </div>

    <div class="accordeon-recall__text">
        <span><?=GetMessage('REVIEWS_NO_ITEMS')?></span>

    </div>
    <div class="accordeon-recall__button">
        <button class="view d-sm-none"><?=GetMessage('REVIEWS_MOBILE_VIEW_MORE')?></button>
        <button class="view d-none d-sm-flex"><?=GetMessage('REVIEWS_VIEW_MORE')?></button>
        <button class="submit" data-action="writeReview"><?=GetMessage('REVIEWS_WRITE_REVIEW')?></button>
    </div>
</div>

<?
$arJSParams = array(
    'CONTAINER_ID' => $containerId,
    'PRODUCT_ID' => $arParams['PRODUCT_ID'],
    'SITE_ID' => $component->getSiteId(),
    'TEMPLATE_ID' => $component->getSiteTemplateId(),
);
?>

<script>
    <?echo $obName?> = new ProductReviews(<?=\Bitrix\Main\Web\Json::encode($arJSParams)?>);
</script>