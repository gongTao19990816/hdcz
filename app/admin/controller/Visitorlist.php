<?php
/*
 module:		来访列表
 create_time:	2022-12-05 20:33:15
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\VisitorlistService;

class Visitorlist extends Admin
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
            $where['nickname'] = $this->request->param('nickname', '', 'serach_in');
            $where['country'] = $this->request->param('country', '', 'serach_in');
            $where['ifpic'] = $this->request->param('ifpic', '', 'serach_in');
            $where['member_id'] = $this->request->param('member_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'visitorlist_id,unique_id,avatar_thumb,sec_uid,nickname,signature,follower_status,following_count,total_favorited,country,aweme_count,ifpic,member_id';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'visitorlist_id desc';

            $res = VisitorlistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

