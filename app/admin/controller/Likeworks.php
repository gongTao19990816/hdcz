<?php
/*
 module:		作品点赞表
 create_time:	2022-11-24 15:47:44
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\LikeworksService;

class Likeworks extends Admin
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
            $where['nickname'] = $this->request->param('nickname', '', 'serach_in');

            $add_time_start = $this->request->param('add_time_start', '', 'serach_in');
            $add_time_end = $this->request->param('add_time_end', '', 'serach_in');

            $where['add_time'] = ['between', [strtotime($add_time_start), strtotime($add_time_end)]];
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'likeworks_id,uid,nickname,aweme_id,add_time,status';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'likeworks_id desc';

            $res = LikeworksService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

