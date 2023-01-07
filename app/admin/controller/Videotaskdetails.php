<?php
/*
 module:		视频任务详情
 create_time:	2022-11-25 13:49:30
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Videotaskdetails as VideotaskdetailsModel;
use app\admin\service\VideotaskdetailsService;

class Videotaskdetails extends Admin
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
            $where['a.videotasks_id'] = $this->request->param('videotasks_id', '', 'serach_in');
            $where['a.uid'] = $this->request->param('uid', '', 'serach_in');
            $where['a.video_url'] = $this->request->param('video_url', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.task_name';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videotaskdetails_id desc';

            $res = VideotaskdetailsService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'videotasks_id,uid,video_url,pay_time,status';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideotaskdetailsService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $videotaskdetails_id = $this->request->get('videotaskdetails_id', '', 'serach_in');
            if (!$videotaskdetails_id) $this->error('参数错误');
            $this->view->assign('info', checkData(VideotaskdetailsModel::find($videotaskdetails_id)));
            return view('update');
        } else {
            $postField = 'videotaskdetails_id,videotasks_id,uid,video_url,pay_time,status';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideotaskdetailsService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('videotaskdetails_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            VideotaskdetailsModel::destroy(['videotaskdetails_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $videotaskdetails_id = $this->request->get('videotaskdetails_id', '', 'serach_in');
        if (!$videotaskdetails_id) $this->error('参数错误');
        $this->view->assign('info', VideotaskdetailsModel::find($videotaskdetails_id));
        return view('view');
    }


}

