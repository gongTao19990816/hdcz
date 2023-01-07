<?php
/*
 module:		头像库管理
 create_time:	2022-11-29 19:23:55
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Headimage as HeadimageModel;
use app\admin\service\HeadimageService;

class Headimage extends Admin
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
            $where['a.typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.type_title';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'headimage_id desc';

            $res = HeadimageService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'image,typecontrol_id,usage_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            print_r($data['image']);
            die;
            $res = HeadimageService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $headimage_id = $this->request->get('headimage_id', '', 'serach_in');
            if (!$headimage_id) $this->error('参数错误');
            $this->view->assign('info', checkData(HeadimageModel::find($headimage_id)));
            return view('update');
        } else {
            $postField = 'headimage_id,image,typecontrol_id,status,usage_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = HeadimageService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('headimage_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            HeadimageModel::destroy(['headimage_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $headimage_id = $this->request->get('headimage_id', '', 'serach_in');
        if (!$headimage_id) $this->error('参数错误');
        $this->view->assign('info', HeadimageModel::find($headimage_id));
        return view('view');
    }


}

