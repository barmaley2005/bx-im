<?

namespace DevBx\Forms\WebForm;

use Bitrix\Main\Localization\Loc;
use DevBx\Forms\WebForm\Types\StringType;

class WOFinishPage extends WOCollectionItem {
    public function __construct()
    {
        parent::__construct(array(
            (new StringType('CONTENT'))->configureDefaultValue(function() {
                return Loc::getMessage('DEVBX_WEB_FORM_FINISH_PAGE_CONTENT');
            }),
            (new Types\ConditionType('SHOW_RULE'))->configureDefaultValue(array('VALUE'=>'never')),
        ));
    }
}