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
<div class="modal-dialog">
    <div class="container">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header__img">
                    <img src="/local/templates/oren/img/gallery/img-7.jpg" alt="">
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="shares-container">
                    <div class="shares-item">
                        <h3 class="shares-title">Оплачивайте покупку по частям</h3>
                        <div class="shares-content">
                            <p>Долями позволяет разделить сумму покупки на 4 равных части без комиссий и переплат. Получайте
                                заказы сразу, а платите за них постепенно.</p>
                        </div>

                        <div class="shares-row">
                            <div class="shares-col">
                                <div class="shares-col__item">
                                    <div class="shares-col__head">
                                        <div class="shares-col__icon">
                                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <rect width="40" height="40" rx="6" fill="#FFEDF9" />
                                                <path
                                                    d="M18.7877 19.5431V12.6503C18.7877 11.8767 17.7986 11.5538 17.3422 12.1785L11.1284 20.6826C10.7422 21.211 11.1197 21.9542 11.7741 21.9542H16.211C16.6527 21.9542 17.0108 22.3123 17.0108 22.754L17.0108 29.3482C17.0108 30.1315 18.0202 30.4478 18.4673 29.8046L24.7261 20.7993C25.0947 20.269 24.7152 19.5431 24.0694 19.5431H22.0701"
                                                    stroke="#313131" stroke-width="2.39928" stroke-linecap="round" stroke-linejoin="round" />
                                                <path
                                                    d="M27.0008 8.1001L28.2719 11.029L31.2008 12.3001L28.2719 13.5712L27.0008 16.5001L25.7297 13.5712L22.8008 12.3001L25.7297 11.029L27.0008 8.1001Z"
                                                    stroke="#877569" stroke-width="2.39928" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <p class="shares-col__title">Покупайте сразу все</p>
                                    </div>
                                    <p class="shares-col__content">
                                        Можно не откладывать деньги на то, что давно хотели, и купить уже сейчас
                                    </p>
                                </div>
                                <div class="shares-col__item">
                                    <div class="shares-col__head">
                                        <div class="shares-col__icon">
                                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <rect width="40" height="40" rx="6" fill="#EDF6FF" />
                                                <path
                                                    d="M16.4214 10.8999C12.7678 12.3796 10.1992 15.8853 10.1992 19.9742C10.1992 25.4008 14.723 29.7999 20.3034 29.7999C24.5849 29.7999 28.2444 27.2103 29.716 23.554C29.7446 23.483 29.7723 23.4116 29.7992 23.3398"
                                                    stroke="#313131" stroke-width="2.39928" stroke-linecap="round" />
                                                <path
                                                    d="M21.4454 10.2142C25.6333 11.0657 28.9349 14.3673 29.7864 18.5552C29.8342 18.7902 29.7681 18.9982 29.6035 19.1734C29.4275 19.3606 29.14 19.5002 28.8015 19.5002H21.7001C21.0375 19.5002 20.5004 18.9631 20.5004 18.3005V11.1991C20.5004 10.8605 20.64 10.5731 20.8272 10.3971C21.0024 10.2325 21.2104 10.1664 21.4454 10.2142Z"
                                                    stroke="#877569" stroke-width="2.39928" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <p class="shares-col__title">Распределяйте расходы</p>
                                    </div>
                                    <p class="shares-col__content">
                                        Оплачивайте покупки частями — по 25% каждые две недели с зарплаты
                                    </p>
                                </div>
                            </div>
                            <div class="shares-col">
                                <div class="shares-col__img">
                                    <img src="/local/templates/oren/img/gallery/img-6.jpg" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shares-item">
                        <h3 class="shares-title">Как работает оплата Долями в ORENSHAL</h3>
                        <div class="shares-list">
                            <div class="shares-list__item">
                                <h4 class="shares-list__title">Добавляйте товары в корзину</h4>
                                <div class="shares-list__content">
                                    <p>Выбирайте любые понравившиеся товары, сборка заказа начнётся сразу после оформления и оплаты
                                        первых 25% стоимости.</p>
                                </div>
                            </div>
                            <div class="shares-list__item">
                                <h4 class="shares-list__title">4 небольших платежа</h4>
                                <div class="shares-list__content">
                                    <p>Долями – просто ещё один способ оплаты: сервис автоматически будет списывать по 1/4 от
                                        стоимости покупки каждые 2 недели.</p>
                                </div>
                            </div>
                            <div class="shares-list__item">
                                <h4 class="shares-list__title">Без переплат и скрытых условий</h4>
                                <div class="shares-list__content">
                                    <p>Это не кредит и не рассрочка: вы заплатите ровно ту сумму, которая указана в корзине при
                                        оформлении заказа.</p>
                                </div>
                            </div>
                            <div class="shares-list__item">
                                <h4 class="shares-list__title">Без анкет и ожидания</h4>
                                <div class="shares-list__content">
                                    <p>Нужно просто ввести ФИО, дату рождения, номер телефона и данные банковской карты.</p>
                                    <p>Отказаться от долями-заказа так же просто, как и от обычного. Все деньги вернутся вам на карту.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="shares-item">
                        <h3 class="shares-title">Как оформить заказ</h3>
                        <div class="shares-content">
                            <p>1. Соберите корзину на общую сумму от 1 000 ₽ до 70 000 ₽.</p>
                            <p>2. При оформлении заказа выбирайте «оплата Долями».</p>
                            <p>3. Спишем первый платёж и отправим вам заказ. Остальное — точно по графику.</p>
                        </div>
                    </div>
                </div>

                <a href="" class="shares-rules">Правила использования сервиса оплаты по частям «Долями»</a>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>