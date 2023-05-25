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
<div class="bonus-row">
    <?

    $arClass = array(
        'order-1',
        'order-3 order-xl-2',
        'order-2 order-xl-3',
        'order-4',
        );

    foreach ($arResult['ITEMS'] as $key=>$arItem)
    {
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <div class="bonus-col <?=$arClass[$key]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
            <div class="bonus-icon">
                <svg width="25" height="22" viewBox="0 0 25 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M16.1983 19.8554C19.3253 19.3525 23.9409 13.3703 23.9409 10.2466C23.9409 7.83785 22.2534 6.11719 19.9704 6.11719C18.0348 6.11719 16.4463 7.67888 15.9998 8.89667C15.5532 7.67888 13.9647 6.11719 12.0292 6.11719C9.74609 6.11719 8.05859 7.83785 8.05859 10.2466C8.05859 13.3703 12.6745 19.3525 15.8012 19.8554C15.8667 19.8695 15.9331 19.8784 15.9998 19.8819C16.0646 19.8642 16.1313 19.8553 16.1983 19.8554V19.8554Z"
                        stroke="#E6E2D9" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                    <path
                        d="M11.2415 19.379C15.0634 18.7644 20.7047 11.4528 20.7047 7.63495C20.7047 4.69092 18.6422 2.58789 15.8518 2.58789C13.4861 2.58789 11.5446 4.49663 10.9989 5.98504C10.4531 4.49663 8.51156 2.58789 6.14591 2.58789C3.35547 2.58789 1.29297 4.69092 1.29297 7.63495C1.29297 11.4528 6.93467 18.7644 10.7562 19.379C10.8362 19.3962 10.9173 19.4071 10.9989 19.4114C11.0781 19.3898 11.1597 19.3789 11.2415 19.379V19.379Z"
                        stroke="#877569" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div class="bonus-name">
                <p><?=$arItem['NAME']?></p>
            </div>
            <div class="bonus-description">
                <p><?=$arItem['PREVIEW_TEXT']?></p>
            </div>
        </div>
        <?
    }
    ?>
</div>
