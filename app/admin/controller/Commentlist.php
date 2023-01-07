<?php
/*
 module:		comment_list
 create_time:	2022-11-23 19:40:59
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Commentlist as CommentlistModel;
use app\admin\service\CommentlistService;

class Commentlist extends Admin
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
            $where['cid'] = $this->request->param('cid', '', 'serach_in');
            $where['comment_language'] = $this->request->param('comment_language', '', 'serach_in');
            $where['text'] = $this->request->param('text', '', 'serach_in');
            $where['create_time'] = $this->request->param('create_time', '', 'serach_in');
            $where['digg_count'] = $this->request->param('digg_count', '', 'serach_in');
            $where['aweme_id'] = $this->request->param('aweme_id', '', 'serach_in');
            $where['reply_id'] = $this->request->param('reply_id', '', 'serach_in');
            $where['reply_comment_total'] = $this->request->param('reply_comment_total', '', 'serach_in');
            $where['uid'] = $this->request->param('uid', '', 'serach_in');
            $where['sec_uid'] = $this->request->param('sec_uid', '', 'serach_in');
            $where['avatar_medium'] = $this->request->param('avatar_medium', '', 'serach_in');
            $where['nickname'] = $this->request->param('nickname', '', 'serach_in');
            $where['unique_id'] = $this->request->param('unique_id', '', 'serach_in');
            $where['aweme_count'] = $this->request->param('aweme_count', '', 'serach_in');
            $where['following_count'] = $this->request->param('following_count', '', 'serach_in');
            $where['follower_count'] = $this->request->param('follower_count', '', 'serach_in');
            $where['total_favorited'] = $this->request->param('total_favorited', '', 'serach_in');
            $where['signature'] = $this->request->param('signature', '', 'serach_in');
            $where['account_region'] = $this->request->param('account_region', '', 'serach_in');
            $where['membervideo_id'] = $this->request->param('membervideo_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'id desc';

            $res = CommentlistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = CommentlistService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $id = $this->request->get('id', '', 'serach_in');
            if (!$id) $this->error('参数错误');
            $this->view->assign('info', checkData(CommentlistModel::find($id)));
            return view('update');
        } else {
            $postField = 'id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = CommentlistService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            CommentlistModel::destroy(['id' => explode(',', $idx)], true);
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
        $this->view->assign('info', CommentlistModel::find($id));
        return view('view');
    }


}

