<?php
/*
 module:		分组表
 create_time:	2022-12-27 14:39:21
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Apirole as ApiroleModel;
use app\admin\service\ApiroleService;

class Apirole extends Admin
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
            $where['name'] = $this->request->param('name_s', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['pid'] = $this->request->param('pid', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'role_id,name,status,pid,description';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'role_id desc';

            $res = ApiroleService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'name,status,pid,description';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiroleService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $role_id = $this->request->get('role_id', '', 'serach_in');
            if (!$role_id) $this->error('参数错误');
            $this->view->assign('info', checkData(ApiroleModel::find($role_id)));
            return view('update');
        } else {
            $postField = 'role_id,name,status,pid,description';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiroleService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('role_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            ApiroleModel::destroy(['role_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $role_id = $this->request->get('role_id', '', 'serach_in');
        if (!$role_id) $this->error('参数错误');
        $this->view->assign('info', ApiroleModel::find($role_id));
        return view('view');
    }


}

