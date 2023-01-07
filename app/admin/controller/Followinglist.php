<?php
/*
 module:		关注
 create_time:	2022-11-24 14:10:43
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\FollowinglistService;

class Followinglist extends Admin
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

            $field = 'followinglist_id,nickname,member_id,create_time,avatar_thumb,sec_uid,following_count,follower_count,favoriting_count,unique_id,uid,aweme_count,region';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'followinglist_id desc';

            $res = FollowinglistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

