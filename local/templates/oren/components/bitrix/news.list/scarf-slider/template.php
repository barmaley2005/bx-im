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
<section class="section scarf">
    <div class="container">
        <div class="scarf-title">
            <h2 class="title"><?=$arResult['NAME']?></h2>
            <h3 class="subtitle"><?=$arResult['DESCRIPTION']?></h3>
        </div>

        <div class="scarf-box">
            <div class="swiper scarf-swiper">
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

                            <div class="scarf-item">
                                <div class="scarf-head">
                                    <div class="scarf-head__img">
                                        <img src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="" loading="lazy">
                                    </div>
                                </div>
                                <div class="scarf-content">
                                    <p><?=$arItem['PREVIEW_TEXT']?></p>
                                </div>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                </div>

                <!-- If we need navigation buttons -->
                <div class="_prev2 scarf-prev">
                    <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 6H17M1 6L7.29333 11M1 6L7.29333 1" stroke-width="0.75" />
                    </svg>
                </div>
                <div class="_next2 scarf-next">
                    <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M16 6L-2.98023e-07 6M16 6L9.70667 1M16 6L9.70667 11" stroke-width="0.75" />
                    </svg>
                </div>

                <!-- If we need scrollbar -->
                <div class="_scrollbar scarf-scrollbar"></div>
            </div>
        </div>
    </div>
</section>