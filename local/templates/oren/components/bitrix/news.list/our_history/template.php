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
<section class="section">
    <div class="container">
        <div class="scarf-title">
            <h2 class="title"><?=$arResult['NAME']?></h2>
            <h3 class="subtitle"><?=$arResult['DESCRIPTION']?></h3>
        </div>
    </div>

    <div class="history-slider">
        <div class="history-slider__year">
            <div class="container">
                <div class="swiper swiper-year">
                    <!-- Additional required wrapper -->
                    <div class="swiper-wrapper">
                        <!-- Slides -->
                        <?
                        foreach ($arResult['ITEMS'] as $arItem)
                        {
                            $uniqId = $arItem['ID'].'_1';

                            $this->AddEditAction($uniqId, $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                            $this->AddDeleteAction($uniqId, $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                            ?>
                            <div class="swiper-slide" id="<?=$this->GetEditAreaId($uniqId);?>">
                                <div class="swiper-year__box">
                                    <p class="swiper-year__count">

                                    </p>
                                    <div class="swiper-year__dote"></div>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="history-slider__info">
            <div class="container">
                <div class="history-slider__container">
                    <div class="swiper swiper-history">
                        <!-- Additional required wrapper -->
                        <div class="swiper-wrapper">
                            <!-- Slides -->
                            <?
                            foreach ($arResult['ITEMS'] as $arItem)
                            {
                                $uniqId = $arItem['ID'].'_2';

                                $this->AddEditAction($uniqId, $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                                $this->AddDeleteAction($uniqId, $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                                ?>
                                <div class="swiper-slide history__slide" id="<?=$this->GetEditAreaId($uniqId);?>">
                                    <div class="swiper-history__slide">
                                        <div class="swiper-history__col">
                                            <?
                                            if (count($arItem['PHOTO'])>1)
                                            {
                                                ?>
                                                <div class="swiper mySwiper2">
                                                    <div class="swiper-wrapper">
                                                        <?
                                                        foreach ($arItem['PHOTO'] as $arFile)
                                                        {
                                                            ?>
                                                            <div class="swiper-slide history-img">
                                                                <img src="<?=$arFile['SRC']?>" loading="lazy" />
                                                            </div>
                                                            <?
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="swiper mySwiper">
                                                    <div class="swiper-wrapper">
                                                        <?
                                                        foreach ($arItem['PHOTO'] as $arFile)
                                                        {
                                                            ?>
                                                            <div class="swiper-slide">
                                                                <img src="<?=$arFile['SRC']?>" loading="lazy" />
                                                            </div>
                                                            <?
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <?
                                            } else {
                                                ?>
                                                <div class="swiper-history__img history-img">
                                                    <img src="<?=$arItem['PHOTO'][0]['SRC']?>" alt="" loading="lazy">
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>

                                        <div class="swiper-history__col">
                                            <h3 class="swiper-history__year">
                                                <span class="swiper-history__count"><?=$arItem['PROPERTIES']['YEAR']['VALUE']?></span>
                                                <?=GetMessage('OUR_HISTORY_YEAR')?>
                                            </h3>

                                            <div class="swiper-history__content">
                                                <h4 class="swiper-history__title"><?=$arItem['NAME']?></h4>
                                                <div class="swiper-history__text">
                                                    <?=$arItem['PREVIEW_TEXT']?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="swiper-history__subyear"></p>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>

                    <div class="_prev2 swiper-history-prev">
                        <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 6H17M1 6L7.29333 11M1 6L7.29333 1" stroke-width="0.75" />
                        </svg>
                    </div>
                    <div class="_next2 swiper-history-next">
                        <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 6L-2.98023e-07 6M16 6L9.70667 1M16 6L9.70667 11" stroke-width="0.75" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>