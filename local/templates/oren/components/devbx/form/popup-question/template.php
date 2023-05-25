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
            <h5 class="modal-title" id="exampleModalLabel">Задать вопрос</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <?if ($arResult['SUCCESS']):?>
            <div class="comment-modal__container">
                <h2 class="title">Форма отправлена</h2>
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
                            <label class="placement-inputs__label" for="">Ваше имя</label>
                            <input required type="text" class="input" placeholder="Ваше имя" name="UF_NAME" value="<?=$arResult['FIELDS']['UF_NAME']['VALUE']?>">
                            <span class="placement-inputs__info">Поле заполнено некорректно</span>
                        </div>
                    </div>
                    <div class="comment-modal__item">
                        <div class="placement-inputs__col">
                            <label class="placement-inputs__label" for="">Номер телефона*</label>
                            <input required type="text" class="input phone" placeholder="Номер телефона*" name="UF_PHONE" value="<?=$arResult['FIELDS']['UF_PHONE']['VALUE']?>">
                            <span class="placement-inputs__info">Поле заполнено некорректно</span>
                        </div>
                    </div>

                    <div class="comment-modal__item order-1">
                        <div class="placement-textarea">
                            <label class="placement-textarea__label" for="">Комментарий</label>
                            <textarea required placeholder="Комментарий" name="UF_QUESTION"><?=$arResult['FIELDS']['UF_QUESTION']['VALUE']?></textarea>
                        </div>
                    </div>
                </div>

                <div class="comment-modal__consent">
                    <label class="check">
                        <input class="check__input" type="checkbox" checked required>
                        <span class="check__box"></span>
                        <p>
                            Я даю своё согласие на <a href="<?=SITE_DIR?>/customers/privacy/">обработку персональных данных</a> и согласен с
                            <a href="<?=SITE_DIR?>/customers/privacy/">условиями политики конфиденциальности</a>
                        </p>
                    </label>
                </div>

                <div class="comment-modal__button">
                    <button class="submit" type="submit">Задать вопрос</button>
                </div>
            </form>
            <?endif?>
        </div>
    </div>
</div>

<script>
    $('.modal-content .phone').mask('+7(000) 000-00-00');
</script>