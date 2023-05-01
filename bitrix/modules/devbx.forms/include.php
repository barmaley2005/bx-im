<?

use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Main\Loader;

if (!\Bitrix\Main\Loader::includeModule("devbx.core"))
    return false;

define('DEVBX_FORMS_DEBUG', true);

CJSCore::RegisterExt('devbx_forms_fields', [
    'js' => '/bitrix/js/devbx.forms/fields.js',
    'rel' => array('core'),
]);

CJSCore::RegisterExt('devbx_webform_htmleditor', [
    'js' => ['/bitrix/js/devbx.forms/webform.htmleditor.js'],
    'lang' => '/bitrix/modules/devbx.forms/lang/' . LANGUAGE_ID . '/js/webform.htmleditor.php',
    'rel' => array('core'),
]);

CJSCore::RegisterExt('devbx_webform_codemirror', [
    'js' => ['/bitrix/js/devbx.forms/codemirror.js'],
]);

CJSCore::RegisterExt('devbx_webform_multiselect', [
    'js' => ['/bitrix/js/devbx.forms/vue-multiselect.js'],
    'css' => ['/bitrix/css/devbx.forms/vue-multiselect.css'],
]);

CJSCore::RegisterExt('devbx_webform_datepicker', [
    'js' => ['/bitrix/js/devbx.forms/datepicker/index.js'],
    'css' => ['/bitrix/js/devbx.forms/datepicker/index.css'],
]);

CJSCore::RegisterExt('devbx_webform_file_upload', [
    'js' => ['/bitrix/js/devbx.forms/file-upload/vue-upload-component.js']
]);

CJSCore::RegisterExt('devbx_forms_vue_webform_admin', [
    'js' => array(
        '/bitrix/js/devbx.forms/webform.admin.js',
    ),
    'css' => array(
        '/bitrix/css/devbx.forms/webform.admin.css',
    ),
    'rel' => array('core', 'ajax', 'devbx_core_mslang', 'devbx_webform_codemirror')
]);

CJSCore::RegisterExt('devbx_forms_vue_webform', [
    'js' => array(
        '/bitrix/js/devbx.forms/velocity.min.js',
        '/bitrix/js/devbx.forms/webform.public.js',
    ),
    'css' => array(
        '/bitrix/css/devbx.forms/webform.public.css',
    ),
    'rel' => array('core', 'ajax', 'devbx_core_mslang')
]);

/*DEV*/


//\CModule::CreateModuleObject('devbx.forms')->InstallFiles();
//\CModule::CreateModuleObject('devbx.forms')->InstallEvents();

//\DevBx\Core\Admin\Utils::correctModuleTables('devbx.forms');

/*DEV*/


/*
\DevBx\Forms\FormManager::getInstance()->correctTable(DevBx\Forms\FormTable::getEntity());
\DevBx\Forms\FormManager::getInstance()->correctTable(DevBx\Forms\FormLangNameTable::getEntity());

$arFormType = \DevBx\Forms\FormManager::getInstance()->getFormType();

//$iterator = DevBx\Forms\FormTable::getList(array('cache'=>array('ttl'=>3600)));
$iterator = DevBx\Forms\FormTable::getList();
while ($obForm = $iterator->fetchObject())
{
    if (array_key_exists($obForm->getFormType(), $arFormType))
    {
        $entity = \DevBx\Forms\FormManager::getInstance()->compileFormEntity($obForm);
        \DevBx\Forms\FormManager::getInstance()->correctTable($entity);
    }
}
*/

class CDevBxFormsUtils
{
    public static function didBuyProduct($productId, $userId = false)
    {
        global $USER;

        if (!\Bitrix\Main\Loader::includeModule("sale"))
            return false;

        if ($userId === false) {
            if (!$USER->IsAuthorized())
                return false;

            $userId = $USER->GetID();
        }

        $arFilter = array(
            '=ORDER.STATUS_ID' => \Bitrix\Sale\OrderStatus::getFinalStatus(),
            '=ORDER.USER_ID' => $userId,
            '=PRODUCT_ID' => $productId,
        );

        return is_array(BasketTable::getList(array("filter" => $arFilter, "limit" => 1))->fetch());
    }

}

?>