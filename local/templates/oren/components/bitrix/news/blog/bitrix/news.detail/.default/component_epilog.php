<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CMain $APPLICATION */
/** @global \CUser $USER */
/** @global \CDatabase $DB */
/** @var \CBitrixComponent $this */
/** @var \CBitrixComponent $component */
/** @var string $epilogFile */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var array $templateData */

?>
<section class="article">
    <div class="container">
        <div class="article-head">
            <div class="article-head__left">
                <div class="article-head__content">
                    <?if ($templateData['HASH_TAG']):?>
                        <p class="article-head__tag">#<?=$templateData['HASH_TAG']?></p>
                    <?endif?>
                    <h1 class="article-head__title"><?=$templateData['NAME']?></h1>
                    <p class="article-head__date"><?=$templateData['DISPLAY_ACTIVE_FROM']?></p>

                    <div class="article-head__text">
                        <?=$templateData['PREVIEW_TEXT']?>
                    </div>
                </div>
            </div>
            <?if (is_array($templateData['DETAIL_PICTURE'])):?>
                <div class="article-head__right">
                    <div class="article-head__img">
                        <img src="<?=$templateData['DETAIL_PICTURE']['SRC']?>" alt="">
                    </div>
                </div>
            <?endif?>

        </div>

        <div class="article-body">
            <div class="article-content">
                <div class="article-content__container">
                    <?=$templateData['DETAIL_TEXT']?>
                </div>
            </div>

            <div class="article-sidebar">
                <div class="article-sidebar__container">
                    <?=$APPLICATION->ShowViewContent('BLOG_LATEST_ITEMS')?>
                </div>
            </div>
        </div>
    </div>
</section>

