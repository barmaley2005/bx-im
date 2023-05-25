<script id="order-comment-tpl" type="text/html">
    <div class="placement-item">
        <h2 class="title text-left"><?=GetMessage('ORDER_COMMENT')?></h2>

        <div class="placement-item__comment">
            <textarea placeholder="<?=GetMessage('ORDER_COMMENT')?>" name="ORDER_DESCRIPTION" id="orderDescription" v-model="description"></textarea>
        </div>
    </div>
</script>