<script id="order-delivery-tpl" type="text/html">
    <div class="placement-item delivery-items">
        <h2 class="title text-left">Способ получения</h2>

        <div class="placement-radio">
            <div class="placement-radio__row" v-for="delivery in result.DELIVERY" :key="delivery.ID">
                <label class="radio">
                    <input class="radio__input" type="radio"
                           :id="'ID_DELIVERY_ID_'+delivery.ID"
                           :value="delivery.ID"
                           name="DELIVERY_ID"
                           :checked="delivery.CHECKED == 'Y'"
                           @change="$root.sendRequest()"
                    >
                    <span class="radio__box"></span>
                    <span class="delivery-name">{{delivery.NAME}}</span>
                    <span class="delivery-description" v-html="delivery.DESCRIPTION"></span>
                </label>
            </div>
        </div>
    </div>
</script>