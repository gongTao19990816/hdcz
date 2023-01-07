<?php
/*
 module:		粉丝
 create_time:	2022-11-24 15:15:40
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Fanslist as FanslistModel;
use app\admin\service\FanslistService;

class Fanslist extends Admin
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
            $where['member_id'] = $this->request->param('member_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'fanslist_id,nickname,member_id,create_time,avatar_thumb,sec_uid,following_count,follower_count,favoriting_count,unique_id,uid,aweme_count,region';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'fanslist_id desc';

            $res = FanslistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'nickname,member_id,create_time,avatar_thumb,sec_uid,following_count,follower_count,favoriting_count,unique_id,uid,aweme_count,region';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = FanslistService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $fanslist_id = $this->request->get('fanslist_id', '', 'serach_in');
            if (!$fanslist_id) $this->error('参数错误');
            $this->view->assign('info', checkData(FanslistModel::find($fanslist_id)));
            return view('update');
        } else {
            $postField = 'fanslist_id,nickname,member_id,create_time,avatar_thumb,sec_uid,following_count,follower_count,favoriting_count,unique_id,uid,aweme_count,region';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = FanslistService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('fanslist_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            FanslistModel::destroy(['fanslist_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $fanslist_id = $this->request->get('fanslist_id', '', 'serach_in');
        if (!$fanslist_id) $this->error('参数错误');
        $this->view->assign('info', FanslistModel::find($fanslist_id));
        return view('view');
    }


}

