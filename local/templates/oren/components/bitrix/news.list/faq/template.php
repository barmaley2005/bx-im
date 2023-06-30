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
<div id="accordeon" class="accordeon _faq">
    <?
    foreach ($arResult['SECTIONS'] as $arSection)
    {
        ?>
        <div class="accordion-item" id="<?=$this->GetEditAreaId($arSection['ID']);?>">
            <div class="accordion-header collapsed" data-bs-toggle="collapse" aria-expanded="false">
                <div class="accordeon-header__content">
                    <h3 class="accordeon-header__title">
                        <?=$arSection['NAME']?>
                    </h3>
                </div>
                <div class="accordeon-header__arrow">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M7.75 9.18934L3.78033 5.21967C3.48744 4.92678 3.01256 4.92678 2.71967 5.21967C2.42678 5.51256 2.42678 5.98744 2.71967 6.28033L7.21967 10.7803C7.51256 11.0732 7.98744 11.0732 8.28033 10.7803L12.7803 6.28033C13.0732 5.98744 13.0732 5.51256 12.7803 5.21967C12.4874 4.92678 12.0126 4.92678 11.7197 5.21967L7.75 9.18934Z" />
                    </svg>
                </div>
            </div>
            <div class="accordion-collapse collapse">
                <div class="accordгon-content">
                    <div class="accordion">
                        <?
                        $n=0;
                        foreach ($arSection['ITEMS'] as $arItem)
                        {
                            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

                            $show = $n==0;
                            ?>
                            <div class="accordion-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                                <div class="accordion-header collapsed" data-bs-toggle="collapse" aria-expanded="false">
                                    <h3 class="accordion-header__title"><?=$arItem['NAME']?></h3>
                                    <div class="accordion-header__arrow">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M7.75 9.18934L3.78033 5.21967C3.48744 4.92678 3.01256 4.92678 2.71967 5.21967C2.42678 5.51256 2.42678 5.98744 2.71967 6.28033L7.21967 10.7803C7.51256 11.0732 7.98744 11.0732 8.28033 10.7803L12.7803 6.28033C13.0732 5.98744 13.0732 5.51256 12.7803 5.21967C12.4874 4.92678 12.0126 4.92678 11.7197 5.21967L7.75 9.18934Z"
                                                fill="#877569" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <?=$arItem['PREVIEW_TEXT']?>
                                    </div>
                                </div>
                            </div>
                            <?
                            $n++;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div> <!-- item -->
        <?
    }
    ?>
</div>