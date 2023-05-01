<?php
define('VUEJS_DEBUG', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use DevBx\Forms\WebForm\WOForm;
use DevBx\Forms\WebForm\Components;

Loader::includeModule("fileman");
Loader::includeModule("ui");
Loader::includeModule("devbx.core");
Loader::includeModule("devbx.forms");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Main\UI\Extension::load("ui.fontawesome4");
\Bitrix\Main\UI\Extension::load("ui.buttons");
\Bitrix\Main\UI\Extension::load("ui.highlightjs");

\Bitrix\Main\UI\Extension::load("ui.vue3");
\Bitrix\Main\UI\Extension::load("ui.vue3.vuex");


CJSCore::Init(['devbx_forms_vue_webform_admin', 'devbx_core_utils']);


$request = \Bitrix\Main\Context::getCurrent()->getRequest();

$webFormId = false;

$isAjax = $request->isPost() && $request['ajax'] == 'Y';
$obForm = false;

if (isset($request['ID']))
{
    $webFormId = intval($request['ID']);

    $wizardConfig = \DevBx\Forms\FormTable::getList([
        'filter' => [
            '=ID' => $webFormId,
            '=FORM_TYPE' => \DevBx\Forms\FormTypes\WebFormType::getType(),
        ]
    ])->fetchObject();

    if (!$wizardConfig)
    {
        CAdminMessage::ShowMessage(Loc::getMessage('DEVBX_WEB_FORM_EDIT_FORM_NOT_FOUND'));
        require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
        return;
    }

    $obForm = new WOForm();
    $obForm->setValues($wizardConfig->getSettings());

} else {
    $wizardConfig = \DevBx\Forms\FormTable::createObject();
    $wizardConfig->setFormType(\DevBx\Forms\FormTypes\WebFormType::getType());
}

$obFormConfig = new \DevBx\Forms\WebForm\WOFormConfig();
$obFormConfig->loadConfig($wizardConfig);

$errors = false;

$wizard = \DevBx\Forms\Wizards\WebForm\Wizard::getInstance();

$fieldManager = $wizard->getFieldManager();

$messages = Loc::loadLanguageFile(__FILE__);
$messages = array_merge($messages, $wizard->getLangMessages());
$messages = array_merge($messages, Components\Manager::getScopeLangMessages(Components\Manager::SCOPE_ADMIN));

$messages = array_merge($messages, \DevBx\Forms\WebForm\Fields\Base::getLangMessages());

$arWebFormElements = array();

foreach ($fieldManager->getWebFormFieldsGroup() as $group)
{
    $arJSGroup = array(
        'NAME' => $group->getName(),
        'ITEMS' => array(),
    );

    foreach ($group->getFields() as $field)
    {
        $arJSGroup['ITEMS'][] = array(
            'DATA' => $field::getFieldData(),
        );

        $messages = array_merge($messages, $field::getLangMessages());
    }

    $arWebFormElements[] = $arJSGroup;
}

$arJSData = array(
    'FORM_ELEMENTS' => $arWebFormElements,
    'DEFAULT_ENTITY' => array(
        'PAGE' => (new \DevBx\Forms\WebForm\WOFormPageConfig())->getValues(),
        'FINISH_PAGE' => (new \DevBx\Forms\WebForm\WOFinishPage())->getValues(),
    ),
    'CONFIG' => $obFormConfig->toArray(),
    'WEB_FORM_ID' => $webFormId,
);

$arCulture = array();

$culture = \Bitrix\Main\Context::getCurrent()->getCulture();

foreach ($culture->entity->getScalarFields() as $field)
{
    if ($field->isPrimary())
        continue;

    $arCulture[$field->getName()] = $culture->get($field->getName());
}

$arJSData['CULTURE'] = $arCulture;

$iterator = \Bitrix\Main\GroupTable::getList(array(
    'filter' => array('ACTIVE'=>'Y'),
    'select' => array('ID','NAME'),
    'order' => array('C_SORT'=>'ASC'),
));

$arJSData['USER_GROUPS'] = $iterator->fetchAll();

?>
    <div class="adm-white-container">
        <div id="app" class="devbx-webform-builder">
        </div>
    </div>
    <script>

        BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);

        vueApp = DevBX.Forms.Admin.createWebFormMaster(<?=\Bitrix\Main\Web\Json::encode(\DevBx\Forms\Internals\Utils::arrayToJSCamel($arJSData))?>);
        myApp = vueApp.mount('#app');

        <?
            if ($obForm)
            {
                $jsWebForm = \DevBx\Forms\Internals\Utils::arrayToJSCamel($obForm->toArray());

                ?>
                myApp.setWebFormData(<?=\Bitrix\Main\Web\Json::encode($jsWebForm)?>);
                <?
            }
        ?>

    </script>
<div style="display: none;">
<?

$editor = new \CHTMLEditor;
$editor->Show(array('id'=>'fake','name'=>'fake'));

CJSCore::Init(['devbx_webform_htmleditor']);

?>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
