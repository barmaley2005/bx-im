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
<ul class="header-desctop__menu">
    <?
    $n=0;
    foreach ($arResult as $arItem)
    {
        ?>
        <li>
            <a<?if ($n == 0):?> id="catalog"<?endif?> href="<?=$arItem['LINK']?>"><?=$arItem['TEXT']?></a>
        </li>
        <?
        $n++;
    }
    ?>
</ul>
