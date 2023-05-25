<script id="order-total-tpl" type="text/html">
    <div class="design">
        <div class="design-container">
            <h2 class="design-title">Ваш заказ</h2>

            <div class="design-list">
                <div class="design-list__container">
                    <div class="design-list__row">
                        <p class="design-list__name">Товары ({{result.TOTAL.BASKET_POSITIONS}})</p>
                        <p class="design-list__count" v-html="result.TOTAL.PRICE_WITHOUT_DISCOUNT"></p>
                    </div>
                    <div class="design-list__row _discount" v-if="result.TOTAL.DISCOUNT_PRICE>0">
                        <p class="design-list__name">Скидка</p>
                        <p class="design-list__count" v-html="' - '+result.TOTAL.DISCOUNT_PRICE_FORMATED"></p>
                    </div>
                    <div class="design-list__row">
                        <p class="design-list__name">Доставка</p>
                        <p class="design-list__count" v-html="result.TOTAL.DELIVERY_PRICE_FORMATED"></p>
                    </div>
                    <div class="design-list__row _cashback" v-if="result.LOGICTIM_BONUS">
                        <p class="design-list__name">Кэшбэк</p>
                        <p class="design-list__count" v-html="result.LOGICTIM_BONUS.ADD_BONUS_FORMAT"></p>
                    </div>

                    <div class="design-list__row _cashback" v-if="result.LOGICTIM_BONUS && result.LOGICTIM_BONUS.PAY_BONUS_NO_POST>0">
                        <p class="design-list__name">Оплата бонусами</p>
                        <p class="design-list__count" v-html="' - '+result.LOGICTIM_BONUS.PAY_BONUS_FORMATED"></p>
                    </div>

                </div>
                <div class="design-list__footer">
                    <p class="design-list__text">Итого</p>
                    <p class="design-list__total" v-html="result.TOTAL.ORDER_TOTAL_PRICE_FORMATED"></p>
                </div>
            </div>

            <div class="design-button">
                <button class="submit" @click.stop.prevent="$root.saveOrder()">Оформить заказ</button>
            </div>

            <p class="design-info">Доступные способы доставки и оплаты можно выбрать при оформлении заказа</p>
        </div>

        <div class="certificate design-container">
            <h2 class="design-title">Промокод или подарочный сертификат</h2>

            <div class="certificate-box">
                <div class="certificate-input">
                    <input ref="coupon" type="text" placeholder="Введите код или номер" :value="enteredCouponValue" @change="couponChange($event.target.value)">
                </div>
                <div class="certificate-button">
                    <button class="submit" @click.stop.prevent="enterCoupon($refs.coupon.value)">ОК</button>
                </div>
            </div>
        </div>
    </div>
</script>
