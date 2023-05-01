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
<div class="newsletter" id="footer-subscribe-form">
    <?
    $frame = $this->createFrame("footer-subscribe-form", false)->begin();
    ?>
    <h3 class="newsletter-title"><?=GetMessage('subscribe_footer_title')?></h3>

    <div class="newsletter-box">
        <form action="<?= $arResult["FORM_ACTION"] ?>">
            <input type="hidden" name="OK" value="Y">
            <input type="text" class="newsletter-input" placeholder="<?=GetMessage('subscribe_email_placeholder')?>" name="sf_EMAIL" value="<?=$arResult["EMAIL"]?>">
            <div class="newsletter-button">
                <button class="subscribe" type="submit"><?=GetMessage('subscribe_submit_text')?></button>
            </div>
        </form>
    </div>

    <p class="newsletter-consent"><?=GetMessage('subscribe_consent_text')?></p>
    <?
    $frame->end();
    ?>
</div>