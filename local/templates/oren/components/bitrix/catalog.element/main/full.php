<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
<section class="section product" id="<?= $containerId ?>">
    <div class="container">
        <div class="product-container">
            <?
            include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/gallery.php');
            ?>
            <div class="product-info">
                <h1 class="product-info__title"><?= $arResult['NAME'] ?></h1>
            <?
            include($_SERVER['DOCUMENT_ROOT'].$templateFolder.'/product_info.php');
            ?>
            </div>
        </div>

        <div class="product-footer">

            <div class="product-footer__col">
                <div id="accordeon" class="accordeon">
                    <? if ($arResult['STYLIST']): ?>
                        <div class="accordeon-item">
                            <div class="accordeon-header">
                                <div class="accordeon-header__content">
                                    <h3 class="accordeon-header__title">
                                        <?= GetMessage('CATALOG_STYLIST_ADVICE') ?>
                                    </h3>
                                </div>
                                <div class="accordeon-header__arrow">
                                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                            stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="accordeon-body">
                                <div class="accordeon-content">
                                    <div class="accordeon-user">
                                        <? if (is_array($arResult['STYLIST']['AVATAR'])): ?>
                                            <div class="accordeon-user__img">
                                                <img src="<?= $arResult['STYLIST']['AVATAR']['SRC'] ?>" alt="">
                                            </div>
                                        <?endif; ?>
                                        <div class="accordeon-user__text">
                                            <p class="accordeon-user__name"><?= $arResult['STYLIST']['STYLIST_NAME'] ?></p>
                                            <p class="accordeon-user__post"><?= $arResult['STYLIST']['STYLIST_POSITION'] ?> </p>

                                        </div>
                                    </div>
                                    <div class="accordeon-description">
                                        <?= $arResult['STYLIST']['DETAIL_TEXT'] ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- item -->
                    <?endif ?>
                    <? if ($arResult['DETAIL_TEXT']): ?>
                        <div class="accordeon-item">
                            <div class="accordeon-header">
                                <div class="accordeon-header__content">
                                    <h3 class="accordeon-header__title">
                                        <?= GetMessage('CATALOG_DETAIL_TEXT_TITLE') ?>
                                    </h3>
                                </div>
                                <div class="accordeon-header__arrow">
                                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                            stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="accordeon-body">
                                <div class="accordeon-content">
                                    <div class="accordeon-description">
                                        <? if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']): ?>
                                            <p><?= GetMessage('CATALOG_ARTICLE_TITLE') ?>
                                                : <?= $arResult['PROPERTIES']['ARTNUMBER']['VALUE'] ?></p>
                                        <?endif ?>
                                        <?= $arResult['DETAIL_TEXT'] ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- item -->
                    <?endif ?>
                    <? if ($arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE'] || $arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE']): ?>
                        <div class="accordeon-item">
                            <div class="accordeon-header">
                                <div class="accordeon-header__content">
                                    <h3 class="accordeon-header__title">
                                        <?= GetMessage('CATALOG_SOSTAV_I_UHOD_TITLE') ?>
                                    </h3>
                                </div>
                                <div class="accordeon-header__arrow">
                                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                            stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="accordeon-body">
                                <div class="accordeon-content">
                                    <div class="accordeon-info">
                                        <? if ($arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE']): ?>
                                            <div class="accordeon-info__col">
                                                <h4 class="accordeon-info__subtitle"><?= GetMessage('CATALOG_SOSTAV_TITLE') ?></h4>
                                                <div class="accordeon-info__text">
                                                    <p><?= $arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE'] ?></p>
                                                </div>
                                            </div>
                                        <?endif ?>
                                        <? if ($arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE']): ?>
                                            <div class="accordeon-info__col">
                                                <h4 class="accordeon-info__subtitle"><?= GetMessage('CATALOG_REKOMENDACII_PO_UHODU') ?></h4>
                                                <div class="accordeon-info__text">
                                                    <?= $arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE'] ?>
                                                </div>
                                            </div>
                                        <?endif ?>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- item -->
                    <?endif ?>
                    <div class="accordeon-item">
                        <div class="accordeon-header">
                            <div class="accordeon-header__content">
                                <h3 class="accordeon-header__title">
                                    <?= GetMessage('CATALOG_REVIEWS') ?>
                                </h3>
                                <?
                                include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/catalog/element/review_rating.php');
                                ?>
                            </div>
                            <div class="accordeon-header__arrow">
                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                        stroke="#877569" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="accordeon-body">
                            <div class="accordeon-content" data-entity="reviews">
                            </div>
                        </div>
                    </div> <!-- item -->
                </div>
            </div>

            <div class="product-footer__col">
                <?
                include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/catalog/element/gift.php');
                include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/catalog/element/review_recommend.php');
                ?>
            </div>
        </div>

    </div>
</section>

