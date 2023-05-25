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

\Bitrix\Main\UI\Extension::load("ui.vue3");

$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

?>
<div class="modal-dialog modal-dialog-centered" id="vue-authorization">
</div>

<script id="vue-auth-form-tpl" type="text/html">
    <div class="modal-content">
        <div class="modal-col">
            <div class="modal-col__img">
                <img src="<?= SITE_TEMPLATE_PATH ?>/img/registration/img-2.jpg" alt="">
            </div>
        </div>
        <form class="modal-col modal-form">
            <div class="modal-col__head">
                <button type="button" class="modal-col__close" data-bs-dismiss="modal" aria-label="<?=GetMessage('AUTH_MODAL_CLOSE')?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round"
                              stroke-linejoin="round"/>
                        <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="modal-col__content">
                <div class="modal-col_title">
                    <h3 class="modal-col__title"><?=GetMessage('AUTH_TITLE')?></h3>
                </div>

                <div class="modal-col__box">
                    <div class="placement-inputs">
                        <div class="placement-inputs__col" ref="rowPhone" :class="{'_error': errors.phone}">
                            <label class="placement-inputs__label" for=""><?=GetMessage('AUTH_PHONE')?></label>
                            <input type="text" class="input phone" placeholder="<?=GetMessage('AUTH_PHONE')?>" ref="phone" v-model="phone">
                            <span class="placement-inputs__info" v-html="errors.phone ? errors.phone : ''"></span>
                        </div>

                        <div class="placement-inputs__col" ref="rowCode" :class="{'_error': errors.code}">
                            <label class="placement-inputs__label" for=""><?=GetMessage('AUTH_SMS_CODE')?></label>
                            <input type="email" class="input" placeholder="<?=GetMessage('AUTH_SMS_CODE')?>" ref="code" v-model="code" maxlength="4">
                            <span class="placement-inputs__info" v-html="errors.code ? errors.code : ''"></span>
                        </div>
                    </div>
                </div>

                <div class="comment-modal__button modal-col_time _timer">
                    <button class="submit" @click.stop.prevent="sendSMSCode"><?=GetMessage('AUTH_GET_SMS_CODE')?></button>
                    <p class="modal-col__time" v-if="showTimer">
                        <?=GetMessage('AUTH_RETRY_COUNT_DOWN')?> <span class="modal-col__count">{{countDownLabel}}</span>
                    </p>
                </div>
            </div>

            <a href="" class="modal-col__link" data-action="showRegistrationForm"><?=GetMessage('AUTH_REG_BUTTON')?></a>
        </form>
    </div>
</script>

<script>
    (function() {

        BX.message(<?= CUtil::PhpToJSObject($messages) ?>);

        let vueForm = createVueAuthForm('#vue-authorization');

        window.authForm = vueForm;

        $(vueForm.$el.closest('.modal')).on('hidden.bs.modal', function() {
            vueForm.$.appContext.app.unmount();
            vueForm = null;

            delete window.authForm;

            if (document.getElementById('vue-auth-form-tpl'))
                document.getElementById('vue-auth-form-tpl').remove();
        });
    })();
</script>