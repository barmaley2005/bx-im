<?php
namespace DevBx\Forms;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FormControl {

    protected $arResult;

    function __construct(array $arResult)
    {
        $this->arResult = $arResult;
    }

    function ShowInput($fieldName)
    {
        foreach ($this->arResult['FIELDS'] as $arField)
        {
            if ($arField['FIELD_NAME'] == $fieldName)
            {
                echo $arField['HTML'];
                return;
            }
        }

        $this->ShowError(Loc::getMessage("DEVBX_FORM_FORM_CONTROL_FIELD_NOT_FOUND",array('#FIELD_NAME#'=>$fieldName)));
    }

    function ShowError($msg)
    {
        ShowError($msg);
    }

    function ShowErrors()
    {
        foreach ($this->arResult['ERRORS'] as $ar)
        {
            $this->ShowError($ar['text']);
        }
    }

    function ShowCaptcha()
    {
        if (!$this->arResult["CAPTCHA_CODE"])
            return;

        ?>
        <input type="hidden" name="captcha_sid" value="<?=$this->arResult["CAPTCHA_CODE"]?>" />
        <img src="/bitrix/tools/captcha.php?captcha_sid=<?=$this->arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
        <?
    }

    function SubmitButton()
    {
        echo '<button type="submit">'.Loc::getMessage('DEVBX_FORM_FORM_CONTROL_SUBMIT_BUTTON_TEXT').'</button>';
    }

    public static function getFormControl(array $arResult, array $arParams)
    {
        return new static($arResult);
    }

    public function getResult()
    {
        return $this->arResult;
    }

    public function showTpl($tpl)
    {
        $__show = function(FormControl $FORM) use ($tpl)
        {
            global $APPLICATION, $USER, $DB;

            $arResult = $FORM->getResult();

            eval('?>'.$tpl);
        };

        $__show($this);
    }
}