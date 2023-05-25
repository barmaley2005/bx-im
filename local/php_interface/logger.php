<?php
/*
 * Copyright (c) 2021. DEV-BX.RU
 * barmaley2005@gmail.com
 */

class DevBxLogger {
    private $filename = false;
    /**
     * @var bool
     */
    private $logRequestUri = true;
    /**
     * @var bool
     */
    private $logTrace = true;
    /**
     * @var bool
     */
    private $logFooter = true;

    public static function checkDirPath($path)
    {
        if (function_exists('CheckDirPath'))
        {
            return CheckDirPath($path);
        }

        //remove file name
        if(mb_substr($path, -1) != "/")
        {
            $p = mb_strrpos($path, "/");
            $path = mb_substr($path, 0, $p);
        }

        $path = rtrim($path, "/");

        if($path == "")
        {
            //current folder always exists
            return true;
        }

        if(!file_exists($path))
        {
            return mkdir($path, 0755, true);
        }

        return is_dir($path);
    }

    public static function getBackTrace($limit = 0, $options = null, $skip = 1)
    {
        if(!defined("DEBUG_BACKTRACE_PROVIDE_OBJECT"))
        {
            define("DEBUG_BACKTRACE_PROVIDE_OBJECT", 1);
        }

        if ($options === null)
        {
            $options = ~DEBUG_BACKTRACE_PROVIDE_OBJECT;
        }

        $trace = debug_backtrace($options, ($limit > 0? $limit + $skip : 0));

        if ($limit > 0)
        {
            return array_slice($trace, $skip, $limit);
        }

        return array_slice($trace, $skip);
    }

    protected static function formatTrace(array $trace = null)
    {
        if ($trace)
        {
            $traceLines = array();
            foreach ($trace as $traceNum => $traceInfo)
            {
                $traceLine = '';

                if (array_key_exists('class', $traceInfo))
                    $traceLine .= $traceInfo['class'].$traceInfo['type'];

                if (array_key_exists('function', $traceInfo))
                    $traceLine .= $traceInfo['function'].'()';

                if (array_key_exists('file', $traceInfo))
                {
                    $traceLine .= ' '.$traceInfo['file'];
                    if (array_key_exists('line', $traceInfo))
                        $traceLine .= ':'.$traceInfo['line'];
                }

                if ($traceLine)
                    $traceLines[] = ' from '.$traceLine;
            }

            return implode("\n", $traceLines);
        }
        else
        {
            return "";
        }
    }

    public static function getFileNameForLog($trace)
    {
        $arRegTpl = array(
            'component.$1-%Y-%m-%d.log' => '#.*\/components\/.+\/(.+)\/.*$#U',
            'module.$1-%Y-%m-%d.log' => '#.*\/modules\/(.+)\/.*$#U',
            'admin.$1-%Y-%m-%d.log' => '#\/bitrix\/admin\/(.+)\..*$#',
            '$1-%Y-%m-%d.log' => '#.*\/(.+\.php)$#',
        );

        $fileName = 'debug-%Y-%m-%d.log';

        foreach ($trace as $traceNum => $traceInfo)
        {
            if (array_key_exists('file', $traceInfo))
            {
                foreach ($arRegTpl as $replace=>$pattern)
                {
                    if (preg_match($pattern, $traceInfo['file']))
                    {
                        $fileName = preg_replace($pattern,$replace, $traceInfo['file']);
                        break 2;
                    }
                }

            }
        }

        return static::formatFileName($fileName);
    }

    private static function formatFileName($fileName)
    {
        $d = new \DateTime();

        $arMacro = array(
            '%Y' => $d->format('Y'),
            '%y' => $d->format('y'),
            '%m' => $d->format('m'),
            '%d' => $d->format('d'),
            '%H' => $d->format('H'),
            '%h' => $d->format('h'),
            '%i' => $d->format('i'),
            '%s' => $d->format('s'),
        );

        $fileName = str_replace(array_keys($arMacro),array_values($arMacro), $fileName);

        return $fileName;
    }

    private static $registryMap = [];

    private function __construct($filename)
    {
        $this->filename = $filename;
    }

    public static function getInstance($filename = false)
    {
        if (isset(self::$registryMap[$filename]))
            return self::$registryMap[$filename];

        self::$registryMap[$filename] = new static($filename);
        return self::$registryMap[$filename];
    }

    public function setLogRequestUri($value): DevBxLogger
    {
        $this->logRequestUri = $value === true;

        return $this;
    }

    public function setLogTrace($value): DevBxLogger
    {
        $this->logTrace = $value === true;

        return $this;
    }

    public function setLogFooter($value): DevBxLogger
    {
        $this->logFooter = $value === true;

        return $this;
    }

    public function logVar($var, $varName = '', $traceSkip = 1): DevBxLogger
    {
        $arTrace = self::getBackTrace(30, null, $traceSkip);

        if ($this->filename === false)
        {
            $logFilename = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.static::getFileNameForLog($arTrace);
        } else
        {
            $logFilename = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.static::formatFileName($this->filename);
        }

        $header = '';

        if (isset($_SERVER["REQUEST_URI"]) && $_SERVER["REQUEST_URI"] && $this->logRequestUri)
            $header .= "REQUEST URI: ".$_SERVER["REQUEST_URI"]."\n";

        $trace = static::formatTrace($arTrace);

        $body = '';
        if ($varName)
            $body = $varName.":\n";

        if (is_object($var) || is_array($var))
            $body .= var_export($var, true);
        else
            $body .= $var;

        $footer = str_repeat("-", 30);

        $logSessid = '';
        $logTime = '';

        if (function_exists('bitrix_sessid_val'))
        {
            $logSessid = "\nSESSID: ".bitrix_sessid_val();
        }

        if (isset($_SERVER['REQUEST_TIME_FLOAT']))
        {
            $logTime = "\nTIME: ".(microtime(true)-$_SERVER['REQUEST_TIME_FLOAT']);
        }

        if (!isset($_SERVER['DEVBX_DEBUG_ID']))
        {
            $_SERVER['DEVBX_DEBUG_ID'] = md5(uniqid());
        }

        $logDebugId = "\nDEBUG ID: ".$_SERVER['DEVBX_DEBUG_ID'];

        $message =
            ($header ? "\n" . $header : '').
            "\nDate: ".date("Y-m-d H:i:s") .
            $logSessid.
            $logTime.
            $logDebugId.
            "\n" . $body .
            ($this->logTrace ? "\n\n" . $trace : '').
            ($this->logFooter ? "\n" . $footer : '').
            "\n";

        self::checkDirPath($logFilename);

        file_put_contents($logFilename, $message, FILE_APPEND);

        return $this;
    }


    public static function log($var, $varName = '', $fileName = false)
    {
        self::getInstance($fileName)->logVar($var, $varName, 2);
    }

}
