<?php

namespace Local\Lib;

use Bitrix\Main;
use Local\Lib\Internals\DaDataApi;

class Api {
    private static $instance;
    private $methods;
    private $bPackageCall = false;

    public function __construct()
    {
        $this->methods = [];
    }

    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new Api();
        }

        return self::$instance;
    }

    public function registerApi($method, $callback)
    {
        if (array_key_exists($method, $this->methods))
            throw new Main\SystemException('method already exists '.$method);

        if (!is_callable($callback))
            throw new Main\SystemException('not callable');

        $this->methods[$method] = $callback;
    }

    protected function callMethod($method, Main\Type\ParameterDictionary $params)
    {
        $cb = $this->methods[$method];

        if (is_array($cb) && count($cb) == 2)
        {
            $f = new \ReflectionMethod($cb[0], $cb[1]);

            $funcParameters = $f->getParameters();

            if (count($funcParameters) == 0)
            {
                return call_user_func_array($cb, array($this, $params));
            }

            if (count($funcParameters)>0)
            {
                if ($funcParameters[0]->getType() != NULL && $funcParameters[0]->getType()->getName() === self::class)
                {
                    return call_user_func_array($cb, array($this, $params));
                }

                $callParams = [];

                foreach ($funcParameters as $funcParameter)
                {
                    if (!isset($params[$funcParameter->getName()]))
                    {
                        if (!$funcParameter->isOptional())
                        {
                            return ['error' => 'parameter required "'.$funcParameter->getName().'"'];
                        }

                        if ($funcParameter->isArray())
                        {
                            $value = array();
                        } else {
                            $value = null;
                        }
                    } else {
                        $value = $params[$funcParameter->getName()];

                        if ($funcParameter->isArray() && !is_array($value))
                        {
                            return ['error' => 'parameter "'.$funcParameter->getName().'" must be array'];
                        }

                    }

                    $callParams[] = $value;
                }

                return call_user_func_array($cb, $callParams);
            }
        }

        return call_user_func_array($cb, array($this, $params));
    }

    public function processAction()
    {
        $request = Main\Context::getCurrent()->getRequest();

        if (!$request->isPost())
            return;

        \CHTTP::SetStatus('200 OK');

        $response = Main\Context::getCurrent()->getResponse();
        $response->setStatus('200 OK');
        $response->addHeader("X-DevBx-Api", \CModule::CreateModuleObject('local.lib')->MODULE_VERSION);

        if (!check_bitrix_sessid())
        {
            $this->sendJsonAnswer(['error' => 'Сессия устарела']);
        }

        Main\Loader::includeModule('devbx.core');

        $postList = $request->getPostList();

        if ($request->getHeader('content-type') == 'application/json')
        {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data))
            {
                $this->sendJsonAnswer(['error' => 'Ошибка декодирования json']);
            }

            $postList->setValues($data);
        }

        $this->bPackageCall = $postList->offsetExists('package_call') && is_array($postList->get('package_call'));

        if ($this->bPackageCall)
        {
            $result = [];

            foreach ($postList->get('package_call') as $ar)
            {
                $method = $ar['method'];
                if (array_key_exists($method, $this->methods))
                {
                    try {
                        $params = new Main\Type\ParameterDictionary($ar);

                        $methodResult = $this->callMethod($method, $params);
                    } catch (\Exception $e) {
                        if ($e instanceof PackageApiException) {
                            $methodResult = $e->apiResult;
                        } else {
                            AddMessage2Log('Exception: ' . $e->getMessage() . "\r\n" . $e->getTraceAsString());
                            $methodResult = [
                                'error' => $e->getMessage()
                            ];
                        }
                    }

                    if ($methodResult instanceof Main\Result)
                    {
                        if ($methodResult->isSuccess())
                        {
                            $methodResult = $methodResult->getData();
                        } else {
                            $methodResult = [
                                'error' => $methodResult->getErrorMessages()
                            ];
                        }
                    }

                    if (!is_array($methodResult))
                        $methodResult = array('result'=>$methodResult);

                    $result[] = $methodResult+['method'=>$method];
                } else
                {
                    $result[] = ['error'=>'called unknown method '.$method];
                }
            }

        } else
        {
            $method = $postList->get('method');
            if (array_key_exists($method, $this->methods))
            {
                try {
                    $result = $this->callMethod($method, $postList);

                    if ($result instanceof Main\Result)
                    {
                        if ($result->isSuccess())
                        {
                            $result = $result->getData();
                        } else {
                            $result = [
                                'error' => $result->getErrorMessages()
                            ];
                        }
                    }

                } catch (\Exception $e)
                {
                    AddMessage2Log('Exception: '.$e->getMessage()."\r\n".$e->getTraceAsString());
                    $result = [
                        'error' => $e->getMessage()
                    ];
                }
            } else
            {
                $result = ['error'=>'called unknown method '.$method];
            }
        }

        $this->bPackageCall = false;

        $this->sendJsonAnswer($result);
    }

    public function sendJsonAnswer($data)
    {
        if ($this->bPackageCall)
        {
            throw new PackageApiException('', 0, $data);
        }

        //$response = new Main\HttpResponse(Main\Context::getCurrent());
        $response = Main\Context::getCurrent()->getResponse();
        $response->addHeader("Content-Type", "application/json; charset=UTF-8");

        $response->flush(Main\Web\Json::encode($data));

        //Main\Application::getInstance()->end();

    }

    public static function OnPageStart()
    {
        $request = Main\Context::getCurrent()->getRequest();

        /*if (!$request->isPost())
            return;*/

        if (strpos($request->getRequestUri(), '/ajax/api') !== 0)
            return;

        \Local\Lib\Internals\MainApi::registerApi(\Local\Lib\Api::getInstance());
        \Local\Lib\Internals\SaleApi::registerApi(\Local\Lib\Api::getInstance());
        \Local\Lib\Internals\UserGeo::registerApi(\Local\Lib\Api::getInstance());
        \Local\Lib\Internals\NzetaApi::registerApi(\Local\Lib\Api::getInstance());
        \Local\Lib\Internals\DaDataApi::registerApi(\Local\Lib\Api::getInstance());

        \Local\Lib\Api::getInstance()->processAction();
        die();
    }

    public static function getApiUrl()
    {
        return '/ajax/api/';
    }
}
