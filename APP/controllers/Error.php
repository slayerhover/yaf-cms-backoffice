<?php
/**
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
                        strpos($this->getRequest()->getRequestUri(), '.js') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.png') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.ico') !== false ||
                        strpos($this->getRequest()->getRequestUri(), '.gif') !== false
                    ) {
                        header('HTTP/1.1 404 Not Found');
                    }
                default:
                    //记录错误日志
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile() . ' ON LINE ' . $exception->getLine());
                    //显示错误信息                    $this->_view->exception =  $exception;					
            }
			$this->_view->assign('message', nl2br(htmlspecialchars($exception->getMessage(),ENT_QUOTES,'UTF-8')));
			$this->_view->assign('file', htmlspecialchars($exception->getFile(),ENT_QUOTES,'UTF-8')."({$exception->getLine()})");
			$this->_view->assign('line', $this->renderSourceCode($exception->getFile(),$exception->getLine(),25));
			
			$count	=	0;
			$tracer	=	'<table style="width:100%;">';
            foreach($exception->getTrace() as $n => $trace):                
                if($this->isCoreCode($trace))
                    $cssClass='core collapsed';
                elseif(++$count>3)
                    $cssClass='app collapsed';
                else
                    $cssClass='app expanded';
                $hasCode=isset($trace['file']) && $trace['file']!=='unknown' && is_file($trace['file']);
				
				$tracer .=    '<tr class="trace '.$cssClass.'">
                    <td class="number">
                        #'.$n.'
                    </td>
                    <td class="content">
                        <div class="trace-file">';
                            if($hasCode):
                                $tracer .='<div class="plus">+</div>';
                                $tracer .='<div class="minus">–</div>';
                            endif;                            
                            $tracer .='&nbsp;';
                            if(isset($trace['file'])){
                            $tracer .= htmlspecialchars($trace['file'],ENT_QUOTES,'UTF-8')."(".$trace['line'].")";
                            }
                            $tracer .= ': ';
                            if(!empty($trace['class']))
                                $tracer .="<strong>{$trace['class']}</strong>{$trace['type']}";
                            $tracer .="<strong>{$trace['function']}</strong>(";
                            if(!empty($trace['args']))
                                $tracer .=htmlspecialchars($this->argumentsToString($trace['args']),ENT_QUOTES,'UTF-8');
                            $tracer .=')';							
            $tracer .='      </div>';
                        if($hasCode) $tracer .=$this->renderSourceCode($trace['file'],$trace['line'],25);
            $tracer .='  </td>
                </tr>';
            endforeach;
			$tracer .='</table>';
			
			$this->_view->assign('trace', $tracer);
			$this->_view->assign('time', date('Y-m-d H:i:s'));
        } else {
            //禁止输出视图内容
            switch ($exception->getCode()) {
                case YAF_ERR_AUTOLOAD_FAILED:
                case YAF_ERR_NOTFOUND_MODULE:
                case YAF_ERR_NOTFOUND_CONTROLLER:
                case YAF_ERR_NOTFOUND_ACTION:
                case YAF_ERR_NOTFOUND_VIEW:
                    header('HTTP/1.1 404 Not Found');
                    //记录日志
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile());
                    $this->_view->assign('type', 'err404');
                    break;
                default:
                    header('HTTP/1.1 500 Internal Server Error');
                    //记录文件错误日志                  
					Log::out('error', 'I', $exception->getMessage() . ' IN FILE ' . $exception->getFile() . ' ON LINE ' . $exception->getLine());
                    //记录sentry错误日志
                    $this->_view->assign('type', '404');
                    $this->_view->display('404.html');
                    break;
            }
			return false;
        }
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
