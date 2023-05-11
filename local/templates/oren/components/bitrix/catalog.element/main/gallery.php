<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
<div class="gallery-wrap">
    <div id="gallery-top-wrap" class="gallery-top-wrap" data-entity="gallery">
        <div class="gallery-top gallery-top-swiper">
            <div class="swiper-wrapper">
                <?
                foreach ($arResult['MORE_PHOTO'] as $arPhoto) {
                    ?>
                    <div class="swiper-slide">
                        <img src="<?= $arPhoto['SRC'] ?>" alt="" loading="lazy">
                    </div>
                    <?
                }
                ?>
            </div>
        </div>
        <div class="zoom-container"></div>
        <div class="gallery-top__info _discount">
            <div class="bestseller-head">

                <div class="bestseller-head__left">
                    <div
                        class="bestseller-head__info" <? if (!$arResult['DISPLAY_PRICE']['DISCOUNT']): ?> style="display:none;" <?endif ?>>
                        <p> -<?= $arResult['DISPLAY_PRICE']['PERCENT'] ?>%</p>
                    </div>
                </div>

                <div class="bestseller-head__like" data-item-id="<?= $arResult['ID'] ?>"
                     data-action="favorite">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <div class="gallery-thumbs-wrap">
        <div class="gallery-thumbs-inner">
            <div class="gallery-thumbs gallery-thumbs-swiper">
                <div class="swiper-wrapper" id="img-container">
                    <?
                    foreach ($arResult['MORE_PHOTO'] as $arPhoto) {
                        ?>
                        <div class="swiper-slide">
                            <img src="<?= $arPhoto['PREVIEW_SRC'] ?>" alt="" loading="lazy">
                        </div>
                        <?
                    }
                    ?>
                </div>

            </div>

            <div class="gallery-thumbs-navigation">
                <div class="gallery-thumbs-next gallery-next">
                    <svg width="18" height="10" viewBox="0 0 18 10" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1 1L8.29289 8.29289C8.68342 8.68342 9.31658 8.68342 9.70711 8.29289L17 1"
                            stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="gallery-thumbs-prev gallery-prev">
                    <svg width="18" height="10" viewBox="0 0 18 10" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17 9L9.70711 1.70711C9.31658 1.31658 8.68342 1.31658 8.29289 1.70711L1 9"
                            stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

