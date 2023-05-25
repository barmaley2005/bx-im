<?php
if (CSite::InDir(SITE_DIR.'personal/cart/') || CSite::InDir(SITE_DIR.'personal/order/'))
    return;
?>
</div>
<?
$APPLICATION->ShowViewContent('PERSONAL_FOOTER_CONTENT');
?>
</div>

