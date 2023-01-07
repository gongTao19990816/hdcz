<?php
/*
 module:		external_member
 create_time:	2022-12-13 13:15:07
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\ExternalmemberService;

class Externalmember extends Admin
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
            $where['uid'] = $this->request->param('uid', '', 'serach_in');
            $where['nickname'] = ['like', $this->request->param('nickname', '', 'serach_in')];
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['sources'] = $this->request->param('sources', '', 'serach_in');
            $where['label'] = $this->request->param('label', '', 'serach_in');
            $where['if_collection'] = $this->request->param('if_collection', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'external_member_id,uid,avatar_thumb,follower_status,following_count,total_favorited,nickname,unique_id,signature,status,country,member_type,sec_uid,aweme_count,updata_time,ifpic,unread_viewer_count,addtime,sources,label,if_collection';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'external_member_id desc';

            $res = ExternalmemberService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

