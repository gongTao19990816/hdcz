<?php
/*
 module:		任务详情
 create_time:	2022-12-07 11:55:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\QueueDetail as QueueDetailModel;
use app\admin\service\QueueDetailService;

class QueueDetail extends Admin
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
            $where['a.queue_id'] = $this->request->param('queue_id', '', 'serach_in');
            $where['a.request_param'] = $this->request->param('request_param', '', 'serach_in');
            $where['a.respone'] = $this->request->param('respone', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');

            $execution_time_start = $this->request->param('execution_time_start', '', 'serach_in');
            $execution_time_end = $this->request->param('execution_time_end', '', 'serach_in');

            $where['a.execution_time'] = ['between', [strtotime($execution_time_start), strtotime($execution_time_end)]];

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = '';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'detail_id desc';

            $res = QueueDetailService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'queue_id,request_param,respone,status,execution_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = QueueDetailService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $detail_id = $this->request->get('detail_id', '', 'serach_in');
            if (!$detail_id) $this->error('参数错误');
            $this->view->assign('info', checkData(QueueDetailModel::find($detail_id)));
            return view('update');
        } else {
            $postField = 'detail_id,queue_id,request_param,respone,status,execution_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = QueueDetailService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('detail_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            QueueDetailModel::destroy(['detail_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $detail_id = $this->request->get('detail_id', '', 'serach_in');
        if (!$detail_id) $this->error('参数错误');
        $this->view->assign('info', QueueDetailModel::find($detail_id));
        return view('view');
    }


}

