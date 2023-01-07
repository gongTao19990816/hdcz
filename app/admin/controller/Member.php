<?php
/*
 module:		账户管理
 create_time:	2022-11-03 15:03:02
 author:		
 contact:		
*/

namespace app\admin\controller;

use app\admin\model\Member as MemberModel;
use app\admin\service\MemberService;

class Member extends Admin
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
            $where['a.username'] = $this->request->param('username', '', 'serach_in');
            $where['a.nickname'] = $this->request->param('nickname', '', 'serach_in');
            $where['a.status'] = $this->request->param('status', '', 'serach_in');
            $where['a.equipment_id'] = $this->request->param('equipment_id', '', 'serach_in');
            $where['a.mem_switch'] = $this->request->param('mem_switch', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'a.*,b.equipment_brand';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'member_id desc';

            $res = MemberService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*修改排序开关按钮操作*/
    function updateExt()
    {
        $postField = 'member_id,mem_switch';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (!$data['member_id']) $this->error('参数错误');
        try {
            MemberModel::update($data);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*修改*/
    function update()
    {
        if (!$this->request->isPost()) {
            $member_id = $this->request->get('member_id', '', 'serach_in');
            if (!$member_id) $this->error('参数错误');
            $this->view->assign('info', checkData(MemberModel::find($member_id)));
            return view('update');
        } else {
            $postField = 'member_id,username,pass,nickname,status,headpic,autograph,equipment_id,mem_switch,remarks';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MemberService::update($data);
            return json(['status' => '00', 'msg' => '修改成功']);
        }
    }

    /*删除*/
    function delete()
    {
        $idx = $this->request->post('member_id', '', 'serach_in');
        if (!$idx) $this->error('参数错误');
        try {
            MemberModel::destroy(['member_id' => explode(',', $idx)], true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return json(['status' => '00', 'msg' => '操作成功']);
    }

    /*查看详情*/
    function view()
    {
        $member_id = $this->request->get('member_id', '', 'serach_in');
        if (!$member_id) $this->error('参数错误');
        $this->view->assign('info', MemberModel::find($member_id));
        return view('view');
    }

    /*添加*/
    function add()
    {
        if (!$this->request->isPost()) {
            return view('add');
        } else {
            $postField = 'username,pass,nickname,status,headpic,autograph,equipment_id,mem_switch,remarks';
            $data = $this->request->only(explode(',', $postField), 'post', null);
            $res = MemberService::add($data);
            return json(['status' => '00', 'msg' => '添加成功']);
        }
    }

    /*详情*/
    function listdata()
    {
        if (!$this->request->isAjax()) {
            return view('listdata');
        } else {
            $limit = $this->request->post('limit', 20, 'intval');
            $offset = $this->request->post('offset', 0, 'intval');
            $page = floor($offset / $limit) + 1;

            $where = [];
            $where['username'] = $this->request->param('username', '', 'serach_in');
            $where['nickname'] = $this->request->param('nickname', '', 'serach_in');
            $where['status'] = $this->request->param('status', '', 'serach_in');
            $where['equipment_id'] = $this->request->param('equipment_id', '', 'serach_in');
            $where['mem_switch'] = $this->request->param('mem_switch', '', 'serach_in');

            $order = $this->request->post('order', '', 'serach_in');    //排序字段 bootstrap-table 传入
            $sort = $this->request->post('sort', '', 'serach_in');        //排序方式 desc 或 asc

            $field = 'member_id,username,pass,nickname,status,headpic,fans,dianzan,play,autograph,equipment_id,mem_switch,remarks';
            $orderby = ($sort && $order) ? $sort . ' ' . $order : 'member_id desc';

            $res = MemberService::listdataList(formatWhere($where), $field, $orderby, $limit, $page);
            return json($res);
        }
    }

    /*start*/
    /*登录*/
    function login()
    {
        $login_url = config('my.main_link') . config('my.link_url.login');//登录接口
        $profile_self = config('my.main_link') . config('my.link_url.userinfo');
        $member_id = $this->request->get('member_id', '', 'serach_in');
        if (!$member_id) $this->error('参数错误');
        $member_info = MemberModel::find($member_id);
        if ($member_info) {
// 			$login = json_decode($this->doHttpPost($login_url,['username'=>$member_info['username'],'password'=>$member_info['pass']]),ture);
            $userinfo = json_decode($this->doHttpPosts($profile_self, $member_info['token'], $member_info['access_token']), ture);
            $headers = json_encode($userinfo['request']['headers']);
            $geturl = $this->curl_get($userinfo['request']['url'], $headers);
            echo '<pre>';
            print_r($geturl);
            die;
        }
        return view('login');
    }

//请求接口
    private function doHttpPost($url, $data)
    {
        //var_dump($data);die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }

    //请求接口
    private function doHttpPosts($url, $data, $access)
    {
        $header = array(
            'Content-Type: application/json;charset=utf-8',
            'Accept: application/json',
            'Authorization:Bearer ' . $access
        );
        // $header['Authorization'] = 'Bearer '.$access;
        //print_r($header);die;
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            // curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_POSTFIELDS => $data
        ));
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $Json = json_decode($response);
        $request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        // print_r($request_header);die;
        $link = $Json->data->payment_link;

        return $response;

    }

    //get
    function curl_get($url, $header = array())
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $data = curl_exec($curl);
        if (curl_error($curl)) {
            print "Error: " . curl_error($curl);
        } else {
            curl_close($curl);
            return $data;
        }
    }
    /*end*/


}

