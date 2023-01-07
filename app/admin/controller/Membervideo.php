<?php
/*
 module:		用户作品
 create_time:	2022-11-23 16:29:39
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Membervideo as MembervideoModel;
use app\admin\service\MembervideoService;

class Membervideo extends Admin
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
            $where['a.member_id'] = $this->request->param('member_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.nickname';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'membervideo_id desc';

            $res = MembervideoService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'member_id,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,video_pic_url,addtime';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MembervideoService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $membervideo_id = $this->request->get('membervideo_id', '', 'serach_in');
            if (!$membervideo_id) $this->error('参数错误');
            $this->view->assign('info', checkData(MembervideoModel::find($membervideo_id)));
            return view('update');
        } else {
            $postField = 'membervideo_id,member_id,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,video_pic_url,addtime';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MembervideoService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('membervideo_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            MembervideoModel::destroy(['membervideo_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $membervideo_id = $this->request->get('membervideo_id', '', 'serach_in');
        if (!$membervideo_id) $this->error('参数错误');
        $this->view->assign('info', MembervideoModel::find($membervideo_id));
        return view('view');
    }


}

