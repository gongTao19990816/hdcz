<?php

namespace app\ApplicationName\controller;

use app\ApplicationName\service\BaseService;

class Index extends Base
{

    //首页
    public function index()
    {
        $this->view->assign('media', baseService::getMedia());  //网站关键词描述信息
        $this->view->assign('pid', 0);
        $default_themes = config('base.default_themes') ? config('base.default_themes') : 'index';
        return view($default_themes . '/index');
    }


}
