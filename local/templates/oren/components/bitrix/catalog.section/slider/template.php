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

if (empty($arResult['ITEMS']))
    return;

$elementEdit = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT');
$elementDelete = CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE');
$elementDeleteParams = array('CONFIRM' => GetMessage('CT_BCS_TPL_ELEMENT_DELETE_CONFIRM'));

$containerId = 'slider'.'_'.md5($this->randString().$component->getAction());
?>
<section class="section bestseller" id="<?=$containerId?>">
    <div class="container">
        <?if ($arParams['SLIDER_TITLE']):?>
            <h2 class="title"><?=$arParams['SLIDER_TITLE']?></h2>
        <?endif?>

        <div class="bestseller-container">
            <div class="swiper bestseller-swiper">
                <!-- Additional required wrapper -->
                <div class="swiper-wrapper">
                    <!-- Slides -->

                <?

                foreach ($arResult['ITEMS'] as $arItem)
                {
                    $uniqueId = $arItem['ID'].'_'.md5($this->randString().$component->getAction());
                    $areaId = $this->GetEditAreaId($uniqueId);

                    $this->AddEditAction($uniqueId, $arItem['EDIT_LINK'], $elementEdit);
                    $this->AddDeleteAction($uniqueId, $arItem['DELETE_LINK'], $elementDelete, $elementDeleteParams);

                    ?>
                    <div class="swiper-slide">
                    <?
                    $APPLICATION->IncludeComponent(
                        'bitrix:catalog.item',
                        'main',
                        array(
                            'RESULT' => array(
                                'ITEM' => $arItem,
                                'AREA_ID' => $areaId,
                            ),
                            'PARAMS' => $arParams+array('DISABLE_JS'=>'Y'),
                        ),
                        $component,
                        array('HIDE_ICONS' => 'Y')
                    );
                    ?>
                    </div>
                        <?
                }
                ?>
                </div>

                <!-- If we need navigation buttons -->
                <div class="_prev2 bestseller-prev">
                    <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 6H17M1 6L7.29333 11M1 6L7.29333 1" stroke-width="0.75" />
                    </svg>
                </div>
                <div class="_next2 bestseller-next">
                    <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 6L-2.98023e-07 6M16 6L9.70667 1M16 6L9.70667 11" stroke-width="0.75" />
                    </svg>
                </div>

            </div>
        </div>

        <?if ($arParams['SLIDER_LINK']):?>
        <div class="bestseller-button">
            <div class="button-box">
                <a href="<?=$arParams['SLIDER_LINK']?>" class="button"><?=$arParams['SLIDER_LINK_TEXT'] ?: GetMessage('SLIDER_LINK_TEXT')?></a>
                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                        stroke-linecap="round" class="button-bg__elem" />
                </svg>
            </div>
        </div>
        <?endif?>
    </div>
</section>

<script>
    (function(){

        console.log($('#<?=$containerId?> .bestseller-swiper'));

        const swiper = new Swiper('#<?=$containerId?> .bestseller-swiper', {
            speed: 400,
            slidesPerView: 5,
            spaceBetween: 15,
            loop: true,
            navigation: {
                nextEl: '.bestseller-next',
                prevEl: '.bestseller-prev',
            },
            breakpoints: {
                // when window width is >= 320px
                320: {
                    spaceBetween: 10,
                    slidesPerView: "auto",
                },
                992: {
                    slidesPerView: 4,
                    spaceBetween: 10,
                },
                // when window width is >= 480px
                1200: {
                    slidesPerView: 4,
                },
                // when window width is >= 640px
                1400: {
                    slidesPerView: 5,
                }
            }
        });

        $("#<?=$containerId?> .bestseller-link").brazzersCarousel();
    })();
</script>