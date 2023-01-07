<?php
/*
 module:		私信素材库
 create_time:	2022-12-10 15:58:08
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\PrivateLetterService;

class PrivateLetter extends Admin
{


    /*首页数据列表*/
    function index()
    {
        if (!$this->request->isAjax()) {
            return view('index');
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page = floor($offset / $limit) + 1;

            $where = [];
            $where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
            $where['grouping_id'] = $this->request->param('grouping_id', '', 'serach_in');
            $where['type'] = $this->request->param('type', '', 'serach_in');
            $where['content'] = $this->request->param('content', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'privateletter_id,typecontrol_id,grouping_id,type,content';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'privateletter_id desc';

            $res = PrivateLetterService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

