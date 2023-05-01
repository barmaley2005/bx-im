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

if (empty($arResult))
    return;

$arTree = \DevBx\Core\Menu::getTreeMenu($arResult);

$arCatalog = array_shift($arTree);

?>
<div class="header-catalog">
    <div class="container">
        <div class="header-catalog__container">
            <div class="header-catalog__item _show">
                <div class="header-catalog__title">
                    <a href="<?=$arCatalog['LINK']?>"><?=$arCatalog['TEXT']?></a>
                    <div class="header-catalog__arrow">
                        <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                        </svg>
                    </div>
                </div>
                <div class="header-catalog__box">
                    <div class="header-catalog__row">
                        <div class="header-catalog__col">
                            <div class="menu">
                                <?
                                foreach ($arCatalog['ITEMS'] as $arItem)
                                {
                                    ?>
                                    <div class="menu-item">
                                        <div class="menu-head">
                                            <p>
                                                <a class="menu-head__link" href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
                                            </p>
                                            <div class="menu-head__arrow">
                                                <svg width="14" height="8" viewBox="0 0 14 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13 1L7 7L1 1" stroke="#877569" stroke-linecap="round" />
                                                </svg>
                                            </div>
                                        </div>
                                        <?
                                        if (!empty($arItem['ITEMS']) && is_array($arItem['ITEMS']))
                                        {
                                            ?>
                                        <div class="menu-container">
                                            <ul class="menu-box">
                                                <?
                                                foreach ($arItem['ITEMS'] as $arSubItem)
                                                {
                                                    ?>
                                                    <li>
                                                        <a href="<?=$arSubItem['LINK']?>"><?=$arSubItem['TEXT']?></a>
                                                    </li>
                                                    <?
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                            <?
                                        }
                                        ?>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="" class="header-catalog__img">
            <img src="/local/templates/oren/img/header-catalog/img-1.jpg" alt="">
        </a>

        <ul class="header-catalog__links">
            <?
            foreach ($arTree as $arItem)
            {
                ?>
                <li>
                    <a href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
                </li>
                <?
            }
            ?>
        </ul>
    </div>
</div>
