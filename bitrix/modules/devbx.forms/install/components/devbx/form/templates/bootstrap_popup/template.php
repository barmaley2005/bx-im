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
/** @var CDevBxFormsForm $component */

$this->setFrameMode(true);

if (empty($arParams['MODAL_TITLE']))
    $arParams['MODAL_TITLE'] = GetMessage('DEVBX_FORMS_COMPONENT_FORM_MODAL_TITLE_DEFAULT');

if (empty($arParams['CLOSE_BUTTON_NAME']))
    $arParams['CLOSE_BUTTON_NAME'] = GetMessage('DEVBX_FORMS_COMPONENT_FORM_CLOSE_BUTTON_NAME_DEFAULT');

if (empty($arParams['SUBMIT_BUTTON_NAME']))
    $arParams['SUBMIT_BUTTON_NAME'] = GetMessage('DEVBX_FORMS_COMPONENT_FORM_SUBMIT_BUTTON_NAME_DEFAULT');

if (empty($arParams['SUCCESS_TEXT']))
    $arParams['SUCCESS_TEXT'] = GetMessage('DEVBX_FORMS_COMPONENT_FORM_SUCCESS_TEXT_DEFAULT');

$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'devbx.form');
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'devbx.form');

?>
<? if ($arResult['ACTION'] != CDevBxFormsForm::defaultFormAction): ?>
    <? if ($arResult['SUCCESS']): ?>
        <div class="modal-header">
            <h5 class="modal-title"><?= $arParams['MODAL_TITLE'] ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-success"><?= $arParams['SUCCESS_TEXT'] ?></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    data-dismiss="modal"><?= $arParams['CLOSE_BUTTON_NAME'] ?></button>
        </div>
    <? else: ?>
        <form action="<?= POST_FORM_ACTION_URI ?>" method="post">
            <? foreach ($arResult['HIDDEN_FIELDS'] as $ar): ?>
                <input type="hidden" name="<?= $ar['NAME'] ?>" value="<?= $ar['VALUE'] ?>">
            <? endforeach ?>
            <input type="hidden" name="template" value="<?= $signedTemplate ?>">
            <input type="hidden" name="parameters" value="<?= $signedParams ?>">
            <div class="modal-header">
                <h5 class="modal-title"><?= $arParams['MODAL_TITLE'] ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <? foreach ($arResult['ERRORS'] as $ar): ?>
                    <div class="alert alert-danger"><?= $ar['text'] ?></div>
                <? endforeach ?>

                <? foreach ($arResult['FIELDS'] as $arField): ?>
                    <div class="form-group">
                        <label><?= $arField['EDIT_FORM_LABEL'] ?></label>
                        <?= $arField["HTML"] ?>
                    </div>
                <? endforeach; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-dismiss="modal"><?= $arParams['CLOSE_BUTTON_NAME'] ?></button>
                <button type="submit" class="btn btn-primary"
                        data-submit><?= $arParams['SUBMIT_BUTTON_NAME'] ?></button>
            </div>
        </form>
    <? endif ?>
<? else: ?>
    <?
    $obName = 'ob' . preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId('ajax-form'));

    $uniqueId = md5($this->randString());

    ?>
    <button id="<?= $uniqueId ?>" class="btn btn-primary">Отправить</button>
    <script>
        var <?=$obName?> = new JCDevBxPopupForm({
            signedTemplate: '<?=CUtil::JSEscape($signedTemplate)?>',
            signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
            uniqueId: '<?=CUtil::JSEscape($uniqueId)?>',
            ajaxUrl: '<?=CUtil::JSEscape($component->getPath() . '/ajax.php')?>',
            actionVariable: '<?=CUtil::JSEscape($arParams['ACTION_VARIABLE'])?>',
            siteId: '<?=CUtil::JSEscape(SITE_ID)?>',
            hiddenFields: <?=json_encode($arResult['HIDDEN_FIELDS'])?>
        });
    </script>
<? endif ?>