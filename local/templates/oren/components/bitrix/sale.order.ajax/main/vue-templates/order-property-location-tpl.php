<script id="order-property-location-tpl" type="text/html">
    <div v-if="locationsInitialized">
        <label ref="label" class="placement-inputs__label _show" :for="htmlId">{{property.NAME}}</label>
        <div ref="row" :data-property-id-row="property.ID" :style="{'visibility': locationsInitialized ? '' : 'hidden'}" v-html="currentLocation.output.HTML"></div>
        <input type="hidden" name="RECENT_DELIVERY_VALUE" :value="currentLocation.lastValue">

        <component v-if="currentLocation.showAlt" :is="propAltLocation.component" :property="propAltLocation"></component>

        <error-block :result="$root.result" block="REGION"></error-block>
    </div>
</script>
