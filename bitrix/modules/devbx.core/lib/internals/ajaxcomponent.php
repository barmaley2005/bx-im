<?php

namespace DevBx\Core\Internals;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use DevBx\Core\PackageApiException;
use DevBx\Core\Utils;

Loc::loadMessages(__FILE__);

class AjaxComponent extends \CBitrixComponent
{
    protected $ajaxAction;
    protected $originalParameters;
    private $bPackageCall = false;

    function onPrepareComponentParams($arParams)
    {
        if (!isset($this->arParams['ACTION_VARIABLE']) || empty(trim($this->arParams['ACTION_VARIABLE'])))
        {
            $this->arParams['ACTION_VARIABLE'] = 'ajax-action';
        }

        $this->ajaxAction = $arParams['DEVBX_AJAX_ACTION'] == 'Y';
        unset($arParams['DEVBX_AJAX_ACTION']);

        $this->originalParameters = $arParams;

        return parent::onPrepareComponentParams($arParams);
    }

    public function sendJsonAnswer($data)
    {
        global $APPLICATION;

        if ($this->bPackageCall)
        {
            throw new PackageApiException('', 0, $data);
        }

        $data = Utils::prepareArrayForJS($data);

        $response = \Bitrix\Main\Context::getCurrent()->getResponse();
        $response->addHeader("Content-Type", "application/json; charset=UTF-8");

        $response->flush(\Bitrix\Main\Web\Json::encode($data));
        die();
    }

    public function showError($strError)
    {
        if ($this->ajaxAction)
        {
            $this->sendJsonAnswer(['ERROR'=>$strError]);
        }

        $this->arResult['ERROR'] = $strError;

        if ($this->bPackageCall)
        {
            throw new PackageApiException('', 0, $this->arResult);
        }
    }

    protected function loadTemplatesAction()
    {
        if (!$this->initComponentTemplate())
        {
            $this->showError(Loc::getMessage('DEVBX_ERR_INITIALIZE_TEMPLATE'));
            return;
        }

        global $APPLICATION, $DB, $USER;

        $templateName = $this->getTemplate()->GetName();
        $templateFile = $this->getTemplate()->GetFile();
        $templateFolder = $this->getTemplate()->GetFolder();

        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $components = $this;

        $documentRoot = Main\Application::getDocumentRoot();

        $jsTemplates = new Main\IO\Directory($documentRoot.$templateFolder.'/js-templates');

        if (!$jsTemplates->isExists())
        {
            $this->showError(Loc::getMessage('DEVBX_ERR_NO_JS_TEMPLATES'));
            return;
        }

        $this->arResult['JS_TEMPLATES'] = array();

        /** @var Main\IO\File $jsTemplate */
        foreach ($jsTemplates->getChildren() as $jsTemplate)
        {
            ob_start();
            include($jsTemplate->getPath());
            $html = ob_get_clean();

            $document = new \Bitrix\Main\Web\Dom\Document;
            $document->loadHTML($html);

            $node = $document->querySelector('script');

            if ($node)
            {
                $attributes = $node->getAttributes();

                if ($attributes['id'])
                {
                    $id = $attributes['id'];
                    /* @var \Bitrix\Main\Web\DOM\Attr $id */

                    $this->arResult['JS_TEMPLATES'][$id->getValue()] = $node->getInnerHTML();
                }
            }
        }
    }

    public function packageAction()
    {
        $callItems = $this->request->getPost('items');
        if (!is_array($callItems))
        {
            $this->showError('invalid call package items');
            return;
        }

        $this->bPackageCall = true;

        $packageResult = [];

        $requestParams = $this->request->toArray();
        $postParams = $this->request->getPostList()->toArray();

        unset($requestParams['items']);
        unset($postParams['items']);

        foreach ($callItems as $item)
        {
            $this->arResult = [];
            $action = $item['method'];
            $callback = $item['callback'];

            $this->request->setValues(array_merge($requestParams, $item));
            $this->request->getPostList()->setValues(array_merge($postParams, $item));

            if (is_callable(array($this, $action . 'Action'))) {
                try {
                    $this->{$action . 'Action'}();
                } catch (\Exception $e) {
                    if  ($e instanceof PackageApiException)
                    {
                        $this->arResult = $e->apiResult;
                    } else
                    {
                        $this->arResult['ERROR'] = $e->getMessage();
                    }
                }
            } else
            {
                $this->arResult['ERROR'] = 'unknown action '.$action;
            }

            if (!is_array($this->arResult))
            {
                $this->arResult = array('RESULT'=>$this->arResult);
            }

            $this->arResult['method'] = $action;
            if ($callback)
            {
                $this->arResult['callback'] = $callback;
            }

            $packageResult[] = $this->arResult;
        }

        $this->bPackageCall = false;

        $this->arResult = $packageResult;
    }

    public static function componentAjaxRequest($componentName)
    {
        $request = Main\Application::getInstance()->getContext()->getRequest();
        $request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

        $salt = str_replace(':','.',$componentName);

        $signer = new \Bitrix\Main\Security\Sign\Signer;
        try
        {
            $signedParamsString = $request->get('parameters') ?: '';
            $params = $signer->unsign($signedParamsString, $salt);
            $params = unserialize(base64_decode($params));

            $template = $signer->unsign($request->get('template'), $salt);
        }
        catch (\Bitrix\Main\Security\Sign\BadSignatureException $e)
        {
            die();
        }

        $action = $request->get($params['ACTION_VARIABLE']);
        if (empty($action))
            return;

        global $APPLICATION;

        $APPLICATION->IncludeComponent(
            $componentName,
            $template,
            $params+['DEVBX_AJAX_ACTION'=>'Y'],
        );

        require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
    }

    function processRequest()
    {
        if ($this->request->isPost()) {
            $action = $this->request->getPost($this->arParams['ACTION_VARIABLE']);

            if (!empty($action)) {

                if (!check_bitrix_sessid())
                {
                    if ($this->ajaxAction)
                    {
                        $this->sendJsonAnswer(['ERROR'=>Loc::getMessage('DEVBX_AJAX_COMPONENT_INVALID_SESSION')]);
                    }

                    $this->arResult['ERROR'] = Loc::getMessage('DEVBX_AJAX_COMPONENT_INVALID_SESSION');
                    return false;
                }

                if (is_callable(array($this, $action . 'Action'))) {
                    try {
                        $this->{$action . 'Action'}();

                        if ($this->ajaxAction)
                            $this->sendJsonAnswer($this->arResult);

                        return true;
                    } catch (\Exception $e) {
                        if ($this->ajaxAction)
                            $this->sendJsonAnswer(['ERROR' => $e->getMessage()]);

                        $this->arResult['ERROR'] = $e->getMessage();
                    }
                }
            }
        }

        return false;
    }

}