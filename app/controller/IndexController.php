<?php
declare (strict_types=1);

namespace app\controller;

use app\BaseController;
use think\facade\Session;
use think\Response;

class IndexController extends BaseController
{
    /**
     * 显示资源列表
     *
     * @return Response
     */
    public function index()
    {
        if (Session::get('user_id')) {
            return redirect('/user/index');
        }
        return view('index');
    }
}
