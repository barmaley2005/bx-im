<script id="order-total-tpl" type="text/html">
    <div class="design">
        <div class="design-container">
            <h2 class="design-title"><?=GetMessage('ORDER_TOTAL_TITLE')?></h2>

            <div class="design-list">
                <div class="design-list__container">
                    <div class="design-list__row">
                        <p class="design-list__name">{{$Bitrix.Loc.getMessage('ORDER_TOTAL_PRODUCTS', {'#NUM#': result.TOTAL.BASKET_POSITIONS})}}</p>
                        <p class="design-list__count" v-html="result.TOTAL.PRICE_WITHOUT_DISCOUNT"></p>
                    </div>
                    <div class="design-list__row _discount" v-if="result.TOTAL.DISCOUNT_PRICE>0">
                        <p class="design-list__name"><?=GetMessage('ORDER_TOTAL_DISCOUNT')?></p>
                        <p class="design-list__count" v-html="' - '+result.TOTAL.DISCOUNT_PRICE_FORMATED"></p>
                    </div>
                    <div class="design-list__row">
                        <p class="design-list__name"><?=GetMessage('ORDER_TOTAL_DELIVERY')?></p>
                        <p class="design-list__count" v-html="result.TOTAL.DELIVERY_PRICE_FORMATED"></p>
                    </div>
                    <div class="design-list__row _cashback" v-if="result.LOGICTIM_BONUS">
                        <p class="design-list__name"><?=GetMessage('ORDER_TOTAL_CASHBACK')?></p>
                        <p class="design-list__count" v-html="result.LOGICTIM_BONUS.ADD_BONUS_FORMAT"></p>
                    </div>

                    <div class="design-list__row _cashback" v-if="result.LOGICTIM_BONUS && result.LOGICTIM_BONUS.PAY_BONUS_NO_POST>0">
                        <p class="design-list__name"><?=GetMessage('ORDER_TOTAL_PAY_BONUS')?></p>
                        <p class="design-list__count" v-html="' - '+result.LOGICTIM_BONUS.PAY_BONUS_FORMATED"></p>
                    </div>

                </div>
                <div class="design-list__footer">
                    <p class="design-list__text"><?=GetMessage('ORDER_TOTAL_TOTAL')?></p>
                    <p class="design-list__total" v-html="result.TOTAL.ORDER_TOTAL_PRICE_FORMATED"></p>
                </div>
            </div>

            <div class="design-button">
                <button class="submit" @click.stop.prevent="$root.saveOrder()"><?=GetMessage('ORDER_TOTAL_SUBMIT')?></button>
            </div>

            <p class="design-info"><?=GetMessage('ORDER_TOTAL_INFO')?></p>
        </div>

        <div class="certificate design-container">
            <h2 class="design-title"><?=GetMessage('ORDER_TOTAL_PROMO_OR_CERT')?></h2>

            <div class="certificate-box">
                <div class="certificate-input">
                    <input ref="coupon" type="text" placeholder="<?=GetMessage('ORDER_TOTAL_INPUT_PROMO')?>" :value="enteredCouponValue" @change="couponChange($event.target.value)">
                </div>
                <div class="certificate-button">
                    <button class="submit" @click.stop.prevent="enterCoupon($refs.coupon.value)"><?=GetMessage('ORDER_TOTAL_BTN_OK')?></button>
                </div>
            </div>
        </div>
    </div>
</script>
