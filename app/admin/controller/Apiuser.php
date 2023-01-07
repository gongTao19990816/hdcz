<?php
/*
 module:		api_user
 create_time:	2023-01-02 14:26:58
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Apiuser as ApiuserModel;
use app\admin\service\ApiuserService;

class Apiuser extends Admin
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
            $where['group_id'] = $this->request->param('group_id', '', 'serach_in');
            $where['username'] = $this->request->param('username', '', 'serach_in');

            $createtime_start = $this->request->param('createtime_start', '', 'serach_in');
            $createtime_end = $this->request->param('createtime_end', '', 'serach_in');

            $where['createtime'] = ['between', [strtotime($createtime_start), strtotime($createtime_end)]];
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['phone'] = $this->request->param('phone', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'id,group_id,username,password,email,createtime,updatetime,status,phone';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'id desc';

            $res = ApiuserService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'group_id,username,nickname,password,salt,email,mobile,avatar,level,gender,birthday,bio,money,score,successions,maxsuccessions,prevtime,logintime,loginip,loginfailure,joinip,jointime,createtime,updatetime,token,status,verification,phone';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiuserService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $id = $this->request->get('id', '', 'serach_in');
            if (!$id) $this->error('参数错误');
            $this->view->assign('info', checkData(ApiuserModel::find($id)));
            return view('update');
        } else {
            $postField = 'id,group_id,username,nickname,password,salt,email,mobile,avatar,level,gender,birthday,bio,money,score,successions,maxsuccessions,prevtime,logintime,loginip,loginfailure,joinip,jointime,createtime,updatetime,token,status,verification,phone';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = ApiuserService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            ApiuserModel::destroy(['id' => explode(',', $idx)], true);
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
        $this->view->assign('info', ApiuserModel::find($id));
        return view('view');
    }


}

