<?php
/*
 module:		菜单管理
 create_time:	2022-12-27 14:14:56
 author:		大怪兽
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Menulist as MenulistModel;
use app\admin\service\MenulistService;

class Menulist extends Admin
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
            $where['menu_name'] = ['like', $this->request->param('menu_name', '', 'serach_in')];
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['pid'] = $this->request->param('pid', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'menulist_id,menu_name,url,menu_icon,status,pid';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'menulist_id desc';

            $res = MenulistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'menu_name,url,menu_icon,status,pid';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MenulistService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $menulist_id = $this->request->get('menulist_id', '', 'serach_in');
            if (!$menulist_id) $this->error('参数错误');
            $this->view->assign('info', checkData(MenulistModel::find($menulist_id)));
            return view('update');
        } else {
            $postField = 'menulist_id,menu_name,url,menu_icon,status,pid';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MenulistService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('menulist_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            MenulistModel::destroy(['menulist_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }


}

