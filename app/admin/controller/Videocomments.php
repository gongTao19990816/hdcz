<?php
/*
 module:		视频评论任务
 create_time:	2022-11-26 13:46:15
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Videocomments as VideocommentsModel;
use app\admin\service\VideocommentsService;

class Videocomments extends Admin
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
            $where['mode'] = $this->request->param('mode', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'videocomments_id,task_name,comments,add_time,mode';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'videocomments_id desc';

            $res = VideocommentsService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'task_name,comments,add_time,mode';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideocommentsService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $videocomments_id = $this->request->get('videocomments_id', '', 'serach_in');
            if (!$videocomments_id) $this->error('参数错误');
            $this->view->assign('info', checkData(VideocommentsModel::find($videocomments_id)));
            return view('update');
        } else {
            $postField = 'videocomments_id,task_name,comments,add_time,mode';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideocommentsService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('videocomments_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            VideocommentsModel::destroy(['videocomments_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

