<?php

namespace DevBx\Core;

class PackageApiException extends \Exception
{
    var $apiResult;

    public function __construct($message = "", $code = 0, $result = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->apiResult = $result;
    }
}