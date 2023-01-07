<?php
/*
 module:		任务表
 create_time:	2022-12-09 16:25:31
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Tasklist as TasklistModel;
use app\admin\service\TasklistService;

class Tasklist extends Admin
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
            $where['task_name'] = $this->request->param('task_name', '', 'serach_in');
            $where['task_type'] = $this->request->param('task_type', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'tasklist_id,task_name,task_type,task_num,create_time,status';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'tasklist_id desc';

            $res = TasklistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'status,create_time,task_num,task_type,task_name';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = TasklistService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $tasklist_id = $this->request->get('tasklist_id', '', 'serach_in');
            if (!$tasklist_id) $this->error('参数错误');
            $this->view->assign('info', checkData(TasklistModel::find($tasklist_id)));
            return view('update');
        } else {
            $postField = 'tasklist_id,status,create_time,task_num,task_type,task_name';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = TasklistService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('tasklist_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            TasklistModel::destroy(['tasklist_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $tasklist_id = $this->request->get('tasklist_id', '', 'serach_in');
        if (!$tasklist_id) $this->error('参数错误');
        $this->view->assign('info', TasklistModel::find($tasklist_id));
        return view('view');
    }


}

