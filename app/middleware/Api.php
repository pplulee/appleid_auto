<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response\Json;

class Api
{
    public function handle($request, Closure $next)
    {
        if (!$request->isPost()) {
            return json(["code" => 403, "msg" => "请求方式错误"]);
        }
        $key = $request->header('key');
        if (!$key || $key == ""){
            return json(['code' => 401, 'msg' => '未提供密钥']);
        } else {
            $apikey = env('API_KEY');
            if ($key != $apikey){
                return json(['code' => 403, 'msg' => '密钥错误']);
            } else {
                return $next($request);
            }
        }
    }
}
