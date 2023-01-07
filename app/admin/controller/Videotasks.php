<?php
/*
 module:		视频任务发布
 create_time:	2022-11-25 13:39:22
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Videotasks as VideotasksModel;
use app\admin\service\VideotasksService;

class Videotasks extends Admin
{


    /*首页数据列表*/
    function index()
    {
        if (!$this->request->isAjax()) {
            return view('index');
        } else {
            echo json_encode(['123' => '456']);
            flushRequest();
            $limit = $this->request->post('limit', 20, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page = floor($offset / $limit) + 1;

            $where = [];
            $where['task_name'] = $this->request->param('task_name', '', 'serach_in');

            $release_time_start = $this->request->param('release_time_start', '', 'serach_in');
            $release_time_end = $this->request->param('release_time_end', '', 'serach_in');

            $where['release_time'] = ['between', [strtotime($release_time_start), strtotime($release_time_end)]];

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'videotasks_id,task_name,video_description,release_time';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videotasks_id desc';

            $res = VideotasksService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'task_name,video_description,release_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideotasksService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $videotasks_id = $this->request->get('videotasks_id', '', 'serach_in');
            if (!$videotasks_id) $this->error('参数错误');
            $this->view->assign('info', checkData(VideotasksModel::find($videotasks_id)));
            return view('update');
        } else {
            $postField = 'videotasks_id,task_name,video_description,release_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideotasksService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('videotasks_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            VideotasksModel::destroy(['videotasks_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $videotasks_id = $this->request->get('videotasks_id', '', 'serach_in');
        if (!$videotasks_id) $this->error('参数错误');
        $this->view->assign('info', VideotasksModel::find($videotasks_id));
        return view('view');
    }


}

