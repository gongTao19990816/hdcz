<?php
/*
 module:		api_user_rule
 create_time:	2023-01-02 14:54:31
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Apiuserrule as ApiuserruleModel;
use app\admin\service\ApiuserruleService;

class Apiuserrule extends Admin
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
            $where['pid'] = $this->request->param('pid', '', 'serach_in');
            $where['name'] = $this->request->param('name_s', '', 'serach_in');
            $where['title'] = $this->request->param('title', '', 'serach_in');
            $where['remark'] = $this->request->param('remark', '', 'serach_in');
            $where['ismenu'] = $this->request->param('ismenu', '', 'serach_in');

            $createtime_start = $this->request->param('createtime_start', '', 'serach_in');
            $createtime_end = $this->request->param('createtime_end', '', 'serach_in');

            $where['createtime'] = ['between', [strtotime($createtime_start), strtotime($createtime_end)]];
            $where['weigh'] = $this->request->param('weigh', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'id,pid,name,title,remark,ismenu,createtime,updatetime,weigh,status';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'id desc';

            $res = ApiuserruleService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*修改排序开关按钮操作*/
    function updateExt()
    {
        $postField = 'id,status';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (!$data['id']) $this->error('参数错误');
        try {
            ApiuserruleModel::update($data);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'pid,name,title,remark,ismenu,createtime,updatetime,weigh,status';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiuserruleService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $id = $this->request->get('id', '', 'serach_in');
            if (!$id) $this->error('参数错误');
            $this->view->assign('info', checkData(ApiuserruleModel::find($id)));
            return view('update');
        } else {
            $postField = 'id,pid,name,title,remark,ismenu,createtime,updatetime,weigh,status';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiuserruleService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            ApiuserruleModel::destroy(['id' => explode(',', $idx)], true);
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
        $this->view->assign('info', ApiuserruleModel::find($id));
        return view('view');
    }


}

