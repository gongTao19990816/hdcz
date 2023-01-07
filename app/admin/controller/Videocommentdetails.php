<?php
/*
 module:		视频评论详情
 create_time:	2022-11-26 13:44:28
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Videocommentdetails as VideocommentdetailsModel;
use app\admin\service\VideocommentdetailsService;

class Videocommentdetails extends Admin
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
            $where['a.videocomments_id'] = $this->request->param('videocomments_id', '', 'serach_in');
            $where['a.uid'] = $this->request->param('uid', '', 'serach_in');
            $where['a.aweme_id'] = $this->request->param('aweme_id', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');
            $where['a.mode'] = $this->request->param('mode', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.task_name';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videocommentdetails_id desc';

            $res = VideocommentdetailsService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'videocomments_id,uid,aweme_id,text,pay_time,status,failure_reason,mode,exe_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideocommentdetailsService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('videocommentdetails_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            VideocommentdetailsModel::destroy(['videocommentdetails_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

