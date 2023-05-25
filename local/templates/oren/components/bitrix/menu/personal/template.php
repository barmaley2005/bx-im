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
<menu class="account-block__menu-list">
    <?
    foreach ($arResult as $arItem)
    {
        ?>
        <li class="account-block__menu-item<?if ($arItem['SELECTED']):?> _active<?endif?>">
            <a href="<?=$arItem['LINK']?>" class="account-block__menu-link"><?=$arItem['TEXT']?></a>
        </li>
        <?
    }
    ?>
</menu>
