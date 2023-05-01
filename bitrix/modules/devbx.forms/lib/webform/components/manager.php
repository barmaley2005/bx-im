<?php

namespace DevBx\Forms\WebForm\Components;

use Bitrix\Main\EventResult;
use Bitrix\Main\SystemException;

class Manager {

    const SCOPE_ADMIN = 'admin';
    const SCOPE_USER = 'user';

    public static function getScopeLangMessages($scope)
    {
        $arClass = [];

        switch ($scope)
        {
            case static::SCOPE_ADMIN:
                $arClass = array(
                    Admin\DevBxFormEmptyCell::class,
                    Admin\DevBxFormLayoutToolbar::class,
                    Admin\DevBxWebFormAdminActions::class,
                    Admin\DevBxWebFormFormNextPageButton::class,
                    Admin\DevBxWebFormFormPrevPageButton::class,
                    Admin\DevBxWebFormFormSubmitButton::class,
                    Admin\DevBxWebFormMasterMenu::class,
                    Admin\DevBxWebFormMaster::class,
                    Admin\DevBxWebFormPagesActions::class,
                    Admin\DevBxWebFormPublicAdminPanel::class,
                    Admin\DevBxWebFormSettings::class,
                    Admin\DevBxWebFormCondition::class,
                    Admin\FieldCond::class,
                    Admin\DevBxWebFormSettingsGeneral::class,
                    Admin\DevBxWebFormFinishPage::class,
                    Admin\DevBxWebFormFinishPageCond::class,
                    Admin\DevBxWebFormPopupConditionWizard::class,
                );
                break;
            case static::SCOPE_USER:
                $arClass = array(
                    User\DevBxWebFormHeader::class
                );
                break;
        }

        $event = new \Bitrix\Main\Event(
            "devbx.forms",
            "OnWebFormGetComponents",
            array("SCOPE"=>$scope)
        );

        $event->send();

        foreach ($event->getResults() as $eventResult)
        {
            if ($eventResult->getType() == EventResult::SUCCESS)
            {
                $parameters = $eventResult->getParameters();

                if (isset($parameters['COMPONENTS']))
                {
                    $components = $parameters['COMPONENTS'];

                    if (is_array($components))
                    {
                        $arClass = array_merge($arClass, array_values($components));
                    } elseif (!empty($components) && is_string($components)) {
                        $arClass[] = $components;
                    }
                }
            }
        }

        $result = [];

        foreach ($arClass as $class)
        {
            if (!class_exists($class)) {
                if (defined('DEVBX_FORMS_DEBUG') && DEVBX_FORMS_DEBUG === true)
                    throw new SystemException('Class not found ' . $class);

                continue;
            }

            if (!is_a($class, Base::class, true)) {
                if (defined('DEVBX_FORMS_DEBUG') && DEVBX_FORMS_DEBUG === true)
                    throw new SystemException('Class ' . $class . ' need subclass of ' . Base::class);

                continue;
            }

            $result = array_merge($result, $class::getLangMessages());
        }

        return $result;
    }

}