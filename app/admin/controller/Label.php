<?php
/*
 module:		标签素材
 create_time:	2022-12-02 15:14:38
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Label as LabelModel;
use app\admin\service\LabelService;

class Label extends Admin
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
            $where['label'] = $this->request->param('label', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
            $where['grouping_id'] = $this->request->param('grouping_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'label_id,add_time,label,status,usage_time,typecontrol_id,grouping_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'label_id desc';

            $res = LabelService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'add_time,label,status,usage_time,typecontrol_id,grouping_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = LabelService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $label_id = $this->request->get('label_id', '', 'serach_in');
            if (!$label_id) $this->error('参数错误');
            $this->view->assign('info', checkData(LabelModel::find($label_id)));
            return view('update');
        } else {
            $postField = 'label_id,add_time,label,status,usage_time,typecontrol_id,grouping_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = LabelService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('label_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            LabelModel::destroy(['label_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

