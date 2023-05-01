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

\Bitrix\Main\Loader::includeModule('devbx.core');

$arTree = \DevBx\Core\Menu::getTreeMenu($arResult);
?>
<div class="sidebar-catalog__box">
    <?
    foreach ($arTree as $arItem)
    {
        $hasChildSelected = false;

        if (is_array($arItem['ITEMS']))
        {
            foreach ($arItem['ITEMS'] as $arSubItem)
            {
                $hasChildSelected |= $arSubItem['SELECTED'];
            }
        }

        ?>
        <div class="menu-item">
            <div class="menu-head">
                <p>
                    <a class="menu-head__link<?if (!$hasChildSelected && $arItem['SELECTED']):?> _active<?endif?>" href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
                </p>
                <div class="menu-head__arrow">
                    <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                    </svg>
                </div>
            </div>
            <?if (is_array($arItem['ITEMS'])):?>
            <div class="menu-container">
                <ul class="menu-box">
                    <?
                    foreach ($arItem['ITEMS'] as $arSubItem)
                    {
                        ?>
                        <li<?if ($arSubItem['SELECTED']):?> class="_active"<?endif?>>
                            <a href="<?=$arSubItem['LINK']?>"><?=$arSubItem['TEXT']?></a>
                        </li>
                        <?
                    }
                    ?>
                </ul>
            </div>
            <?endif;?>
        </div>
        <?
    }
    ?>
    <div class="menu-item">
        <div class="menu-head">
            <p>
                <a class="menu-head__link" href="<?=SITE_DIR?>catalog/"><?=GetMessage('MENU_ITEM_CATALOG_LINK_TEXT')?></a>
            </p>
            <div class="menu-head__arrow">
                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                </svg>
            </div>
        </div>
    </div>
</div>