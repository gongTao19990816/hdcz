<?php
/*
 module:		视频素材
 create_time:	2022-11-29 19:19:57
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Material as MaterialModel;
use app\admin\service\MaterialService;

class Material extends Admin
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
            $where['a.video_num'] = ['like', $this->request->param('video_num', '', 'serach_in')];

            $add_time_start = $this->request->param('add_time_start', '', 'serach_in');
            $add_time_end = $this->request->param('add_time_end', '', 'serach_in');

            $where['a.add_time'] = ['between', [strtotime($add_time_start), strtotime($add_time_end)]];
            $where['a.typecontrol_id'] = $this->request->param('typecontrol_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.type_title';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'material_id desc';

            $res = MaterialService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'pic,add_time,typecontrol_id,video_url';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MaterialService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $material_id = $this->request->get('material_id', '', 'serach_in');
            if (!$material_id) $this->error('参数错误');
            $this->view->assign('info', checkData(MaterialModel::find($material_id)));
            return view('update');
        } else {
            $postField = 'material_id,pic,add_time,typecontrol_id,video_url';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MaterialService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('material_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            MaterialModel::destroy(['material_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $material_id = $this->request->get('material_id', '', 'serach_in');
        if (!$material_id) $this->error('参数错误');
        $this->view->assign('info', MaterialModel::find($material_id));
        return view('view');
    }


}

