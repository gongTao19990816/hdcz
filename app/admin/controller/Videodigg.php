<?php
/*
 module:		作品点赞
 create_time:	2022-12-05 16:53:07
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\VideodiggService;

class Videodigg extends Admin
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
            $where['type'] = $this->request->param('type', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'videodigg_id,uid,aweme_id,create_time,type';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videodigg_id desc';

            $res = VideodiggService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

