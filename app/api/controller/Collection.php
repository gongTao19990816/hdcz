<?php

namespace app\api\controller;

use SplFileInfo;
use think\exception\ValidateException;
use think\facade\Validate;

/**
 * 添加采集任务类
 */
class Collection extends Common
{
    function aa()
    {
        $members = db("member")->where("ifpic", 0)->field("member_id,avatar_thumb")->select();
        foreach ($members as $member) {
            $splFileInfo = new SplFileInfo($member['avatar_thumb']);
            $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
            if ($avatar_hash == '6786ffc93d6a02f2b30a98ee94132937') {
                db("member")->where("member_id", $member['member_id'])->update(["no_avatar" => 1]);
            } else {
                db("member")->where("member_id", $member['member_id'])->update(["no_avatar" => 0]);
            }
        }
    }

    /**
     * 视频采集
     */
    function video()
    {  //采集用户视频  /需要参数 uid sec_uid
        // die('1111');

        $data = $this->request->post();
        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
        echo json_encode(['status' => 200, 'msg' => "操作成功"]);
        flushRequest();
        $userlist = $data['data'];
// 		var_dump(count($userlist));die;
        $redis_key = get_task_key('collection_video');
        $addtask['redis_key'] = $redis_key;
        $addtask['task_name'] = '采集用户视频';
        $addtask['task_type'] = 'CollectionVideo';
        $addtask['task_num'] = count($userlist);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        $redis = connectRedis();
        $task_details = [];
        foreach ($userlist as $k => $v) {
            $v['max_cursor'] = 0;
            // var_dump($v);die;
            $v['token'] = doToken('', 2);
            $v['proxy'] = getHttpProxy($v['token']['user']['uid']);
            $adddata['parameter'] = $v;
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'CollectionVideo';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            unset($adddata['tasklistdetail_id']);
            //$arr = db('tasklistdetail')->insertGetId($adddata);
            $arr = \app\api\model\TaskListDetail::add($adddata);
            $adddata['tasklistdetail_id'] = $arr;
            //$redis->lPush($redis_key, json_encode($adddata));
            $task_details[] = $adddata;
        }

        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
// 		$v['max_cursor'] = 0;
//         $v['token'] = doToken('', 2);
//         $v['proxy'] = getHttpProxy($v['token']['user']['uid']);
//         $adddata['parameter'] = json_encode($v);
//         $adddata['create_time'] = time();
//         $adddata['task_type'] = 'GetAwemeList';
//         $adddata['tasklist_id'] = $usertask;
//         $adddata['crux'] = $v['uid'];
//         unset($adddata['tasklistdetail_id']);
//         $arr = db('tasklistdetail')->insertGetId($adddata);
//         $adddata['tasklistdetail_id'] = $arr;
//         $redis->lPush('task', json_encode($adddata));
    }


    /**
     * 用户采集
     */
    function user()
    {
        $params = $this->request->post();

        //验证规则
        $rule = [
            'task_name' => 'require',
            'label' => 'require',
            'upper_limit' => 'require|number',
            'uid_list' => 'require'
        ];

        //错误提示
        $msg = [
            'task_name.require' => '任务名必传',
            'label.require' => '数据标签必传',
            'upper_limit.require' => '采集上限必传',
            'upper_limit.number' => '采集上限仅正整数',
            'uid_list.require' => 'UID列表必传'
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }
        if (!in_array("CollectionFans", $params['type_list']) && !in_array("CollectionFollow", $params['type_list']) && !in_array("CollectionComment", $params['type_list'])) {
            throw new ValidateException("采集内容至少传一项");
        }
        foreach ($params['uid_list'] as $item) {
            if (!isset($item['uid'])) throw new ValidateException("未传递uid");
            if (!isset($item['sec_uid'])) throw new ValidateException("未传递sec_uid");
        }
        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        $task_num = count($params['uid_list']) * count($params['type_list']) * $params['upper_limit'];
        checkTaskNum($task_num);
        /*
         * 提交任务时：
         * $splFileInfo = new SplFileInfo("http://192.168.4.30/uploads/xiazai/6997530518493742086.png");
         * no_avatar_thumb_hash = hash_file("md5", $splFileInfo->getPathname());
         * 验证是否无头像：
         * hash：6786ffc93d6a02f2b30a98ee94132937
         * file：/uploads/api/202212/638b39ace8149.png
        */
        $redis_key = get_task_key('get_aweme_list');

        $task = [
            'redis_key' => $redis_key,
            "task_name" => $params['task_name'],
            "task_type" => "CollectionUser",
            "task_num" => $task_num,
            "create_time" => time(),
            "status" => 1,
        ];
        $task_id = db("tasklisttwo")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "视频任务发布中，可使用GET传递task_id访问'/api/push/getTaskProgress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();

        $redis = connectRedis();
        $upper_limit = $params['upper_limit'];
        $task_details = [];
        foreach ($params['uid_list'] as $item) {
            $i = 0;
            foreach ($params['type_list'] as $type) {
                if ($i < $upper_limit) {
                    $token = doToken('', 2);
                    //取http代理
                    $proxy = getHttpProxy($token['user']['uid']);
                    switch ($type) {
                        case "CollectionFans":
                            $parameter = [
                                'label' => $params['label'],
                                'uid' => $item['uid'],
                                'sec_uid' => $item['sec_uid'],
                                'unique_id' => $item['unique_id'],
                                'source_type' => 1,
                                'count' => 20,
                                'offset' => 0,
                                'max_time' => 0,
                                'token' => $token,
                                'proxy' => $proxy,
                                'black_list' => $params['black_list']
                            ];
                            break;
                        case "CollectionFollow":
                            $parameter = [
                                'label' => $params['label'],
                                'uid' => $item['uid'],
                                'sec_uid' => $item['sec_uid'],
                                'unique_id' => $item['unique_id'],
                                'source_type' => 1,
                                'count' => 20,
                                'vcdAuthFirstTime' => 0,
                                'max_time' => 0,
                                'token' => $token,
                                'proxy' => $proxy,
                                'black_list' => $params['black_list']
                            ];
                            break;
                        case "CollectionComment":
                            $type = "CollectionVideo";
                            $parameter = [
                                'label' => $params['label'],
                                "uid" => $item['uid'],
                                "sec_uid" => $item['sec_uid'],
                                'source' => 0,
                                'count' => 20,
                                'unique_id' => $item['unique_id'],
                                "max_cursor" => 0,
                                "token" => $token,
                                "proxy" => $proxy
                            ];
                            break;
                        default:
                            break;
                    }
                    if (!isset($parameter)) continue;
                    $task_detail = [
                        "tasklist_id" => $task_id,
                        "parameter" => $parameter,
                        "status" => 1,
                        "create_time" => time(),
                        "task_type" => $type,
                        "crux" => $item['uid']
                    ];
                    unset($task_detail['tasklistdetail_id']);
                    //$task_detail_id = db("tasklistdetailtwo")->insertGetId($task_detail);
                    $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                    $task_detail['tasklistdetail_id'] = $task_detail_id;
                    $task_details[] = $task_detail;
                    $i++;
                }
            }
        }

        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }
}