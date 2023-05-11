<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<!-- Если есть скидка добавить класс _new для product-price-->

<?

if (!empty($arResult['OFFERS'])) {

}

?>

<div class="product-price<? if ($arResult['DISPLAY_PRICE']['DISCOUNT']): ?> _new<? endif ?>" data-entity="price">
    <p class="product-price__new" data-entity="price-new"><?= $arResult['DISPLAY_PRICE']['PRINT_PRICE'] ?></p>
    <p class="product-price__old" data-entity="price-old"><?= $arResult['DISPLAY_PRICE']['PRINT_BASE_PRICE'] ?></p>

    <button class="product-price__bonus" data-entity="bonus"></button>
</div>

<?
include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/catalog/element/dolami.php');
?>

<?
$arSelOffer = false;

if (!empty($arResult['OFFERS'])) {
    $arSelOffer = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']];

    ?>
    <div class="product-box">
        <?
        foreach ($arResult['SKU_PROPS'] as $propCode => $skuProp) {
            $curValue = $arSelOffer['TREE']['PROP_' . $skuProp['ID']];

            if ($skuProp['USER_TYPE'] == 'directory') {
                ?>
                <div class="product-box__item" data-entity="tree_prop"
                     data-value="<?= $skuProp['ID'] ?>">
                    <p class="product-box__title"><?= $skuProp['NAME'] ?>:
                        <span
                            class="product-box__name"><?= $skuProp['VALUES'][$curValue]['NAME'] ?></span>
                    </p>

                    <div class="product-box__check">
                        <?
                        foreach ($skuProp['VALUES'] as $value) {
                            ?>
                            <div
                                class="product-box__col" <? if ($value['ID'] <= 0): ?> style="display: none;" <? endif ?>>
                                <label class="radio">
                                    <input class="radio__input" type="radio"
                                           value="<?= $value['ID'] ?>" name="tree_<?= $skuProp['ID'] ?>"
                                        <? if ($value['ID'] == $curValue): ?> checked<? endif ?>
                                    >
                                    <span class="radio__box"><img src="<?= $value['PICT']['SRC'] ?>"
                                                                  alt=""></span>
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
                <div class="product-box__item" data-entity="tree_prop"
                     data-value="<?= $skuProp['ID'] ?>">
                    <p class="product-box__title"><?= $skuProp['NAME'] ?>:</p>

                    <div class="calculation-size">
                        <?
                        foreach ($skuProp['VALUES'] as $value) {
                            ?>
                            <label
                                class="radio" <? if ($value['ID'] <= 0): ?> style="display: none;" <? endif ?>>
                                <input class="radio__input" type="radio"
                                       value="<?= $value['ID'] ?>" name="tree_<?= $skuProp['ID'] ?>"
                                    <? if ($value['ID'] == $curValue): ?> checked<? endif ?>
                                >
                                <div class="radio__box">
                                    <p class="radio__text"><?= $value['NAME'] ?></p>
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
            <svg width="6" height="3" viewBox="0 0 6 3" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M0.2 2.2V0.96H5.44V2.2H0.2Z"/>
            </svg>
        </div>
        <input class="price-input" type="text" value="1" data-entity="product-quantity">
        <div class="price-plus">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M4.12 9.24V0.759999H5.38V9.24H4.12ZM0.4 5.6V4.42H9.1V5.6H0.4Z"/>
            </svg>
        </div>
    </div>

    <?
    $basketProductId = $arSelOffer ? $arSelOffer['ID'] : $arResult['ID'];
    ?>

    <button class="product-button__add" data-entity="add2basket" data-action="add2basket"
            data-product-id="<?= $basketProductId ?>">
        <?= GetMessage('CATALOG_ADD_TO_BASKET') ?>
    </button>

    <div class="product-button__like">
        <div class="bestseller-head__like">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M8.1767 14.094C10.9562 13.647 15.0591 8.32951 15.0591 5.55291C15.0591 3.4118 13.5591 1.88232 11.5296 1.88232C9.80917 1.88232 8.39717 3.2705 8.00023 4.35298C7.60329 3.2705 6.19129 1.88232 4.47082 1.88232C2.44141 1.88232 0.941406 3.4118 0.941406 5.55291C0.941406 8.32951 5.04447 13.647 7.82376 14.094C7.88195 14.1066 7.94094 14.1145 8.00023 14.1176C8.05789 14.1019 8.11718 14.094 8.1767 14.094Z"/>
            </svg>
        </div>
    </div>
</div>

<?
include($_SERVER['DOCUMENT_ROOT'] . SITE_DIR . 'include/catalog/element/delivery.php');
?>
