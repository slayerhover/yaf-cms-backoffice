<?php/**
 * yaf 框架报错类调用
 *  默认错误会调用这个Controller 中 ErrorAction
 */
class ErrorController extends Yaf_Controller_Abstract {
    private $_config;
    public function init(){
        $this->_config = Yaf_Application::app()->getConfig();
    }
    /**
     * [具体错误处理]
     * @param  Exception $exception [description]
     * @return [type]               [description]
     */
    public function errorAction(Exception $exception)
    {		
        if ($this->_config->application->debug) {			
            switch ($exception->getCode()) {
                case YAF_ERR_AUTOLOAD_FAILED:
                case YAF_ERR_NOTFOUND_MODULE:
                case YAF_ERR_NOTFOUND_CONTROLLER:
                case YAF_ERR_NOTFOUND_ACTION:
                case YAF_ERR_NOTFOUND_VIEW:
                    if (strpos($this->getRequest()->getRequestUri(), '.css') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.jpg') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.js')  !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.png') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.ico') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.gif') !== false
                    ){					
                        #header('HTTP/1.1 404 Not Found');						#json("404 Not Found");						$result	= array(							'ret'	=>	'1',							'msg'	=>	'404 Not Found',						);
                    }else{						$result	= array(							'ret'	=>	1,							'msg'	=>	$exception->getMessage(),						);					}					break;
                default:
                    //记录错误日志
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile() . ' ON LINE ' . $exception->getLine());                    					$result	= array(							'ret'	=>	'1',							'msg'	=>	$exception->getMessage(),					);			
            }
        } else {
            //禁止输出视图内容
            switch ($exception->getCode()) {
                case YAF_ERR_AUTOLOAD_FAILED:
                case YAF_ERR_NOTFOUND_MODULE:
                case YAF_ERR_NOTFOUND_CONTROLLER:
                case YAF_ERR_NOTFOUND_ACTION:
                case YAF_ERR_NOTFOUND_VIEW:
                    #header('HTTP/1.1 404 Not Found');
                    //记录日志
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile());
                    #$this->_view->assign('type', 'err404');					$result	= array(							'ret'	=>	'404',							'msg'	=>	'404 Not Found',						);					
                break;
                default:
                    #header('HTTP/1.1 500 Internal Server Error');
                    //记录文件错误日志                  
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile() . ' ON LINE ' . $exception->getLine());					$result	= array(							'ret'	=>	'500',							'msg'	=>	'500 Internal Server Error',						);
                break;
            }
        }				$result	= ['ret'	=>	$exception->getCode(), 'errmsg'=>$exception->getMessage()];				if($exception->getPrevious()){			$result['file']	=	$exception->getPrevious()->getFile();			$result['line']	=	$exception->getPrevious()->getLine();			$result['msg']	=	$exception->getPrevious()->getMessage();		}		json($result);		
    }
	
	public function renderSourceCode($file, $errorLine, $maxLines)
    {
        $errorLine--; // adjust line number to 0-based from 1-based
        if ($errorLine < 0 || ($lines = @file($file)) === false || ($lineCount = count($lines)) <= $errorLine)
            return '';
		
        $halfLines = (int)($maxLines / 2);
        $beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
        $endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;
        $lineNumberWidth = strlen($endLine + 1);

        $output = '';
        for ($i = $beginLine; $i <= $endLine; ++$i) {
            $isErrorLine = $i === $errorLine;
			$oneline = str_replace(array('<','>'), array('&lt','&gt'), $lines[$i]);
            $code = sprintf("<span class=\"ln" . ($isErrorLine ? ' error-ln' : '') . "\">%0{$lineNumberWidth}d</span> %s", $i + 1, $oneline);
            if (!$isErrorLine)
                $output .= $code;
            else
                $output .= '<span class="errorflag">' . $code . '</span>';
        }
        return '<div class="code"><pre>' . $output . '</pre></div>';
    }
	public function isCoreCode($trace)
    {
        if (isset($trace['file'])) {
            $systemPath = realpath(dirname(__FILE__) . '/..');
            return $trace['file'] === 'unknown' || strpos(realpath($trace['file']), $systemPath . DIRECTORY_SEPARATOR) === 0;
        }
        return false;
    }
	
	public function argumentsToString($args)
    {
        $count = 0;

        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5)
                    unset($args[$key]);
                else
                    $args[$key] = '...';
                continue;
            }

            if (is_object($value))
                $args[$key] = get_class($value);
            elseif (is_bool($value))
                $args[$key] = $value ? 'true' : 'false';
            elseif (is_string($value)) {
                if (strlen($value) > 64)
                    $args[$key] = '"' . substr($value, 0, 64) . '..."';
                else
                    $args[$key] = '"' . $value . '"';
            } elseif (is_array($value))
                $args[$key] = 'array(' . $this->argumentsToString($value) . ')';
            elseif ($value === null)
                $args[$key] = 'null';
            elseif (is_resource($value))
                $args[$key] = 'resource';

            if (is_string($key)) {
                $args[$key] = '"' . $key . '" => ' . $args[$key];
            } elseif ($isAssoc) {
                $args[$key] = $key . ' => ' . $args[$key];
            }
        }
        $out = implode(", ", $args);

        return $out;
    }
	
}
