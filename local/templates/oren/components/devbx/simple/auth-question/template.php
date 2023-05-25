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
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <h5 class="modal-title" id="exampleModalLabel">Для оформления заказа необходимо авторизоваться</h5>
        </div>
        <div class="modal-footer">
            <button class="submit" data-bs-dismiss="modal" data-action="showAuthForm">Авторизоваться</button>
            <button class="view" data-bs-dismiss="modal" data-action="showRegistrationForm">Зарегистрироваться</button>
        </div>
    </div>
</div>
