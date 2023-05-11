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
<!-- Оставить отзыв -->
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?=GetMessage('REVIEW_TITLE')?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?=GetMessage('REVIEW_CLOSE_TEXT')?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <?if ($arResult['SUCCESS']):?>
            <div class="comment-modal-assessment">
                <p class="comment-modal-assessment__title"><?=GetMessage('REVIEW_SUCCESS_MESSAGE')?></p>
            </div>
            <?else:?>
            <form action="<?= POST_FORM_ACTION_URI ?>" method="post">
                <?foreach ($arResult['HIDDEN_FIELDS'] as $ar):?>
                    <input type="hidden" name="<?=$ar['NAME']?>" value="<?=$ar['VALUE']?>">
                <?endforeach?>
                <input type="hidden" name="productId" value="<?=$arParams['DEFAULT_FIELD_VALUE_UF_PRODUCT_ID']?>">

                <?if (!empty($arResult['ERRORS'])):?>
                    <div style="grid-column:1/-1;">
                        <?foreach ($arResult['ERRORS'] as $ar):?>
                            <div class="alert alert-danger"><?=$ar['text']?></div>
                        <?endforeach?>
                    </div>
                <?endif?>

                <div class="comment-modal-assessment">
                    <p class="comment-modal-assessment__title"><?=GetMessage('REVIEW_RATE_PRODUCT')?></p>
                    <div class="comment-modal-assessment__star">
                        <div class="rating">

                            <?
                            for ($rating=5; $rating>0; $rating--)
                            {
                                $checked = $arResult['FIELDS']['UF_RATING']['VALUE'] == $rating;
                                ?>
                                <input type="radio" id="star<?=$rating?>" name="UF_RATING" value="<?=$rating?>"<?if ($checked):?> checked<?endif?>/>
                                <label for="star<?=$rating?>">
                                    <svg width="21" height="20" viewBox="0 0 21 20" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                              d="M16.1864 18.8376C15.878 18.9938 15.5215 18.9982 15.1784 18.7548L10.9863 15.7549C10.8039 15.6421 10.6602 15.5937 10.5119 15.5937C10.3414 15.5937 10.1836 15.6274 9.98716 15.708L5.84695 18.6991C5.51861 18.9696 5.15691 18.9652 4.83302 18.8171C4.80538 18.8046 4.77819 18.7911 4.75149 18.7768C4.68933 18.7465 4.63292 18.7058 4.58472 18.6565C4.61956 18.6917 4.53581 18.6169 4.48244 18.5539C4.37386 18.4319 4.30106 18.2829 4.27193 18.1229C4.2428 17.9629 4.25844 17.7981 4.31716 17.6463L5.94108 12.7229C5.9904 12.579 5.99136 12.4233 5.9438 12.2789C5.89625 12.1344 5.80273 12.0091 5.67722 11.9216L1.47474 8.88219C1.30323 8.771 1.17326 8.60729 1.10473 8.41611C1.0362 8.22494 1.03287 8.01682 1.09526 7.8236C1.17753 7.44679 1.55479 7.12862 2.03063 7.12862H7.27223C7.57834 7.12862 7.86517 6.92042 7.96968 6.62645L9.58322 1.73744C9.72182 1.2536 10.1302 1.05273 10.5127 1.05273C10.6898 1.05273 10.8403 1.07913 11.0196 1.18689C11.2027 1.29832 11.3428 1.4706 11.4354 1.71472L13.0512 6.61326C13.1601 6.92042 13.447 7.12862 13.7531 7.12862H18.9621C19.2978 7.12862 19.5661 7.27671 19.7381 7.51276C19.7826 7.57434 19.8189 7.63886 19.8389 7.68138C20.0568 8.03326 19.956 8.57575 19.5402 8.86752L15.3177 11.9216C15.1905 12.0109 15.0962 12.1388 15.0492 12.286C15.0023 12.4331 15.0052 12.5914 15.0576 12.7368L16.6585 17.6287C16.8734 18.1265 16.6244 18.6184 16.2509 18.8024C16.2299 18.8151 16.2084 18.8269 16.1864 18.8376Z"/>
                                    </svg>
                                </label>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="comment-modal__container">
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for=""><?=GetMessage('REVIEW_LABEL_NAME')?></label>
                            <input type="text" class="input" placeholder="<?=GetMessage('REVIEW_PLACEHOLDER_NAME')?>" name="UF_NAME" value="<?=$arResult['FIELDS']['UF_NAME']['VALUE']?>">
                            <span class="placement-inputs__info"><?=GetMessage('REVIEW_FIELD_ERROR')?></span>
                        </div>
                    </div>
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for=""><?=GetMessage('REVIEW_LABEL_CITY')?></label>
                            <input type="text" class="input" placeholder="<?=GetMessage('REVIEW_PLACEHOLDER_CITY')?>" name="UF_CITY" value="<?=$arResult['FIELDS']['UF_CITY']['VALUE']?>">
                            <span class="placement-inputs__info"><?=GetMessage('REVIEW_FIELD_ERROR')?></span>
                        </div>
                    </div>
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for=""><?=GetMessage('REVIEW_LABEL_EMAIL')?></label>
                            <input type="email" class="input" placeholder="<?=GetMessage('REVIEW_PLACEHOLDER_EMAIL')?>" name="UF_EMAIL" value="<?=$arResult['FIELDS']['UF_EMAIL']['VALUE']?>">
                            <span class="placement-inputs__info"><?=GetMessage('REVIEW_FIELD_ERROR')?></span>
                        </div>
                    </div>
                    <div class="comment-modal__item order-2 order-md-0">
                        <div class="modalMy-check">
                            <label class="check">
                                <input type="hidden" name="UF_RECOMMEND" value="0">
                                <input class="check__input" type="checkbox" checked name="UF_RECOMMEND" value="1">
                                <span class="check__box"></span>
                                <?=GetMessage('REVIEW_I_RECOMMEND')?>
                            </label>
                        </div>
                    </div>
                    <div class="comment-modal__item order-1">
                        <textarea name="UF_COMMENT" placeholder="<?=GetMessage('REVIEW_PLACEHOLDER_COMMENT')?>"><?=$arResult['FIELDS']['UF_COMMENT']['VALUE']?></textarea>
                    </div>
                </div>

                <div class="comment-modal__consent">
                    <label class="check">
                        <input class="check__input" type="checkbox" checked>
                        <span class="check__box"></span>
                        <p>
                            <?=GetMessage('REVIEW_POLICY_TEXT')?>
                        </p>
                    </label>
                </div>

                <div class="comment-modal__button">
                    <button class="submit"><?=GetMessage('REVIEW_SUBMIT_BUTTON_TEXT')?></button>
                </div>
            </form>
            <?endif?>
        </div>
    </div>
</div>
