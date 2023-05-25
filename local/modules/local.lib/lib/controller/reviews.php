<?php

namespace Local\Lib\Controller;

use Bitrix\Main;
use Bitrix\Main\Engine\ActionFilter;

class Reviews extends Main\Engine\Controller
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

    public function getProductReviewsAction($productId)
    {
        global $APPLICATION;

        Main\Loader::includeModule('iblock');

        $arElement = \CIBlockElement::GetByID($productId)->Fetch();
        if (!$arElement || $arElement['ACTIVE'] != 'Y') {
            $this->addError(new Main\Error('Element not found'));
            return false;
        }

        $GLOBALS['arrProductReview'] = array('=UF_PRODUCT_ID' => $productId);

        ob_start();

        $APPLICATION->IncludeComponent(
            "devbx:form.result.list",
            "detail-reviews",
            array(
                "AJAX_MODE" => "N",
                "AJAX_OPTION_ADDITIONAL" => "",
                "AJAX_OPTION_HISTORY" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "CREATED_DATE_FORMAT" => "d.m.Y",
                "DISPLAY_BOTTOM_PAGER" => "N",
                "DISPLAY_FIELDS" => array("ID", "ACTIVE", "CREATED_DATE", "MODIFIED_DATE", "UF_PRODUCT_ID", "UF_NAME", "UF_CITY", "UF_EMAIL", "UF_COMMENT", "UF_RECOMMEND"),
                "DISPLAY_TOP_PAGER" => "N",
                "FILTER_NAME" => "arrProductReview",
                "FORM_ID" => "2",
                "MODIFIED_DATE_FORMAT" => "d.m.Y",
                "ONLY_ACTIVE_RESULTS" => "Y",
                "PAGER_BASE_LINK_ENABLE" => "N",
                "PAGER_DESC_NUMBERING" => "N",
                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                "PAGER_SHOW_ALL" => "N",
                "PAGER_SHOW_ALWAYS" => "N",
                "PAGER_TEMPLATE" => ".default",
                "PAGER_TITLE" => "Результаты",
                "RESULTS_COUNT" => "3",
                "SORT_BY1" => "CREATED_DATE",
                "SORT_BY2" => "ID",
                "SORT_ORDER1" => "DESC",
                "SORT_ORDER2" => "ASC",
                "PRODUCT_ID" => $productId
            )
        );

        return array(
            'content' => ob_get_clean(),
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

    public function getReviewFormAction($productId)
    {
        global $APPLICATION;

        Main\Loader::includeModule('iblock');

        $arElement = \CIBlockElement::GetByID($productId)->Fetch();
        if (!$arElement || $arElement['ACTIVE'] != 'Y') {
            $this->addError(new Main\Error('Element not found'));
            return false;
        }

        if ($_REQUEST['AJAX_CALL'] != 'Y')
            ob_start();
        //PUBLIC_AJAX_MODE

        $APPLICATION->IncludeComponent(
            "devbx:form",
            "review",
            array(
                "ACTION_VARIABLE" => "form-action",
                "AJAX_LOAD_FORM" => "N",
                "AJAX_MODE" => "Y",
                "AJAX_OPTION_ADDITIONAL" => "",
                "AJAX_OPTION_HISTORY" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "CHECK_AJAX_SESSID" => "N",
                "DEFAULT_FIELDS" => array("UF_PRODUCT_ID", ""),
                "DEFAULT_FIELD_VALUE_UF_PRODUCT_ID" => $productId,
                "FORM_ID" => "2",
                "READ_ONLY_FIELDS" => array("", ""),
            )
        );

        $html = ob_get_clean();

        $html .= $APPLICATION->EndBufferContent();

        return array(
            'content' => $html,
            'js' => Main\Page\Asset::getInstance()->getJs(),
            'css' => Main\Page\Asset::getInstance()->getCss(),
        );
    }

}