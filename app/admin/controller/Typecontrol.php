<?php
/*
 module:		类型管理
 create_time:	2022-11-14 17:08:27
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Typecontrol as TypecontrolModel;
use app\admin\service\TypecontrolService;

class Typecontrol extends Admin
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
            $where['type_title'] = $this->request->param('type_title', '', 'serach_in');
            $where['pid'] = $this->request->param('pid', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = '*';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'typecontrol_id desc';

            $res = TypecontrolService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            $res['rows'] = formartList(['typecontrol_id', 'pid', 'type_title', 'type_title'], $res['rows']);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'type_title,pid';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = TypecontrolService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $typecontrol_id = $this->request->get('typecontrol_id', '', 'serach_in');
            if (!$typecontrol_id) $this->error('参数错误');
            $this->view->assign('info', checkData(TypecontrolModel::find($typecontrol_id)));
            return view('update');
        } else {
            $postField = 'typecontrol_id,type_title,pid';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = TypecontrolService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('typecontrol_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            TypecontrolModel::destroy(['typecontrol_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $typecontrol_id = $this->request->get('typecontrol_id', '', 'serach_in');
        if (!$typecontrol_id) $this->error('参数错误');
        $this->view->assign('info', TypecontrolModel::find($typecontrol_id));
        return view('view');
    }


}

