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
<?if ($arResult['SUCCESS']):?>
<div class="buyers-form">
    <h2 class="title"><?=GetMessage('QUESTION_SUCCESS_TITLE')?></h2>
</div>
<?else:?>
<form action="<?=POST_FORM_ACTION_URI?>" class="buyers-form" method="post">
    <h2 class="title"><?=GetMessage('QUESTION_TITLE')?></h2>

    <div class="buyers-form__box">

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

        <div class="buyers-form__item">
            <div class="placement-inputs__col">
                <label class="placement-inputs__label" for=""><?=GetMessage('QUESTION_NAME')?></label>
                <input type="text" class="input" placeholder="<?=GetMessage('QUESTION_NAME')?>" name="UF_NAME" value="<?=$arResult['FIELDS']['UF_NAME']['VALUE']?>">
                <span class="placement-inputs__info"><?=GetMessage('QUESTION_INVALID_FIELD')?></span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-inputs__col">
                <label class="placement-inputs__label" for=""><?=GetMessage('QUESTION_EMAIL')?></label>
                <input type="email" class="input" placeholder="<?=GetMessage('QUESTION_EMAIL')?>" name="UF_EMAIL" value="<?=$arResult['FIELDS']['UF_EMAIL']['VALUE']?>">
                <span class="placement-inputs__info"><?=GetMessage('QUESTION_INVALID_FIELD')?></span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-inputs__col">
                <label class="placement-inputs__label" for=""><?=GetMessage('QUESTION_PHONE')?></label>
                <input type="text" class="input phone" placeholder="<?=GetMessage('QUESTION_PHONE')?>" name="UF_PHONE" value="<?=$arResult['FIELDS']['UF_PHONE']['VALUE']?>">
                <span class="placement-inputs__info"><?=GetMessage('QUESTION_INVALID_FIELD')?></span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="select">
                <div class="select-head">
                    <label class="select__label" for=""><?=GetMessage('QUESTION_SELECT_SUBJECT')?></label>
                    <input type="text" class="select-head__input" placeholder="<?=GetMessage('QUESTION_SELECT_SUBJECT')?>"
                           name="UF_SUBJECT" value="<?=$arResult['FIELDS']['UF_SUBJECT']['VALUE']?>">
                    <div class="select-arrow">
                        <svg width="10" height="7" viewBox="0 0 10 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 2L4.29289 5.29289C4.68342 5.68342 5.31658 5.68342 5.70711 5.29289L9 2"
                                  stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <div class="select-container">
                    <ul class="select-list">
                        <li class="_active">
                            <?=GetMessage('QUESTION_SUBJECT_1')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_2')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_3')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_4')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_5')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_6')?>
                        </li>
                        <li>
                            <?=GetMessage('QUESTION_SUBJECT_7')?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-textarea">
                <label class="placement-textarea__label" for=""><?=GetMessage('QUESTION_YOUR_QUESTION')?></label>
                <textarea placeholder="<?=GetMessage('QUESTION_YOUR_QUESTION')?>" name="UF_QUESTION"><?=$arResult['FIELDS']['UF_QUESTION']['VALUE']?></textarea>
            </div>
        </div>
    </div>

    <div class="buyers-form__footer">
        <div class="buyers-form__check">
            <label class="check">
                <input class="check__input" type="checkbox" checked name="UF_AGREE" value="1">
                <span class="check__box"></span>
                <div class="check-text">
                    <p><?=GetMessage('QUESTION_POLICY')?></p>
                </div>
            </label>

        </div>
        <div class="buyers-form__button">
            <button class="submit" type="submit"><?=GetMessage('QUESTION_SUBMIT')?></button>
        </div>
    </div>
</form>
<script>
    styleSelect('.buyers-form .select');
    $('.phone').mask('+7(000) 000-00-00');
</script>
<?endif?>