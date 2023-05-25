<script id="order-payment-tpl" type="text/html">
    <div class="placement-item">
        <h2 class="title text-left">Способ оплаты</h2>

        <div class="placement-radio">
            <div class="placement-radio__row" v-for="paySystem in result.PAY_SYSTEM" :key="paySystem.ID">
                <label class="radio">
                    <input class="radio__input" type="radio"
                           :id="'ID_PAY_SYSTEM_ID_'+paySystem.ID"
                           :value="paySystem.ID"
                           name="PAY_SYSTEM_ID"
                           :checked="paySystem.CHECKED == 'Y'"
                           @change="$root.sendRequest()"
                    >
                    <span class="radio__box"></span>
                    <span class="pay-system-name">{{paySystem.NAME}}</span>
                </label>
            </div>
        </div>

        <div class="placement-bonus" v-if="result.LOGICTIM_BONUS">
            <div class="placement-bonus__check">
                <p class="placement-bonus__title">Использовать бонусы ORENSHAL CLUB </p>

                <label class="check">
                    <input id="placementBonus" class="check__input" type="checkbox" v-model="$root.useBonus">
                    <span class="check__box"></span>
                </label>

            </div>

            <div class="placement-bonus__container" ref="bonusContainer" :style="{'height': $root.useBonus ? $refs.box.clientHeight+'px' : ''}">
                <div class="placement-bonus__box" ref="box">

                    <p class="placement-bonus__available">
                        Доступно бонусов: <span>{{result.LOGICTIM_BONUS.USER_BONUS}} баллов</span>
                    </p>

                    <p class="placement-bonus__text">
                        Списать можно до {{result.LOGICTIM_BONUS.MAX_BONUS}} бонусов для этого заказа
                    </p>

                    <div class="placement-bonus__writeoff">
                        <input type="text" placeholder="Введите сумму" v-model="$root.result.LOGICTIM_BONUS.PAY_BONUS">
                        <button class="submit" @click.stop.prevent="$root.sendRequest()">Списать</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>