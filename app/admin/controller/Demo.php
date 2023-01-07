<?php
/*
 module:		doem
 create_time:	2023-01-01 21:45:59
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Demo as DemoModel;
use app\admin\service\DemoService;

class Demo extends Admin
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

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'doem_id,user_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'doem_id desc';

            $res = DemoService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'user_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = DemoService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $doem_id = $this->request->get('doem_id', '', 'serach_in');
            if (!$doem_id) $this->error('参数错误');
            $this->view->assign('info', checkData(DemoModel::find($doem_id)));
            return view('update');
        } else {
            $postField = 'doem_id,user_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = DemoService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('doem_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            DemoModel::destroy(['doem_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $doem_id = $this->request->get('doem_id', '', 'serach_in');
        if (!$doem_id) $this->error('参数错误');
        $this->view->assign('info', DemoModel::find($doem_id));
        return view('view');
    }


}

