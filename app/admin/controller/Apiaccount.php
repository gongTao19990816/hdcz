<?php
/*
 module:		api账户表
 create_time:	2022-12-27 14:42:50
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Apiaccount as ApiaccountModel;
use app\admin\service\ApiaccountService;

class Apiaccount extends Admin
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
            $where['user'] = $this->request->param('user', '', 'serach_in');
            $where['role_id'] = $this->request->param('role_id', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'user_id,name,user,role_id,note,status,create_time';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'user_id desc';

            $res = ApiaccountService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'name,user,pwd,role_id,note,status,create_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiaccountService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $user_id = $this->request->get('user_id', '', 'serach_in');
            if (!$user_id) $this->error('参数错误');
            $this->view->assign('info', checkData(ApiaccountModel::find($user_id)));
            return view('update');
        } else {
            $postField = 'user_id,name,user,pwd,role_id,note,status,create_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiaccountService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('user_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            ApiaccountModel::destroy(['user_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $user_id = $this->request->get('user_id', '', 'serach_in');
        if (!$user_id) $this->error('参数错误');
        $this->view->assign('info', ApiaccountModel::find($user_id));
        return view('view');
    }


}

