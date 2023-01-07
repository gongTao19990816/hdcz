<?php
/*
 module:		api对应的权限表
 create_time:	2022-12-27 14:38:43
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Apiaccess as ApiaccessModel;
use app\admin\service\ApiaccessService;

class Apiaccess extends Admin
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
            $where['purviewval'] = $this->request->param('purviewval', '', 'serach_in');
            $where['role_id'] = $this->request->param('role_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'id,purviewval,role_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'id desc';

            $res = ApiaccessService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'purviewval,role_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiaccessService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $id = $this->request->get('id', '', 'serach_in');
            if (!$id) $this->error('参数错误');
            $this->view->assign('info', checkData(ApiaccessModel::find($id)));
            return view('update');
        } else {
            $postField = 'id,purviewval,role_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiaccessService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            ApiaccessModel::destroy(['id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $id = $this->request->get('id', '', 'serach_in');
        if (!$id) $this->error('参数错误');
        $this->view->assign('info', ApiaccessModel::find($id));
        return view('view');
    }


}

