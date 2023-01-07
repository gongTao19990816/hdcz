<?php
/*
 module:		分组管理
 create_time:	2022-12-01 16:06:21
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Grouping as GroupingModel;
use app\admin\service\GroupingService;

class Grouping extends Admin
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
            $where['grouping'] = $this->request->param('grouping', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'grouping_id,grouping,add_time';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'grouping_id desc';

            $res = GroupingService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'add_time,grouping';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = GroupingService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $grouping_id = $this->request->get('grouping_id', '', 'serach_in');
            if (!$grouping_id) $this->error('参数错误');
            $this->view->assign('info', checkData(GroupingModel::find($grouping_id)));
            return view('update');
        } else {
            $postField = 'grouping_id,add_time,grouping';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = GroupingService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('grouping_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            GroupingModel::destroy(['grouping_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $grouping_id = $this->request->get('grouping_id', '', 'serach_in');
        if (!$grouping_id) $this->error('参数错误');
        $this->view->assign('info', GroupingModel::find($grouping_id));
        return view('view');
    }


}

