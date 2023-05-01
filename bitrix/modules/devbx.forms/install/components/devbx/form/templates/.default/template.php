<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);

if ($arParams['USE_BOOTSTRAP'] == 'Y')
{
    $this->addExternalCss('/bitrix/css/main/bootstrap.css');
}

if (empty($arParams['MSG_BUTTON_SUBMIT_TEXT']))
    $arParams['MSG_BUTTON_SUBMIT_TEXT'] = Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_SUBMIT_BUTTON_NAME_DEFAULT');

if (empty($arParams['MSG_SUCCESS']))
    $arParams['MSG_SUCCESS'] = Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_MSG_SUCCESS_DEFAULT');
?>

<?if ($arResult['SUCCESS']):?>
<div class="alert alert-success"><?=$arParams['MSG_SUCCESS']?></div>
<?else:?>
<form action="<?=POST_FORM_ACTION_URI?>" method="post">

    <?foreach ($arResult['HIDDEN_FIELDS'] as $ar):?>
    <input type="hidden" name="<?=$ar['NAME']?>" value="<?=$ar['VALUE']?>">
    <?endforeach?>

    <?foreach ($arResult['ERRORS'] as $ar):?>
        <div class="alert alert-danger"><?=$ar['text']?></div>
    <?endforeach?>

    <? foreach ($arResult['FIELDS'] as $arField): ?>
        <div class="form-group">
            <label><?= $arField['EDIT_FORM_LABEL'] ?: $arField['FIELD_NAME'] ?></label>
            <?= $arField["HTML"] ?>
        </div>
    <? endforeach; ?>

    <?if ($arResult['CAPTCHA_CODE']):?>
        <div class="row">
            <div class="form-group col-md-3 captcha-image">
                <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" style="position: absolute;bottom:0;" />
            </div>

            <div class="form-group col-md-4 captcha-word">
                <label><?=Loc::getMessage('DEVBX_FORMS_COMPONENT_FORM_LABEL_CAPTCHA')?></label>
                <input type="text" name="captcha_word" maxlength="50" value="">
            </div>
        </div>
    <?endif?>

    <div class="form-group">
        <button type="submit" class="btn btn-primary"><?=$arParams['SUBMIT_BUTTON_NAME']?></button>
    </div>
</form>
<?endif?>