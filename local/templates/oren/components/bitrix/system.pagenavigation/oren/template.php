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

if(!$arResult["NavShowAlways"])
{
    if ($arResult["NavRecordCount"] == 0 || ($arResult["NavPageCount"] == 1 && $arResult["NavShowAll"] == false))
        return;
}

$strNavQueryString = ($arResult["NavQueryString"] != "" ? $arResult["NavQueryString"]."&amp;" : "");
$strNavQueryStringFull = ($arResult["NavQueryString"] != "" ? "?".$arResult["NavQueryString"] : "");

?>

<ul class="paginationMy">
    <?
    if ($arResult["NavPageNomer"] > 1)
    {
        if($arResult["bSavePage"]) {
            $firstPage = $arResult["sUrlPath"] . '?' . $strNavQueryString . 'PAGEN_' . $arResult["NavNum"] . '=1';
            $prevPage = $arResult["sUrlPath"] . '?' . $strNavQueryString . 'PAGEN_' . $arResult["NavNum"] . '='.($arResult["NavPageNomer"]-1);
        } else {
            $firstPage = $strNavQueryStringFull;
            if ($arResult["NavPageNomer"] > 2)
            {
                $prevPage = $arResult["sUrlPath"] . '?' . $strNavQueryString . 'PAGEN_' . $arResult["NavNum"] . '='.($arResult["NavPageNomer"]-1);
            } else {
                $prevPage = $strNavQueryStringFull;
            }
        }

        ?>
    <li class="paginationMy-list">
        <a href="<?=$prevPage?>" class="paginationMy-link">
            <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 7H22M1 7L9.26 13M1 7L9.26 1" stroke="#877569" />
            </svg>
        </a>

        <svg class="paginationMy-bg" width="62" height="38" viewBox="0 0 62 38" fill="none"
             xmlns="http://www.w3.org/2000/svg">
            <path
                d="M12.6877 11.4119C13.4172 11.2007 21.6782 5.99753 37.6665 5.99753C42.8059 5.99753 61 7.76776 61 19.3161C61 31.7152 45.11 37 31.3795 37C18.082 37 0.999954 30.1534 1 19.3161C1.00008 1 33.6689 1 35.5 1"
                class="paginationMy-bg__elem" stroke-linecap="round" />
        </svg>

    </li>
    <?
    }
    ?>

    <?while($arResult["nStartPage"] <= $arResult["nEndPage"]):?>
        <?if ($arResult["nStartPage"] == $arResult["NavPageNomer"]):?>
            <li class="paginationMy-list _active">
                <a href="" class="paginationMy-link">
                    <?=$arResult["nStartPage"]?>
                </a>

                <svg class="paginationMy-bg" width="32" height="32" viewBox="0 0 32 32" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9 5.77267C9.36472 5.58768 11.3391 3.84341 19.3332 3.84341C21.9029 3.84341 31 5.39404 31 15.5098C31 26.3708 23.055 31 16.1898 31C9.54102 31 0.999977 25.0027 1 15.5098C1.00004 -0.534172 18.4177 1.04254 19.3332 1.04254"
                        class="paginationMy-bg__elem" stroke-linecap="round" />
                </svg>
            </li>
        <?elseif($arResult["nStartPage"] == 1 && $arResult["bSavePage"] == false):?>
            <li class="paginationMy-list">
                <a href="<?=$arResult["sUrlPath"]?><?=$strNavQueryStringFull?>" class="paginationMy-link">
                    <?=$arResult["nStartPage"]?>
                </a>

                <svg class="paginationMy-bg" width="32" height="32" viewBox="0 0 32 32" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9 5.77267C9.36472 5.58768 11.3391 3.84341 19.3332 3.84341C21.9029 3.84341 31 5.39404 31 15.5098C31 26.3708 23.055 31 16.1898 31C9.54102 31 0.999977 25.0027 1 15.5098C1.00004 -0.534172 18.4177 1.04254 19.3332 1.04254"
                        class="paginationMy-bg__elem" stroke-linecap="round" />
                </svg>
            </li>
        <?else:?>
            <li class="paginationMy-list">
                <a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["nStartPage"]?>" class="paginationMy-link">
                    <?=$arResult["nStartPage"]?>
                </a>

                <svg class="paginationMy-bg" width="32" height="32" viewBox="0 0 32 32" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9 5.77267C9.36472 5.58768 11.3391 3.84341 19.3332 3.84341C21.9029 3.84341 31 5.39404 31 15.5098C31 26.3708 23.055 31 16.1898 31C9.54102 31 0.999977 25.0027 1 15.5098C1.00004 -0.534172 18.4177 1.04254 19.3332 1.04254"
                        class="paginationMy-bg__elem" stroke-linecap="round" />
                </svg>
            </li>
        <?endif?>
        <?$arResult["nStartPage"]++?>
    <?endwhile?>

    <?
    if ($arResult['NavPageCount']>$arResult['nEndPage']+1)
    {
        ?>
        <li class="paginationMy-list">
            ...
        </li>

        <li class="paginationMy-list">
            <a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=$arResult["NavPageCount"]?>" class="paginationMy-link">
                <?=$arResult["NavPageCount"]?>
            </a>

            <svg class="paginationMy-bg" width="32" height="32" viewBox="0 0 32 32" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M9 5.77267C9.36472 5.58768 11.3391 3.84341 19.3332 3.84341C21.9029 3.84341 31 5.39404 31 15.5098C31 26.3708 23.055 31 16.1898 31C9.54102 31 0.999977 25.0027 1 15.5098C1.00004 -0.534172 18.4177 1.04254 19.3332 1.04254"
                    class="paginationMy-bg__elem" stroke-linecap="round" />
            </svg>
        </li>
        <?
    }
    ?>


    <?if($arResult["NavPageNomer"] < $arResult["NavPageCount"]):?>
    <li class="paginationMy-list">
        <a href="<?=$arResult["sUrlPath"]?>?<?=$strNavQueryString?>PAGEN_<?=$arResult["NavNum"]?>=<?=($arResult["NavPageNomer"]+1)?>" class="paginationMy-link">
            <svg width="22" height="14" viewBox="0 0 22 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M21 7H0M21 7L12.74 13M21 7L12.74 1" stroke="#877569" />
            </svg>
        </a>

        <svg class="paginationMy-bg" width="62" height="38" viewBox="0 0 62 38" fill="none"
             xmlns="http://www.w3.org/2000/svg">
            <path
                d="M12.6877 11.4119C13.4172 11.2007 21.6782 5.99753 37.6665 5.99753C42.8059 5.99753 61 7.76776 61 19.3161C61 31.7152 45.11 37 31.3795 37C18.082 37 0.999954 30.1534 1 19.3161C1.00008 1 33.6689 1 35.5 1"
                class="paginationMy-bg__elem" stroke-linecap="round" />
        </svg>

    </li>
    <?endif?>
</ul>
