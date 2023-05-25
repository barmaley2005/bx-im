<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<script id="order-tpl" type="text/html">
    <div id="bx-soa-order">
        <section class="section placement">
            <div class="container">
                <h1 class="title text-left">Оформление заказа</h1>

                <p class="placement-account" v-if="!result.IS_AUTHORIZED">
                    <a href="" data-action="showAuthForm">Войдите</a> в свой аккаунт и мы заполним форму за вас. Нет аккаунта? Мы создадим
                    его автоматически
                    в ходе оформления заказа.

                    <error-block :result="result" block="AUTH"></error-block>
                </p>

                <form action="<?= POST_FORM_ACTION_URI ?>" class="form-placement" @submit.stop.prevent method="POST"
                      name="ORDER_FORM" id="bx-soa-order-form">
                    <?
                    echo bitrix_sessid_post();

                    if ($arResult['PREPAY_ADIT_FIELDS'] <> '') {
                        echo $arResult['PREPAY_ADIT_FIELDS'];
                    }
                    ?>
                    <input type="hidden" name="<?= $arParams['ACTION_VARIABLE'] ?>" value="saveOrderAjax">
                    <input type="hidden" name="location_type" value="code">
                    <input type="hidden" name="BUYER_STORE" id="BUYER_STORE" value="<?= $arResult['BUYER_STORE'] ?>">

                    <div class="basket-container">
                        <div class="goods">
                            <div class="placement-container">

                                <error-block :result="result" block="PROPERTY"></error-block>

                                <order-prop-group :ref="setGroupRef" v-for="group in propGroups" :group="group"
                                                  :key="group.ID"></order-prop-group>

                                <error-block :result="result" block="DELIVERY"></error-block>
                                <order-delivery :result="result"></order-delivery>

                                <error-block :result="result" block="PAY_SYSTEM"></error-block>
                                <order-payment :result="result"></order-payment>
                                <order-comment :result="result"></order-comment>

                            </div>
                        </div>

                        <order-total :result="result"></order-total>
                    </div>
                </form>
            </div>
        </section>

        <modal-dialog v-if="showModal" :title="modalTitle" :content="modalContent" @close="showModal = false">
        </modal-dialog>
    </div>

</script>
