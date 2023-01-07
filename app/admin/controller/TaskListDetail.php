<?php
/*
 module:		任务明细表
 create_time:	2022-12-09 16:34:00
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\TaskListDetailService;

class TaskListDetail extends Admin
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
            $where['a.status'] = $this->request->param('status', '', 'serach_in');
            $where['a.tasklist_id'] = $this->request->param('tasklist_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'tasklistdetail_id,parameter,status,create_time,reason,tasklist_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'tasklistdetail_id desc';

            $res = TaskListDetailService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

