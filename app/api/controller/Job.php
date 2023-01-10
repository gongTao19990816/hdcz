<?php

namespace app\api\controller;

class Job extends Common
{
    //每日自动更新用户数据（用获取个人的接口，请求需要带上代理）
    function Dsuser()
    {
        flushRequest();
        $user = db('member')->where('status', 1)->field('token,uid')->select();

        $redis_key = get_task_key('get_self_user_info');
        $addtask['task_name'] = '每日自动更新用户数据';
        $addtask['task_type'] = 'GetSelfUserInfo';
        $addtask['task_num'] = count($user);
        $addtask['redis_key'] = $redis_key;
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        $redis = connectRedis();
        $task_details = [];
        foreach ($user as $k => $v) {
            $v['proxy'] = getHttpProxy($v['uid']);
            $v['token'] = json_decode($v['token'], true);
            // var_dump(json_encode($v));die;
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetSelfUserInfo';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;

            $task_details[] = $adddata;
        }

        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }

    }

    //更新粉丝
    function Dsfensi()
    {
        flushRequest();
        $user = db('member')->where('follower_status', '>', 0)->where('status', 1)->field('sec_uid,uid')->select();
        $redis_key = get_task_key('get_fans_list');
        $addtask['task_name'] = '更新账户粉丝列表';
        $addtask['task_type'] = 'GetFansList';
        $addtask['redis_key'] = $redis_key;
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);

        $redis = connectRedis();
        $task_details = [];
        foreach ($user as $k => $v) {
            $v['max_time'] = 0;
            $v['token'] = doToken('', 2);
            $v['proxy'] = getHttpProxy($v['token']['user']['uid']);
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetFansList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;
            $task_details[] = $adddata;
        }

        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
        // var_dump($user);
    }

    //更新关注列表
    function Dsgzhu()
    {
        flushRequest();
        $user = db('member')->where('following_count', '>', 0)->where('status', 1)->field('sec_uid,uid')->select();
        // var_dump($user);die;
        $redis_key = get_task_key('get_follow_list');
        $addtask['task_name'] = '更新账户关注列表';
        $addtask['task_type'] = 'GetFollowList';
        $addtask['redis_key'] = $redis_key;
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);

        $redis = connectRedis();
        $task_details = [];
        foreach ($user as $k => $v) {
            $v['max_time'] = 0;
            // var_dump($v);die;
            $v['token'] = doToken('', 2);
            $v['proxy'] = getHttpProxy($v['token']['user']['uid']);
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetFollowList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;
            $task_details[] = $adddata;
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    //获取用户最新的作品
    function Dsuservideo()
    {
        flushRequest();
        $user = db('member')->where('aweme_count', '>', 0)->where('status', 1)->field('sec_uid,uid')->select();
        // var_dump($user);die;
        $redis_key = get_task_key('get_aweme_list');
        $addtask['redis_key'] = $redis_key;
        $addtask['task_name'] = '更新账户作品';
        $addtask['task_type'] = 'GetAwemeList';
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);

        $redis = connectRedis();
        $task_details = [];
        foreach ($user as $k => $v) {
            $v['max_cursor'] = 0;
            // var_dump($v);die;
            $v['token'] = doToken('', 2);
            $v['proxy'] = getHttpProxy($v['token']['user']['uid']);
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetAwemeList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;
            $task_details[] = $adddata;
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    //获取来访人列表
    function Dsviewlist()
    {
        flushRequest();
        $user = db('member')->where('unread_viewer_count', '>', 0)->where('status', 1)->field('token,uid')->select();
        // var_dump($user);die;
        $redis_key = get_task_key('get_home_visit_list');
        $addtask['redis_key'] = $redis_key;
        $addtask['task_name'] = '获取来访人列表';
        $addtask['task_type'] = 'GetHomeVisitList';
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);

        $redis = connectRedis();
        $task_details = [];
        foreach ($user as $k => $v) {
            // $v['max_cursor'] = 0;
            // var_dump($v);die;
            $v['proxy'] = getHttpProxy($v['uid']);
            $v['token'] = json_decode($v['token'], true);
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetHomeVisitList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;
            $task_details[] = $adddata;
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    function Pinglunlist()
    { //一级评论
        $videolist = db('membervideo')->where('comment_count', '>', 0)->field('aweme_id')->select()->toArray();
        // var_dump($videolist == true);die;
        if ($videolist) {
            $addtask['task_name'] = '获取一级评论列表';
            $addtask['task_type'] = 'GetCommentList';
            $addtask['task_num'] = count($videolist);
            $addtask['create_time'] = time();
            $addtask['status'] = 1;
            $usertask = db('tasklisttwo')->insertGetId($addtask);
            foreach ($videolist as $k => $v) {
                $v['count'] = 50;
                $v['cursor'] = 0;
                $v['type'] = 1;
                $v['channel_id'] = 0;
                $adddata['parameter'] = $v;
                $adddata['create_time'] = time();
                $adddata['task_type'] = 'GetHomeVisitList';
                $adddata['tasklist_id'] = $usertask;
                $adddata['crux'] = $v['aweme_id'];
                db('tasklistdetailtwo')->insert($adddata);
            }
        } else {
            echo '没有视频有评论';
        }
    }

    function Pinglunlists()
    { //二级评论
        $videolist = db('comment_list')->where('reply_comment_total', '>', 0)->field('cid,aweme_id')->select()->toArray();
        if ($videolist) {
            $addtask['task_name'] = '获取二级评论列表';
            $addtask['task_type'] = 'GetCommentList';
            $addtask['task_num'] = count($videolist);
            $addtask['create_time'] = time();
            $addtask['status'] = 1;
            $usertask = db('tasklisttwo')->insertGetId($addtask);
            foreach ($videolist as $k => $v) {
                $v['count'] = 50;
                $v['cursor'] = 0;
                $v['type'] = 2;
                $v['comment_id'] = $v['cid'];
                $adddata['parameter'] = $v;
                $adddata['create_time'] = time();
                $adddata['task_type'] = 'GetHomeVisitList';
                $adddata['tasklist_id'] = $usertask;
                $adddata['crux'] = $v['aweme_id'];
                db('tasklistdetailtwo')->insert($adddata);
            }
        } else {
            echo '没有视频有评论';
        }
    }

    //循环获取设备是否存在
    function shebei()
    {
        // flushRequest();
        for ($i = 0; $i < 255; $i++) {
            echo $i;
            $url = "http://192.168.4." . $i . ":8081/exist";
            $data = json_decode(file_get_contents($url, true), true);
            //  var_dump($data);die;
            if ($data) {
                $indata['deviceip'] = "192.168.4." . $i;
                db('equipment')->insert($indata);
            }
            //  var_dump($data);die;

        }
    }

    //循环的读取设备里面的token
    function num()
    {
        // flushRequest();
        $arr = db('equipment')->limit(2)->field('deviceip')->select()->toArray();
        foreach ($arr as $k => $v) {
            $url = "http://" . $v['deviceip'] . ":8081/backupList?app=TikTok";
            // var_dump($url);die;
            $data = json_decode(file_get_contents($url, true), true);
            foreach ($data['backupArray'] as $key => $val) {
                $urls = "http://" . $v['deviceip'] . ":8081/userToken?app=TikTok&filename=" . $val['name'];
                $datas = json_decode(file_get_contents($urls, true), true);
                $addtoken = $this->AddMemberToken($datas, $val['name'], '内部设备');
                // var_dump($addtoken);die;
            }
            // var_dump();
        }
    }
    // token入库
    function AddMemberToken($data, $backups_name, $phone_number)
    {
        $data = $data;
        // $getdata = $this->request->get();

        $gjtype = explode('=', $data['common-params']);
        $gj = explode('&', $gjtype[1]);
// 		var_dump($gj[0]);die;
        // if (empty($data)) {
        //     throw new ValidateException('参数错误');
        // }
        $token_str = str_replace('&quot;', '"', $data);
        $token_str = str_replace('&amp;', '&', $token_str);
        // var_dump($token_str);die;
        $arr['token'] = json_encode($token_str);
        $arr['backups_name'] = $backups_name;
        $arr['phone_number'] = $phone_number;
        $arr['uid'] = $data['user']['uid'];
        $arr['sec_uid'] = $data['user']['sec_uid'];
        $arr['country'] = $this->transCountryCode($gj[0]);

        $memberinfo = db('member')->where('uid', $data['user']['uid'])->find();
        if ($memberinfo) {
            $res = db('member')->where('uid', $data['user']['uid'])->update($arr);
            $msg = '更新成功' . $arr['uid'];
        } else {
            $arr['addtime'] = time();
            $arr['grouping_id'] = 3;
            $arr['ifpic'] = 0;
            $arr['typecontrol_id'] = 3;
            $res = db('member')->insert($arr);
            $msg = '添加成功';
            // $this->GetMemberInfo($arr['uid']);
        }
        return $msg;
    }


}