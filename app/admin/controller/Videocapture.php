<?php
/*
 module:		视频采集
 create_time:	2022-12-13 21:41:05
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Videocapture as VideocaptureModel;
use app\admin\service\VideocaptureService;

class Videocapture extends Admin
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
            $where['country'] = $this->request->param('country', '', 'serach_in');
            $where['aweme_id'] = $this->request->param('aweme_id', '', 'serach_in');
            $where['ifvideo'] = $this->request->param('ifvideo', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'video_capture_id,uid,country,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,addtime,ifvideo';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'video_capture_id desc';

            $res = VideocaptureService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'uid,country,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,addtime,ifvideo';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideocaptureService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $video_capture_id = $this->request->get('video_capture_id', '', 'serach_in');
            if (!$video_capture_id) $this->error('参数错误');
            $this->view->assign('info', checkData(VideocaptureModel::find($video_capture_id)));
            return view('update');
        } else {
            $postField = 'video_capture_id,uid,country,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,addtime,ifvideo';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = VideocaptureService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('video_capture_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            VideocaptureModel::destroy(['video_capture_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $video_capture_id = $this->request->get('video_capture_id', '', 'serach_in');
        if (!$video_capture_id) $this->error('参数错误');
        $this->view->assign('info', VideocaptureModel::find($video_capture_id));
        return view('view');
    }

    /*批量编辑数据*/
    function Download()
    {
        if (!$this->request->isPost()) {
            $video_capture_id = $this->request->get('video_capture_id', '', 'serach_in');
            if (!$video_capture_id) $this->error('参数错误');
            $this->view->assign('info', ['video_capture_id' => $video_capture_id]);
            return view('Download');
        } else {
            $postField = 'video_capture_id,ifvideo';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $where['video_capture_id'] = explode(',', $data['video_capture_id']);
            unset($data['video_capture_id']);
            try {
                db('video_capture')->where($where)->update($data);
            } catch (\Exception $e) {
                abort(config('my.error_log_code'), $e->getMessage());
            }
            return json(['status' => '00', 'msg' => '操作成功']);
        }
    }


}

