<?php
?>
<div class="container" id="<?= $containerId ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Быстрый просмотр</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.25 5.25L18.75 18.75" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M5.25 18.75L18.75 5.25" stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-body__container">
                    <div class="modal-body__row">
                        <div class="modal-body__col">
                            <?
                            include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/gallery.php');
                            ?>
                        </div>
                        <div class="modal-body__col">
                            <div class="product-info">
                                <h2 class="product-info__title"><?=$arResult['NAME']?></h2>
                                <?
                                include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/product_info.php');
                                ?>
                            </div>
                        </div>
                        <div class="modal-body__col">
                            <div class="modal-body__content">
                                <h4 class="modal-body__title">Описание</h4>
                                <h5 class="modal-body__subtitle">Артикул: П3 130184-07</h5>
                                <p class="modal-body__description">
                                    <?=$arResult['DETAIL_TEXT'] ?: $arResult['PREVIEW_TEXT']?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>

