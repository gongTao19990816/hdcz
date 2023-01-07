<?php
/*
 module:		任务详情日志
 create_time:	2022-12-10 13:41:56
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\TasklistdetaillogService;

class Tasklistdetaillog extends Admin
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

            $s_time_start = $this->request->param('s_time_start', '', 'serach_in');
            $s_time_end = $this->request->param('s_time_end', '', 'serach_in');

            $where['s_time'] = ['between', [strtotime($s_time_start), strtotime($s_time_end)]];
            $where['tasklistdetail_id'] = $this->request->param('tasklistdetail_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'tasklistdetaillog_id,s_time,result,tasklistdetail_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'tasklistdetaillog_id desc';

            $res = TasklistdetaillogService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

