<?php

namespace Local\Lib\Controller;

use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;

class User extends Main\Engine\Controller
{
    protected function getDefaultPreFilters()
    {
        return [
            //new ActionFilter\Authentication(),
            new ActionFilter\HttpMethod(
                [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
            ),
            new ActionFilter\Csrf(),
        ];
    }

    public function logoutAction()
    {
        global $USER;

        if (!$USER->IsAuthorized())
        {
            $this->addError(new Main\Error('Вы не авторизованы'));
            return false;
        }

        $USER->Logout();

        return array('success'=>true);
    }

    public function uploadPersonalPhotoAction()
    {
        global $USER;

        if (!$USER->IsAuthorized())
        {
            $this->addError(new Main\Error('Вы не авторизованы'));
            return false;
        }

        $request = Main\Context::getCurrent()->getRequest();

        $arFile = $request->getFile('file');
        if (!is_array($arFile) || $arFile['error'] || $arFile['size'] <= 0) {
            $this->addError(new Main\Error('Ошибка загрузки файла'));
            return false;
        }

        $USER->Update($USER->GetID(),array('PERSONAL_PHOTO'=>$arFile),false);

        $arPhoto = \CFile::GetFileArray($USER->GetParam('PERSONAL_PHOTO'));

        return array('success'=>true, 'src' => $arPhoto['SRC']);
    }

}
