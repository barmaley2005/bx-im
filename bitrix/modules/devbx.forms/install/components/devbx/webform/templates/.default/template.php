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

use Bitrix\Main\Localization\Loc;

if (is_array($arResult['ERRORS']) && !empty($arResult['ERRORS']))
{
    foreach ($arResult['ERRORS'] as $err)
    {
        ShowError($err['text']);
    }

    return;
}

CJSCore::Init(['devbx_forms_vue_webform', 'devbx_core_utils']);

$areaId = $this->GetEditAreaId('WebForm');

$arJSData = array(
    'WEB_FORM' => $arResult['WEB_FORM'],
    'CULTURE' => $arResult['CULTURE'],
    'SID' => $arResult['WEB_FORM_SID'],
    'FORM_CLASS' => '',
    'DEBUG' => true,
    'ADMIN' => true,
);

$messages = Loc::loadLanguageFile(__FILE__);
$messages = array_merge($messages, $arResult['JS_MESSAGES']);

?>
<div id="<?=$areaId?>">
</div>

<script>
    BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);

    vueApp = DevBX.Forms.WebForm.createWebFormApp(<?=\Bitrix\Main\Web\Json::encode(\DevBx\Forms\Internals\Utils::arrayToJSCamel($arJSData))?>);

    window.webForm = vueApp.mount('#<?=$areaId?>');
</script>
