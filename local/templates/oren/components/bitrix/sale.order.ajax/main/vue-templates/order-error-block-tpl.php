<script id="order-error-block-tpl" type="text/html">
    <div class="order-error-block" v-if="errors.length">
        <div class="order-error-message" v-for="message in errors">
            {{message}}
        </div>
    </div>
</script>