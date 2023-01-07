<?php
/*
 module:		关注用户表
 create_time:	2022-12-05 15:53:49
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\FollowuserService;

class Followuser extends Admin
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
            $where['uid'] = $this->request->param('uid', '', 'serach_in');
            $where['b_uid'] = $this->request->param('b_uid', '', 'serach_in');

            $create_time_start = $this->request->param('create_time_start', '', 'serach_in');
            $create_time_end = $this->request->param('create_time_end', '', 'serach_in');

            $where['create_time'] = ['between', [strtotime($create_time_start), strtotime($create_time_end)]];
            $where['type'] = $this->request->param('type', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'followuser_id,uid,b_uid,b_sec_user_id,create_time,type';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'followuser_id desc';

            $res = FollowuserService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

