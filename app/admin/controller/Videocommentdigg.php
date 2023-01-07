<?php
/*
 module:		作品评论点赞
 create_time:	2022-12-05 18:11:42
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\VideocommentdiggService;

class Videocommentdigg extends Admin
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
            $where['aweme_id'] = $this->request->param('aweme_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'videocommentdigg_id,uid,aweme_id,cid,create_time';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videocommentdigg_id desc';

            $res = VideocommentdiggService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

