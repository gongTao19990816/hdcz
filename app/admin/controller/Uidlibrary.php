<?php
/*
 module:		uid库
 create_time:	2022-12-13 15:37:27
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Uidlibrary as UidlibraryModel;
use app\admin\service\UidlibraryService;

class Uidlibrary extends Admin
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
            $where['a.libraryid_id'] = $this->request->param('libraryid_id', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.name';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'uidlibrary_id desc';

            $res = UidlibraryService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'libraryid_id,url,add_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = UidlibraryService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $uidlibrary_id = $this->request->get('uidlibrary_id', '', 'serach_in');
            if (!$uidlibrary_id) $this->error('参数错误');
            $this->view->assign('info', checkData(UidlibraryModel::find($uidlibrary_id)));
            return view('update');
        } else {
            $postField = 'uidlibrary_id,libraryid_id,uid,sec_uid,url,add_time';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = UidlibraryService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('uidlibrary_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            UidlibraryModel::destroy(['uidlibrary_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $uidlibrary_id = $this->request->get('uidlibrary_id', '', 'serach_in');
        if (!$uidlibrary_id) $this->error('参数错误');
        $this->view->assign('info', UidlibraryModel::find($uidlibrary_id));
        return view('view');
    }


}

