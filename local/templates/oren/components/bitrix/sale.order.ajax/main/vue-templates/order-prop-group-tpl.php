<script id="order-prop-group-tpl" type="text/html">

    <div class="placement-item">
        <h2 class="title text-left">{{group.NAME}}</h2>

        <div class="placement-inputs">
            <component class="placement-inputs__col" v-for="property in visibleProps" :key="property.ID" :is="property.component" :property="property">
            </component>
        </div>
    </div>

</script>
