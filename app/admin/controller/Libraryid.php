<?php
/*
 module:		需要关注的id库
 create_time:	2022-12-13 15:10:03
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Libraryid as LibraryidModel;
use app\admin\service\LibraryidService;

class Libraryid extends Admin
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
            $where['name'] = $this->request->param('name_s', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'libraryid_id,name';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'libraryid_id desc';

            $res = LibraryidService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'name';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = LibraryidService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $libraryid_id = $this->request->get('libraryid_id', '', 'serach_in');
            if (!$libraryid_id) $this->error('参数错误');
            $this->view->assign('info', checkData(LibraryidModel::find($libraryid_id)));
            return view('update');
        } else {
            $postField = 'libraryid_id,name';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = LibraryidService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('libraryid_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            LibraryidModel::destroy(['libraryid_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

