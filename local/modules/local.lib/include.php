<?

if (!\Bitrix\Main\Loader::includeModule('devbx.core'))
    return;

CModule::AddAutoloadClasses(
	"local.lib",
	$a = array(
	)
);
?>