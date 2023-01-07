<?php
/*
 module:		主题内容素材
 create_time:	2022-12-10 16:41:59
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\service\SubjectcontentService;

class Subjectcontent extends Admin
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
            $where['typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
            $where['grouping_id'] = $this->request->param('grouping_id', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'subjectcontent_id,content,use_num,typecontrol_id,grouping_id,add_time,status';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'subjectcontent_id desc';

            $res = SubjectcontentService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }


}

