<script id="order-property-string-tpl" type="text/html">
    <div :data-property-id-row="property.ID">
        <label ref="label" class="placement-inputs__label" :for="htmlId">{{property.NAME}}</label>
        <input ref="input" type="text" class="input"
               :id="htmlId"
               :placeholder="property.NAME" :name="'ORDER_PROP_'+property.ID" @focus="onFocus" @blur="onBlur"
               v-model="value"
        >
    </div>
</script>