<?php
/*
 module:		设备管理
 create_time:	2022-11-03 14:24:13
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Equipment as EquipmentModel;
use app\admin\service\EquipmentService;

class Equipment extends Admin
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
            $where['status'] = $this->request->param('status', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'equipment_id,deviceip,ipattribution,equipment_brand,status,work_time,remarks';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'equipment_id desc';

            $res = EquipmentService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'deviceip,ipattribution,equipment_brand,status,work_time,remarks';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = EquipmentService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $equipment_id = $this->request->get('equipment_id', '', 'serach_in');
            if (!$equipment_id) $this->error('参数错误');
            $this->view->assign('info', checkData(EquipmentModel::find($equipment_id)));
            return view('update');
        } else {
            $postField = 'equipment_id,deviceip,ipattribution,equipment_brand,status,work_time,remarks';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = EquipmentService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('equipment_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            EquipmentModel::destroy(['equipment_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

