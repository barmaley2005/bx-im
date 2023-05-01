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

?>
<section class="main-slider">
    <div class="swiper main-swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <!-- Slides -->
            <?
            foreach ($arResult['ITEMS'] as $arItem)
            {
                $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                ?>
                <div class="swiper-slide" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                    <div class="main-slider__img">
                        <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="">
                    </div>
                    <div class="container">
                        <div class="main-slider__content">
                            <h2 class="main-slider__title"><?=$arItem['NAME']?></h2>
                            <div class="button-box _white">
                                <?if ($arItem['PROPERTIES']['BUTTON_TEXT']['VALUE']):?>
                                    <a href="<?=$arItem['PROPERTIES']['BUTTON_LINK']['VALUE']?>" class="button"><?=$arItem['PROPERTIES']['BUTTON_TEXT']['VALUE']?></a>
                                <?endif?>
                                <svg class="button-bg" width="238" height="68" viewBox="0 0 238 68" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M63.8598 11.0954C63.8598 11.0954 87.0187 6.81025 136.7 6.81025C166.972 6.81025 237 14.3733 237 37.169C237 61.644 171.494 67 117.487 67C65.1837 67 0.999788 62.4177 1 37.169C1.00032 -0.818731 136.7 1.0142 136.7 1.0142"
                                        stroke-linecap="round" class="button-bg__elem" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <?
            }
            ?>
        </div>


    </div>
    <div class="main-slider__container">
        <div class="container">
            <!-- If we need navigation buttons -->
            <div class="_prev main-slider__prev">
                <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1 7H22M1 7L9.26 13M1 7L9.26 1" stroke="white" />
                </svg>
                <svg class="_prev-bg" width="62" height="38" viewBox="0 0 62 38" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.6877 11.4119C13.4172 11.2007 21.6782 5.99753 37.6665 5.99753C42.8059 5.99753 61 7.76776 61 19.3161C61 31.7152 45.11 37 31.3795 37C18.082 37 0.999954 30.1534 1 19.3161C1.00008 1 33.6689 1 35.5 1"
                        stroke="white" stroke-linecap="round" class="_prev-bg__elem"></path>
                </svg>
            </div>
            <div class="_next main-slider__next">
                <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 7H0M21 7L12.74 13M21 7L12.74 1" stroke="white" />
                </svg>

                <svg class="_next-bg" width="62" height="38" viewBox="0 0 62 38" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12.6877 11.4119C13.4172 11.2007 21.6782 5.99753 37.6665 5.99753C42.8059 5.99753 61 7.76776 61 19.3161C61 31.7152 45.11 37 31.3795 37C18.082 37 0.999954 30.1534 1 19.3161C1.00008 1 33.6689 1 35.5 1"
                        stroke="white" stroke-linecap="round" class="_next-bg__elem"></path>
                </svg>
            </div>

        </div>
    </div>

    <div class="arrow">
        <div class="container">
            <a href="#arrow" class="arrow-btn">
                <svg width="20" height="29" viewBox="0 0 20 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 28L10 -8.34465e-07M10 28L19 16.9867M10 28L1 16.9867" stroke="white" />
                </svg>
            </a>
        </div>
    </div>
</section>