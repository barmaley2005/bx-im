<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */
/** @var \CBitrixComponent $component */
$this->setFrameMode(true);
?>
<section class="section collections">
    <div class="container">
        <h2 class="title text-lg-left"><?=$arParams['BLOCK_TITLE']?></h2>
        <div class="subtitle-box text-center d-lg-none">
            <h3 class="subtitle"><?=GetMessage('YOUR_IMAGES_SLIDER_TITLE')?></h3>
        </div>
    </div>
    <div class="collections-container">
        <div class="container">
            <div class="collections-box">
                <div class="swiper collections-swiper">
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper">
                        <!-- Slides -->

                        <?
                        foreach ($arResult['ITEMS'] as $arItem)
                        {
                            ?>
                            <div class="swiper-slide">
                                <div class="collections-item">
                                    <div class="collections-head">
                                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__img">
                                            <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
                                        </a>

                                        <?
                                        foreach ($arItem['PRODUCTS'] as $arProduct)
                                        {
                                            ?>
                                            <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__label" style="left: <?=$arProduct['X']?>%; top: <?=$arProduct['Y']?>%;">
                                                <div class="collections-head__box">
                                                    <p class="collections-head__text">
                                                        <?=$arProduct['NAME']?> <span class="collections-head__price"><?=$arProduct['DISPLAY_PRICE']['PRINT_DISCOUNT_VALUE']?></span>
                                                    </p>
                                                </div>
                                            </a>
                                            <?
                                        }
                                        ?>

                                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-head__more">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.97243 10.9632H2.03125L2.36213 3.68384H9.64154L9.97243 10.9632Z" stroke="white"
                                                      stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                                <path
                                                    d="M4.34375 5.0077V2.69152C4.34375 2.25274 4.51805 1.83194 4.82832 1.52168C5.13858 1.21141 5.55938 1.03711 5.99816 1.03711C6.21542 1.03711 6.43056 1.0799 6.63128 1.16304C6.832 1.24619 7.01438 1.36805 7.16801 1.52168C7.32163 1.6753 7.4435 1.85768 7.52664 2.05841C7.60978 2.25913 7.65257 2.47426 7.65257 2.69152V5.0077"
                                                    stroke="white" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <p class="collections-head__look">
                                                <?=GetMessage('YOUR_IMAGES_SLIDER_VIEW_PRODUCTS')?>
                                            </p>
                                        </a>
                                    </div>
                                    <div class="collections-content">
                                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-name"><?=$arItem['NAME']?></a>
                                        <div class="collections-description">
                                            <p><?=$arItem['PREVIEW_TEXT']?></p>
                                        </div>
                                        <a href="<?=$arItem['DETAIL_PAGE_URL']?>" class="collections-more"><?=GetMessage('YOUR_IMAGES_SLIDER_READ_MORE')?></a>
                                    </div>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>

                    <!-- If we need navigation buttons -->
                    <div class="_prev2 collections-prev">
                        <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 6H17M1 6L7.29333 11M1 6L7.29333 1" stroke-width="0.75" />
                        </svg>
                    </div>
                    <div class="_next2 collections-next">
                        <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 6L-2.98023e-07 6M16 6L9.70667 1M16 6L9.70667 11" stroke-width="0.75" />
                        </svg>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?if ($arParams['SHOW_LINK'] == 'Y'):?>
    <div class="container">
        <div class="bestseller-button">
            <div class="button-box">
                <a href="<?=SITE_DIR?>your_images/" class="button"><?=GetMessage('YOUR_IMAGES_SLIDER_VIEW_ALL')?></a>
                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                        stroke-linecap="round" class="button-bg__elem" />
                </svg>
            </div>
        </div>
    </div>
    <?endif?>

</section>