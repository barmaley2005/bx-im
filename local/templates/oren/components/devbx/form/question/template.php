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
    <h2 class="title">Форма отправлена</h2>
</div>
<?else:?>
<form action="<?=POST_FORM_ACTION_URI?>" class="buyers-form" method="post">
    <h2 class="title">Оставьте свою заявку</h2>

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
                <label class="placement-inputs__label" for="">Ваше имя</label>
                <input type="text" class="input" placeholder="Ваше имя" name="UF_NAME" value="<?=$arResult['FIELDS']['UF_NAME']['VALUE']?>">
                <span class="placement-inputs__info">Поле заполнено некорректно</span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-inputs__col">
                <label class="placement-inputs__label" for="">Ваш E-mail</label>
                <input type="email" class="input" placeholder="Ваш E-mail" name="UF_EMAIL" value="<?=$arResult['FIELDS']['UF_EMAIL']['VALUE']?>">
                <span class="placement-inputs__info">Поле заполнено некорректно</span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-inputs__col">
                <label class="placement-inputs__label" for="">Номер телефона</label>
                <input type="text" class="input phone" placeholder="Номер телефона" name="UF_PHONE" value="<?=$arResult['FIELDS']['UF_PHONE']['VALUE']?>">
                <span class="placement-inputs__info">Поле заполнено некорректно</span>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="select">
                <div class="select-head">
                    <label class="select__label" for="">Выберите тему обращения</label>
                    <input type="text" class="select-head__input" placeholder="Выберите тему обращения"
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
                            Заказ
                        </li>
                        <li>
                            Программа лояльности
                        </li>
                        <li>
                            Акции
                        </li>
                        <li>
                            Советы стилистов
                        </li>
                        <li>
                            Программа лояльности
                        </li>
                        <li>
                            Акции
                        </li>
                        <li>
                            Советы стилистов
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="buyers-form__item">
            <div class="placement-textarea">
                <label class="placement-textarea__label" for="">Ваш вопрос</label>
                <textarea placeholder="Ваш вопрос" name="UF_QUESTION"><?=$arResult['FIELDS']['UF_QUESTION']['VALUE']?></textarea>
            </div>
        </div>
    </div>

    <div class="buyers-form__footer">
        <div class="buyers-form__check">
            <label class="check">
                <input class="check__input" type="checkbox" checked name="UF_AGREE" value="1">
                <span class="check__box"></span>
                <div class="check-text">
                    <p>
                        Я даю своё согласие на <a href="<?=SITE_DIR?>customers/policy/">обработку персональных данных</a> и согласен с <a
                            href="<?=SITE_DIR?>customers/policy/">условиями политики конфиденциальности</a>
                    </p>
                </div>
            </label>

        </div>
        <div class="buyers-form__button">
            <button class="submit" type="submit">Задать вопрос</button>
        </div>
    </div>
</form>
<script>
    styleSelect('.buyers-form .select');
    $('.phone').mask('+7(000) 000-00-00');
</script>
<?endif?>