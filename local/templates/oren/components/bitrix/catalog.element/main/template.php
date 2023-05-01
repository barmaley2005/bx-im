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
<section class="section product">
    <div class="container">
        <div class="product-container">
            <div class="gallery-wrap">
                <div id="gallery-top-wrap" class="gallery-top-wrap">
                    <div class="gallery-top gallery-top-swiper">
                        <div class="swiper-wrapper">
                        <?
                        foreach ($arResult['MORE_PHOTO'] as $arPhoto)
                        {
                            ?>
                            <div class="swiper-slide">
                                <img src="<?=$arPhoto['SRC']?>" alt="" loading="lazy">
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
                                <div class="bestseller-head__info" <?if (!$arResult['DISPLAY_PRICE']['DISCOUNT']):?> style="display:none;" <?endif?>>
                                    <p> -<?=$arResult['DISPLAY_PRICE']['PERCENT']?>%</p>
                                </div>
                            </div>

                            <div class="bestseller-head__like" data-item-id="<?=$arResult['ID']?>" data-action="favorite">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z" />
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
                                foreach ($arResult['MORE_PHOTO'] as $arPhoto)
                                {
                                    ?>
                                    <div class="swiper-slide">
                                        <img src="<?=$arPhoto['PREVIEW_SRC']?>" alt="" loading="lazy">
                                    </div>
                                    <?
                                }
                                ?>
                            </div>

                        </div>

                        <div class="gallery-thumbs-navigation">
                            <div class="gallery-thumbs-next gallery-next">
                                <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L8.29289 8.29289C8.68342 8.68342 9.31658 8.68342 9.70711 8.29289L17 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="gallery-thumbs-prev gallery-prev">
                                <svg width="18" height="10" viewBox="0 0 18 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17 9L9.70711 1.70711C9.31658 1.31658 8.68342 1.31658 8.29289 1.70711L1 9"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="product-info">
                <h1 class="product-info__title"><?=$arResult['NAME']?></h1>

                <!-- Если есть скидка добавить класс _new для product-price-->

                <?

                if (!empty($arResult['OFFERS']))
                {

                }

                ?>

                <div class="product-price<?if ($arResult['DISPLAY_PRICE']['DISCOUNT']):?> _new<?endif?>">
                    <p class="product-price__new"><?=$arResult['DISPLAY_PRICE']['PRINT_PRICE']?></p>
                    <p class="product-price__old"><?=$arResult['DISPLAY_PRICE']['PRINT_BASE_PRICE']?></p>

                    <button class="product-price__bonus">+200 баллов</button>
                </div>

                <?
                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/element/dolami.php');
                ?>

                <?
                if (!empty($arResult['OFFERS']))
                {
                    $arSelOffer = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']];
                ?>
                <div class="product-box">
                    <?
                    foreach ($arResult['TREE_PROP_VALUES'] as $propCode=>$values)
                    {
                        $arProp = $arResult["TREE_PROP"][$propCode];

                        $curValue = $arSelOffer['PROPERTIES'][$propCode]['VALUE'];

                        if ($arProp['USER_TYPE'] == 'directory')
                        {
                            ?>
                            <div class="product-box__item">
                                <p class="product-box__title"><?=$arProp['NAME']?>:
                                    <span class="product-box__name"><?=$value[$curValue]['UF_NAME']?></span>
                                </p>

                                <div class="product-box__check">
                                    <?
                                    foreach ($values as $value)
                                    {
                                        ?>
                                        <div class="product-box__col">
                                            <label class="radio">
                                                <input class="radio__input" type="radio"
                                                       value="<?=$value['UF_NAME']?>" name="radio"
                                                       <?if ($value['UF_XML_ID'] == $curValue):?> checked<?endif?>
                                                >
                                                <span class="radio__box"><img src="<?=$value['UF_FILE']['SRC']?>" alt=""></span>
                                            </label>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <?
                        } else {
                            ?>
                            <div class="product-box__item">
                                <p class="product-box__title"><?=$arProp['NAME']?>:</p>

                                <div class="calculation-size">
                                    <?
                                    foreach ($values as $value)
                                    {
                                        ?>
                                        <label class="radio">
                                            <input class="radio__input" type="radio"
                                                   value="<?=$value?>" name="tree_<?=$propCode?>"
                                                   <?if ($value == $curValue):?> checked<?endif?>
                                            >
                                            <div class="radio__box">
                                                <p class="radio__text"><?=$value?></p>
                                            </div>
                                        </label>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                            <?
                        }
                    }
                    ?>
                </div>
                <?
                }
                ?>

                <div id="product-button" class="product-button">
                    <div class="price-box">
                        <div class="price-minus">
                            <svg width="6" height="3" viewBox="0 0 6 3" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0.2 2.2V0.96H5.44V2.2H0.2Z" />
                            </svg>
                        </div>
                        <input class="price-input" type="text" value="1">
                        <div class="price-plus"> <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                                      xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.12 9.24V0.759999H5.38V9.24H4.12ZM0.4 5.6V4.42H9.1V5.6H0.4Z" />
                            </svg> </div>
                    </div>

                    <button class="product-button__add">
                        Добавить в корзину
                    </button>

                    <div class="product-button__like">
                        <div class="bestseller-head__like">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <?
                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/element/delivery.php');
                ?>
            </div>
        </div>

        <div class="product-footer">

            <div class="product-footer__col">
                <div id="accordeon" class="accordeon">
                    <?if ($arResult['STYLIST']):?>
                    <div class="accordeon-item">
                        <div class="accordeon-header">
                            <div class="accordeon-header__content">
                                <h3 class="accordeon-header__title">
                                    Советы стилиста
                                </h3>
                            </div>
                            <div class="accordeon-header__arrow">
                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordeon-body">
                            <div class="accordeon-content">
                                <div class="accordeon-user">
                                    <?if (is_array($arResult['STYLIST']['AVATAR'])):?>
                                    <div class="accordeon-user__img">
                                        <img src="<?=$arResult['STYLIST']['AVATAR']['SRC']?>" alt="">
                                    </div>
                                    <?endif;?>
                                    <div class="accordeon-user__text">
                                        <p class="accordeon-user__name"><?=$arResult['STYLIST']['STYLIST_NAME']?></p>
                                        <p class="accordeon-user__post"><?=$arResult['STYLIST']['STYLIST_POSITION']?> </p>

                                    </div>
                                </div>
                                <div class="accordeon-description">
                                    <?=$arResult['STYLIST']['DETAIL_TEXT']?>
                                </div>
                            </div>
                        </div>
                    </div> <!-- item -->
                    <?endif?>
                    <?if ($arResult['DETAIL_TEXT']):?>
                    <div class="accordeon-item">
                        <div class="accordeon-header">
                            <div class="accordeon-header__content">
                                <h3 class="accordeon-header__title">
                                    <?=GetMessage('CATALOG_DETAIL_TEXT_TITLE')?>
                                </h3>
                            </div>
                            <div class="accordeon-header__arrow">
                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordeon-body">
                            <div class="accordeon-content">
                                <div class="accordeon-description">
                                    <?if ($arResult['PROPERTIES']['ARTNUMBER']['VALUE']):?>
                                    <p><?=GetMessage('CATALOG_ARTICLE_TITLE')?>: <?=$arResult['PROPERTIES']['ARTNUMBER']['VALUE']?></p>
                                    <?endif?>
                                    <?=$arResult['DETAIL_TEXT']?>
                                </div>
                            </div>
                        </div>
                    </div> <!-- item -->
                    <?endif?>
                    <?if ($arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE'] || $arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE']):?>
                    <div class="accordeon-item">
                        <div class="accordeon-header">
                            <div class="accordeon-header__content">
                                <h3 class="accordeon-header__title">
                                    <?=GetMessage('CATALOG_SOSTAV_I_UHOD_TITLE')?>
                                </h3>
                            </div>
                            <div class="accordeon-header__arrow">
                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordeon-body">
                            <div class="accordeon-content">
                                <div class="accordeon-info">
                                    <?if ($arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE']):?>
                                    <div class="accordeon-info__col">
                                        <h4 class="accordeon-info__subtitle"><?=GetMessage('CATALOG_SOSTAV_TITLE')?></h4>
                                        <div class="accordeon-info__text">
                                            <p><?=$arResult['DISPLAY_PROPERTIES']['SOSTAV']['DISPLAY_VALUE']?></p>
                                        </div>
                                    </div>
                                    <?endif?>
                                    <?if ($arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE']):?>
                                    <div class="accordeon-info__col">
                                        <h4 class="accordeon-info__subtitle"><?=GetMessage('CATALOG_REKOMENDACII_PO_UHODU')?></h4>
                                        <div class="accordeon-info__text">
                                            <?=$arResult['DISPLAY_PROPERTIES']['UHOD']['DISPLAY_VALUE']?>
                                        </div>
                                    </div>
                                    <?endif?>
                                </div>
                            </div>
                        </div>
                    </div> <!-- item -->
                    <?endif?>
                    <div class="accordeon-item">
                        <div class="accordeon-header">
                            <div class="accordeon-header__content">
                                <h3 class="accordeon-header__title">
                                    Отзывы
                                </h3>
                                <?
                                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/element/review_rating.php');
                                ?>
                            </div>
                            <div class="accordeon-header__arrow">
                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M1 1L6.29289 6.29289C6.68342 6.68342 7.31658 6.68342 7.70711 6.29289L13 1"
                                          stroke="#877569" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                        <div class="accordeon-body">
                            <div class="accordeon-content">
                                <?
                                $GLOBALS['arrProductReview'] = array('=UF_PRODUCT_ID'=>$arResult['ID']);
                                ?>
                                <?$APPLICATION->IncludeComponent(
                                    "devbx:form.result.list",
                                    "detail-reviews",
                                    Array(
                                        "AJAX_MODE" => "N",
                                        "AJAX_OPTION_ADDITIONAL" => "",
                                        "AJAX_OPTION_HISTORY" => "N",
                                        "AJAX_OPTION_JUMP" => "N",
                                        "AJAX_OPTION_STYLE" => "Y",
                                        "CREATED_DATE_FORMAT" => "d.m.Y",
                                        "DISPLAY_BOTTOM_PAGER" => "N",
                                        "DISPLAY_FIELDS" => array("ID", "ACTIVE", "CREATED_DATE", "MODIFIED_DATE", "UF_PRODUCT_ID", "UF_NAME", "UF_CITY", "UF_EMAIL", "UF_COMMENT", "UF_RECOMMEND"),
                                        "DISPLAY_TOP_PAGER" => "N",
                                        "FILTER_NAME" => "arrProductReview",
                                        "FORM_ID" => "2",
                                        "MODIFIED_DATE_FORMAT" => "d.m.Y",
                                        "ONLY_ACTIVE_RESULTS" => "Y",
                                        "PAGER_BASE_LINK_ENABLE" => "N",
                                        "PAGER_DESC_NUMBERING" => "N",
                                        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                        "PAGER_SHOW_ALL" => "N",
                                        "PAGER_SHOW_ALWAYS" => "N",
                                        "PAGER_TEMPLATE" => ".default",
                                        "PAGER_TITLE" => "Результаты",
                                        "RESULTS_COUNT" => "20",
                                        "SORT_BY1" => "CREATED_DATE",
                                        "SORT_BY2" => "ID",
                                        "SORT_ORDER1" => "DESC",
                                        "SORT_ORDER2" => "ASC"
                                    )
                                );?>
                            </div>
                        </div>
                    </div> <!-- item -->
                </div>
            </div>

            <div class="product-footer__col">
                <?
                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/element/gift.php');
                include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'include/catalog/element/review_recommend.php');
                ?>
            </div>
        </div>

    </div>
</section>