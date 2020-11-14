<?php

namespace app\common\exception;

use think\exception\Handle;
use think\Log;
use Exception;

/**
 * 重写Handle的render方法，实现自定义异常消息
 * Class ExceptionHandler
 * @package app\common\library\exception
 */
class ExceptionHandler extends Handle
{
    private $code;
    private $message;

    /**
     * 输出异常信息
     * @param Exception $e
     * @return \think\Response|\think\response\Json
     */
    public function render(Exception $e)
    {
        if ($e instanceof BaseException) {
            $this->code = $e->code;
            $this->message = $e->message;
        } else {
            if (config('app_debug')) {
                return parent::render($e);
            }
            $this->code = 0;
            $this->message = $e->getMessage() ?: '很抱歉，服务器内部错误';
            $this->recordErrorLog($e);
        }
        return json(['msg' => $this->message, 'code' => $this->code]);
    }

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        // 不使用内置的方式记录异常日志
    }

    /**
     * 将异常写入日志
     * @param Exception $e
     */
    private function recordErrorLog(Exception $e)
    {
        $data = [
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'message' => $this->getMessage($e),
            'code'    => $this->getCode($e),
        ];
        $log = "[{$data['code']}]{$data['message']} [{$data['file']}:{$data['line']}]";
        $log .= "\r\n" . $e->getTraceAsString();
        Log::record($log, 'error');
    }
}
