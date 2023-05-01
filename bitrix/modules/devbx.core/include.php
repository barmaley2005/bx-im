<?




CJSCore::RegisterExt('devbx_core_admin', [
    'js' => '/bitrix/js/devbx.core/admin.js',
    'rel' => array('core', 'ajax', 'devbx_core_utils', 'devbx_core_mslang'),
    'lang' => '/bitrix/modules/devbx.core/js/admin.php'
]);

CJSCore::RegisterExt('devbx_core_utils', [
    'js' => '/bitrix/js/devbx.core/utils.js',
]);

CJSCore::RegisterExt('devbx_core_ajax', [
    'js' => '/bitrix/js/devbx.core/ajax.js',
    'rel' => array('core', 'ajax'),
]);

CJSCore::RegisterExt('devbx_core_mslang', [
    'js' => '/bitrix/js/devbx.core/ms.lang.min.js',
    'rel' => array(),
]);

?>