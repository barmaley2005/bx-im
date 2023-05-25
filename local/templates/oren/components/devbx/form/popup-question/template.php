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
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?=GetMessage('POPUP_QUESTION_TITLE')?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?=GetMessage('POPUP_QUESTION_MODAL_CLOSE')?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <?if ($arResult['SUCCESS']):?>
            <div class="comment-modal__container">
                <h2 class="title"><?=GetMessage('POPUP_QUESTION_SUCCESS_TEXT')?></h2>
            </div>
            <?else:?>
            <form action="<?=POST_FORM_ACTION_URI?>" method="post">

                <?foreach ($arResult['HIDDEN_FIELDS'] as $ar):?>
                    <input type="hidden" name="<?=$ar['NAME']?>" value="<?=$ar['VALUE']?>">
                <?endforeach?>

                <?if (!empty($arResult['ERRORS'])):?>
                    <div style="grid-column:1/-1;">
                        <?foreach ($arResult['ERRORS'] as $ar):?>
                            <div class="alert alert-danger"><?=$ar['text']?></div>
                        <?endforeach?>
                    </div>
                <?endif?>

                <div class="comment-modal__container">
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for=""><?=GetMessage('POPUP_QUESTION_NAME')?></label>
                            <input required type="text" class="input" placeholder="<?=GetMessage('POPUP_QUESTION_NAME')?>" name="UF_NAME" value="<?=$arResult['FIELDS']['UF_NAME']['VALUE']?>">
                            <span class="placement-inputs__info"><?=GetMessage('POPUP_QUESTION_INVALID_FIELD')?></span>
                        </div>
                    </div>
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for=""><?=GetMessage('POPUP_QUESTION_PHONE')?>*</label>
                            <input required type="text" class="input phone" placeholder="<?=GetMessage('POPUP_QUESTION_PHONE')?>*" name="UF_PHONE" value="<?=$arResult['FIELDS']['UF_PHONE']['VALUE']?>">
                            <span class="placement-inputs__info"><?=GetMessage('POPUP_QUESTION_INVALID_FIELD')?></span>
                        </div>
                    </div>

                    <div class="comment-modal__item order-1">
                        <div class="placement-textarea">
                            <label class="placement-textarea__label" for=""><?=GetMessage('POPUP_QUESTION_COMMENT')?></label>
                            <textarea required placeholder="<?=GetMessage('POPUP_QUESTION_COMMENT')?>" name="UF_QUESTION"><?=$arResult['FIELDS']['UF_QUESTION']['VALUE']?></textarea>
                        </div>
                    </div>
                </div>

                <div class="comment-modal__consent">
                    <label class="check">
                        <input class="check__input" type="checkbox" checked required>
                        <span class="check__box"></span>
                        <p><?=GetMessage('POPUP_QUESTION_POLICY')?></p>
                    </label>
                </div>

                <div class="comment-modal__button">
                    <button class="submit" type="submit"><?=GetMessage('POPUP_QUESTION_SUBMIT')?></button>
                </div>
            </form>
            <?endif?>
        </div>
    </div>
</div>

<script>
    $('.modal-content .phone').mask('+7(000) 000-00-00');
</script>