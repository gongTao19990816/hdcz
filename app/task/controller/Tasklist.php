<?php
/*
 module:		任务表
 create_time:	2022-12-09 16:35:00
 author:		大怪兽
 contact:
*/

namespace app\task\controller;

use app\api\job\FollowTask;
use app\api\model\Externalmember;
use app\api\model\Tasklist as TasklistModel;
use app\api\model\TaskListDetail;
use app\api\service\TasklistService;
use app\task\job\CompleteTask;
use SplFileInfo;
use think\exception\ValidateException;
use think\facade\Log;

class Tasklist extends Common
{


    /**
     * @api {post} /Tasklist/index 01、首页数据列表
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [task_name] 任务名称
     * @apiParam (输入参数：) {string}        [task_type] 任务类型
     * @apiParam (输入参数：) {int}            [status] 状态 未完成|1|success,已完成|0|danger
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据
     * @apiParam (成功返回参数：) {string}        array.data.list 返回数据列表
     * @apiParam (成功返回参数：) {string}        array.data.count 返回数据总数
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function index()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $limit = $this->request->post('limit', 20, 'intval');
        $page = $this->request->post('page', 1, 'intval');

        $where = [];
        $where['task_name'] = $this->request->post('task_name', '', 'serach_in');
        $where['task_type'] = $this->request->post('task_type', '', 'serach_in');
        $where['status'] = $this->request->post('status', '', 'serach_in');

        $field = '*';
        $orderby = 'tasklist_id desc';

        $res = TasklistService::indexList(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Tasklist/add 02、添加
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            task_name 任务名称
     * @apiParam (输入参数：) {string}            task_type 任务类型
     * @apiParam (输入参数：) {string}            task_num 任务数量
     * @apiParam (输入参数：) {int}                status 状态 未完成|1|success,已完成|0|danger
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function add()
    {
        $postField = 'task_name,task_type,task_num';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $data['create_time'] = time();
        $res = TasklistService::add($data);

        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    //弃用
    function get_task_old()
    {
        $redis = connectRedis();
        $task = db('tasklist')->where('status', 1)->field("task_type,tasklist_id")->find();
        $taskdetail = $redis->lPop($task['task_type'] . "_" . $task['tasklist_id']);
        if (!$taskdetail) {
            throw new ValidateException('没有任务22');
        }
        $taskdetail = json_decode($taskdetail, true);
        /*switch ($taskdetail['task_type']) {
            case "PushVideo":
            case "GetHomeVisitList":
                $parameter = json_decode($taskdetail['parameter'], true);
                $parameter['token'] = db('member')->where('uid', $parameter['uid'])->value('token');
                $parameter['proxy'] = getHttpProxy($parameter['uid']);
                $taskdetail['parameter'] = json_encode($parameter);
                break;
            case "GetSelfUserInfo":
                $parameter = json_decode($taskdetail['parameter'], true);
                $parameter['proxy'] = getHttpProxy($parameter['uid']);
                $taskdetail['parameter'] = json_encode($parameter);
                break;
            case "GetFansList":
            case "GetFollowList":
            case "GetAwemeList":
            case "GetCommentList":
                $parameter = json_decode($taskdetail['parameter'], true);
                $jstoken = doToken('', 2);
                $parameter['token'] = $jstoken;
                $parameter['proxy'] = getHttpProxy($jstoken['user']['uid']);
                $taskdetail['parameter'] = json_encode($parameter);
                break;
        }*/
        // 先返回数据
        echo json_encode(['status' => $this->successCode, 'msg' => '领取成功', 'data' => $taskdetail]);
        flushRequest();

        // 再处理内部逻辑
        \db("tasklistdetail")->where("tasklistdetail_id", $taskdetail['tasklistdetail_id'])->update(['status' => 3, 'receive_time' => time()]);
        $updata['status'] = 3;
        $updata['s_time'] = time();
        $updata['tasklistdetail_id'] = $taskdetail['tasklistdetail_id'];
        db('tasklistdetaillog')->insert($updata);
        // db('tasklist')->where('tasklist_id', $taskdetail['tasklist_id'])->dec('task_num')->update();
        //return $this->ajaxReturn($this->successCode, "领取成功", $taskdetail);
    }

    //弃用
    function has_task_old()
    {
        $redis = connectRedis();
        $task = db('tasklist')->where('status', 1)->field("task_type,tasklist_id")->find();
//        var_dump($redis->exists($task['task_type'] . "_" . $task['tasklist_id']));die();
        if ($task) {
            //["task_num" => $task['task_num'] - ($task['complete_num'] + $task['fail_num'])]
            echo json_encode(["code" => 200, "msg" => "有任务", "data" => ["task_num" => \db("tasklistdetail")->where(["tasklist_id" => $task['tasklist_id'], "status" => 1])->count()]]);
            flushRequest();

            //响应返回之后，将参数传递到redis
            $redis = connectRedis();
            if (!$redis->exists($task['task_type'] . "_" . $task['tasklist_id'])) {
                $taskdetail = \db("tasklistdetail")->where(["status" => 1])->select();
                foreach ($taskdetail as $item) {
                    $redis->lPush(config("my.task_key"), json_encode($item));
                }
            }
        } else {
            throw new ValidateException('没有任务');
        }
    }

    function has_task()
    {
        echo json_encode(["code" => 200, "msg" => "有任务", "data" => ["task_num" => connectRedis()->lLen(config("my.task_key"))]]);
        die();
    }

    function get_task()
    {
        $redis = connectRedis();
        $keys = $redis->keys(config('my.task_key_prefix') . '*');

        if ($keys && count($keys)) {
            $key = $keys[0];
            $data = $redis->rpop($key);
            db('tasklistdetail')->where('tasklistdetail_id', $data['tasklistdetail_id'])->update(["status" => 3, "receive_time" => time()]);
            db('task_uid')->where('task_uid_id', $data['task_uid_id'])->update(['update_time' => time()]);
            return json_encode(['status' => $this->successCode, 'msg' => '领取成功', 'data' => json_decode($data, true)]);
        } else {
            return json_encode(['status' => $this->errorCode, 'msg' => '没有任务了']);
        }
        /*if (connectRedis()->lLen(config("my.task_key"))) {
            $taskdetail = json_decode(connectRedis()->rpop(config("my.task_key")), true);
            echo json_encode(['status' => $this->successCode, 'msg' => '领取成功', 'data' => $taskdetail]);
            //queue(ReceiveTask::class, ["tasklistdetail_id" => $taskdetail['tasklistdetail_id']]);
        } else {
            echo json_encode(['status' => $this->errorCode, 'msg' => '没有任务了']);
        }
        die();*/
    }


    function iftask()
    {
        $user = db('tasklist')->where('status', 1)->find();
        if ($user) {
            $res = db('tasklistdetail')->where(['tasklist_id' => $user['tasklist_id'], 'status' => 1])->limit(1)->field('parameter,task_type,tasklistdetail_id,tasklist_id')->find();  //
            if ($res) {
                $res['task_name'] = db('tasklist')->where('tasklist_id', $res['tasklist_id'])->value('task_name');
                if ($res['task_type'] == 'PushVideo') {
                    $parameter = json_decode($res['parameter'], true);
                    $parameter['token'] = db('member')->where('uid', $parameter['uid'])->value('token');
                    $parameter['proxy'] = getHttpProxy($parameter['uid']);
                    $res['parameter'] = json_encode($parameter);
                }
                db('tasklistdetail')->where('tasklistdetail_id', $res['tasklistdetail_id'])->update(['status' => 3, 'receive_time' => time()]);
                $updata['status'] = 3;
                $updata['s_time'] = time();
                $updata['tasklistdetail_id'] = $res['tasklistdetail_id'];
                $ress = db('tasklistdetaillog')->insert($updata);
                return $this->ajaxReturn($this->successCode, '领取成功', $res);
            } else {
                db('tasklist')->where('tasklist_id', $user['tasklist_id'])->update(['status' => 0]);
                throw new ValidateException('没有任务22');
            }
        } else {
            throw new ValidateException('没有任务');
        }
    }


    function submit_task()
    {
        $tasklistdetail_id = $this->request->get('tasklistdetail_id');
        $status = $this->request->get('status');
        if (empty($tasklistdetail_id)) {
            throw new ValidateException('参数错误');
        }
        $params = $this->request->post();
        if ($this->request->contentType() != 'application/json') {
            throw new ValidateException('请使用application/json传递body');
        }

        $body = file_get_contents("php://input");

        $data = $params;
        /*if (empty($data)) {
            throw new ValidateException('返回结果不能为空');
        }*/
        //$tasklistdetail = db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail_id)->find();
        $tasklistdetail = TaskListDetail::where('tasklistdetail_id', $tasklistdetail_id)->find();
        $uid_up_data = ['update_time' => time(), 'status' => 1];
        if(in_array($tasklistdetail['task_type'], ["CollectionFans", "CollectionFollow", "Follow", "UpdateUserData"])){
            //$uid_up_data[] = '';
        }
        db('task_uid')->where('task_uid_id', $tasklistdetail['task_uid_id'])->update($uid_up_data);

        if ($tasklistdetail['task_type'] == "PushVideo") {
            $redis = connectRedis();
            $data1 = db('tasklistdetail')->where(['task_type' => 'PushVideo', 'status' => 5])->find();
            if ($data1) {
                //db('tasklistdetail')->where('tasklistdetail_id', $data1['tasklistdetail_id'])->update(['status' => 1]);
                $tasklistdetail->save(['status' => 1]);

                // $task_detail['tasklistdetail_id'] = $data['tasklistdetail_id'];
                $data1['parameter'] = json_decode($data1['parameter'], true);
                $redis->lPush(config("my.task_key"), json_encode($data1));
            }
        }
        if ($status == 1) {
            $is_success = false;
            if (!empty($data) && isset($data['status_code'])) {
                if ($data['status_code'] == 0) {  //成功
                    //db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail['tasklistdetail_id'])->update(['status' => 0, 'complete_time' => time()]);
                    $tasklistdetail->save(['status' => 0, 'complete_time' => time()]);
                    db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('complete_num')->update();
                    /*$updata['result'] = json_encode($data);
                    $updata['s_time'] = time();
                    $updata['tasklistdetail_id'] = $tasklistdetail['tasklistdetail_id'];
                    $updata['status'] = 0;
                    $res = db('tasklistdetaillog')->insert($updata);*/
                    $is_success = true;
                } else {
                    $parameter = json_decode($tasklistdetail['parameter'], true);
                    if ($parameter && isset($parameter['uid'])) {
                        $uid = $parameter['uid'];
                    } else {
                        $uid = $tasklistdetail['crux'];
                    }
                    $reason = json_encode($data);
                    switch ($data['status_code']) {
                        case 4://服务器不可用，重试
                            $jstoken = doToken('', 2);
                            $proxy = getHttpProxy($jstoken['user']['uid']);
                            $parameter['token'] = $jstoken;
                            $parameter['proxy'] = $proxy;
                            $this->add_new_task($tasklistdetail, $parameter);
                            break;
                        case 2149://关注频率太快，重试
                            $reason = "关注频率太快";
                            $this->add_new_task($tasklistdetail, $parameter);
                            break;
                        case 2096://私密账号
                            $reason = "私密账号";
                            db("member")->where("uid", $uid)->update(["status" => 2096]);
                            break;
                        case 8://账号登出
                            $reason = "账号登出";
                            db("member")->where("uid", $uid)->update(["status" => 2]);
                            break;
                        case 9://账号封禁
                            $reason = "账号封禁";
                            db("member")->where("uid", $uid)->update(["status" => 0]);
                            break;
                        case 3002290://历史记录不可查看
                            $reason = "历史记录不可查看";
                            db("member")->where("uid", $uid)->update(["status" => 3002290]);
                            break;
                        case 3002060://关注列表不可查看
                            $reason = "关注列表不可查看";
                            break;
                        case 24:
                            $reason = "对方账号被封禁";
                            break;
                        case 3002284:
                            $reason = "修改次数太多";
                            break;
                        case 2065:
                            $reason = "用户不存在";
                            break;
                    }

                    //db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail['tasklistdetail_id'])->update(['status' => 2, 'complete_time' => time(), 'reason' => $reason]);
                    $tasklistdetail->save(['status' => 2, 'complete_time' => time(), 'reason' => $reason]);
                    db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('fail_num')->update();
                    $updata['result'] = json_encode($data);
                    $updata['s_time'] = time();
                    $updata['tasklistdetail_id'] = $tasklistdetail['tasklistdetail_id'];
                    $updata['status'] = 2;
                    $res = db('tasklistdetaillog')->insert($updata);
                }
            } else {
                //db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail_id)->update(['status' => 2, 'complete_time' => time(), 'reason' => json_encode($data)]);
                $tasklistdetail->save(['status' => 2, 'complete_time' => time(), 'reason' => json_encode(empty($data) ? $body : $data)]);
                db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('fail_num')->update();
                $updata['result'] = json_encode($data);
                $updata['s_time'] = time();
                $updata['tasklistdetail_id'] = $tasklistdetail_id;
                $updata['status'] = 2;
                $res = db('tasklistdetaillog')->insert($updata);
            }
            if ($is_success) {
                $task_type = trim($tasklistdetail['task_type']);
                switch ($task_type) {
                    case "BatchUpdateUserData":
                    case "UpdateUserData":
                        //用户数据对应着member
                        $this->ExecUpdateUserData($data, $tasklistdetail);
                        break;
                    case "PushVideo":
                        //上传视频
                        $this->ExecPushVideo($data, $tasklistdetail);
                        break;
                    case "GetSelfUserInfo":
                        // 获取自己的个人信息
                        $this->ExecGetSelfUserInfo($data, $tasklistdetail);
                        break;
                    case "GetFansList":
                        // 获取粉丝列表
                        $this->ExecGetFansList($data, $tasklistdetail);//√
                        break;
                    case "GetAwemeList":
                        // 获取视频列表
                        $this->ExecGetAwemeList($data, $tasklistdetail);//√
                        break;
                    case "GetHomeVisitList":
                        //获取来访人列表
                        $this->ExecGetHomeVisitList($data, $tasklistdetail);
                        break;
                    case "GetCommentList":
                        // 获取评论列表
                        $this->ExecGetCommentList($data, $tasklistdetail);
                        break;
                    case "GetFollowList":
                        // 获取关注列表
                        $this->ExecGetFollowList($data, $tasklistdetail);
                        break;
                    case "CollectionFans":
                        //采集粉丝列表
                        $this->ExecCollectionFans($data, $tasklistdetail);
                        break;
                    case "CollectionFollow":
                        //采集关注列表
                        $this->ExecCollectionFollow($data, $tasklistdetail);
                        break;
                    case "CollectionVideo":
                        //采集博主的视频
                        $this->ExecCollectionVideo($data, $tasklistdetail);
                        break;
                    case "CollectionComment":
                        //采集视频的评论
                        $this->ExecCollectionComment($data, $tasklistdetail);
                        break;
                    case "CommentDigg":
                        //评论点赞
                        $this->ExecCommentDigg($tasklistdetail);
                        break;
                    case "ChatProfile"://私信名片
                    case "ChatAweme"://私信作品
                    case "ChatLink"://私信链接
                    case "ChatText"://私信文本
                        $this->ExecChat($tasklistdetail);
                        break;
                    case "Follow":
                        $this->ExecFollowUser($data, $tasklistdetail);
                        //关注用户
                        break;
                }
            }
        } else {//访问失败可能是代理错误
            //db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail_id)->update(['status' => 2, 'complete_time' => time(), 'reason' => json_encode($data)]);
            $tasklistdetail->save(['status' => 2, 'complete_time' => time(), 'reason' => json_encode(empty($data) ? $body : $data)]);
            db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('fail_num')->update();
            $updata['result'] = json_encode($data);
            $updata['s_time'] = time();
            $updata['tasklistdetail_id'] = $tasklistdetail_id;
            $updata['status'] = 2;
            $res = db('tasklistdetaillog')->insert($updata);


            if (empty($data)) {
                // 如果设置了连续失败次数，小于连续失败次数时重发任务
                $fail_num = db("tasklistdetaillog")->where(["tasklistdetail_id" => $tasklistdetail['tasklistdetail_id'], "status" => 2])->count();
                $can_fail_num = db("tasklist")->where("tasklist_id", $tasklistdetail['tasklist_id'])->cache()->value("can_fail_num");
                if (($fail_num + 1) < $can_fail_num) {
                    $jstoken = doToken('', 2);
                    $proxy = getHttpProxy($jstoken['user']['uid']);
                    $parameter = json_decode($tasklistdetail['parameter'], true);
                    $parameter['token'] = $jstoken;
                    $parameter['proxy'] = $proxy;
                    $this->add_new_task($tasklistdetail, $parameter);
                }
                if (in_array($tasklistdetail['task_type'], ["CollectionFans", "CollectionFollow", "Follow", "UpdateUserData"])) {
                    // Windows下运行任务程序报错就重试
                    if (strpos(urldecode($params), "error=[WinError") || strpos($params, "error=%5BWinError")) {
                        $parameter = json_decode($tasklistdetail['parameter'], true);
                        $this->add_new_task($tasklistdetail, $parameter);
                    }

                    // 采集粉丝、采集关注、关注任务失败重发，最多重试三次
                    $fail_num1 = db("tasklistdetail")->where(["tasklist_id" => $tasklistdetail['tasklist_id'], "crux" => $tasklistdetail['crux'], "status" => 2])->count();
                    if ($fail_num1 < 3) {
                        $parameter = json_decode($tasklistdetail['parameter'], true);
                        $this->add_new_task($tasklistdetail, $parameter);
                    }
                }
            }
        }
        return $this->ajaxReturn($this->successCode, '提交成功');
    }

    function ExecFollowUser($data, $tasklistdetail)
    {
        if ($data['follow_status'] == 4) {
            $tasklistdetail_id = $tasklistdetail['tasklistdetail_id'];
            //db('tasklistdetail')->where('tasklistdetail_id', $tasklistdetail_id)->update(['status' => 0, 'complete_time' => time(), 'reason' => "私密账号"]);
            $tasklistdetail->save(['status' => 0, 'complete_time' => time(), 'reason' => "私密账号"]);
            db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('fail_num')->update();
            $updata['result'] = json_encode($data);
            $updata['s_time'] = time();
            $updata['tasklistdetail_id'] = $tasklistdetail_id;
            $updata['status'] = 2;
            $res = db('tasklistdetaillog')->insert($updata);
        }

        $parameter = json_decode($tasklistdetail['parameter'], true);


        $datas['uid'] = $parameter['member_uid'];
        $datas['b_uid'] = $parameter['uid'];
        $datas['b_sec_user_id'] = $parameter['sec_uid'];
        $datas['create_time'] = time();
        $datas['type'] = 1;
        $res = db('followuser')->insert($datas);


        db("external_member")->where("uid", $parameter['uid'])->update(["is_follow" => 0]);

        $exec_num = db("tasklistdetail")->where("crux", $tasklistdetail['crux'])
            ->where("tasklist_id", $tasklistdetail['tasklist_id'])->where("status", 0)->count();// 已执行次数
        $last_exec_time = db("tasklistdetail")->where("crux", $tasklistdetail['crux'])->where("tasklist_id", $tasklistdetail['tasklist_id'])->where("status", 0)
            ->order("complete_time desc")->value("complete_time");//上次执行时间
        $user_follow_upper_limit = $parameter['user_follow_upper_limit'];

        //$external_member = db("external_member")->where(["is_follow" => 1, 'secret' => 0])->field("external_member_id,uid,sec_uid")->find();
        //db("external_member")->where(["external_member_id" => $external_member["external_member_id"]])->update(["is_follow" => 0]);
        $external_member = Externalmember::where(["is_follow" => 1, 'secret' => 0])->field("external_member_id,uid,sec_uid")->find();
        $external_member->save(["is_follow" => 0]);
        //$follow_user_uids = db("followuser")->where("uid", $parameter['member_uid'])->column("b_uid");
        //$external_member = db("external_member")->where("uid", "not in", $follow_user_uids)->orderRaw("rand()")->find();

        if ($exec_num >= $user_follow_upper_limit) {
            db("member")->where("uid", $tasklistdetail['crux'])->update(["ifup" => 1]);
        } else if ($external_member) {
//            $external_member = $external_members[0];
            $parameter["uid"] = $external_member['uid'];
            $parameter["sec_uid"] = $external_member['sec_uid'];
            $rate_min = $parameter['rate_min'];
            $rate_max = $parameter['rate_max'];
            $delay = rand($rate_min, $rate_max); //关注频率，延迟多少秒执行
            $task_detail = [
                "tasklist_id" => $tasklistdetail['tasklist_id'],
                "parameter" => json_encode($parameter),
                "status" => 1,
                "create_time" => time(),
                "task_type" => $tasklistdetail['task_type'],
                "crux" => $tasklistdetail["crux"]
            ];
            unset($task_detail['tasklistdetail_id']);
            $task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
            $task_detail['tasklistdetail_id'] = $task_detail_id;
            $task_detail['parameter'] = $parameter;
            $myDelay = ($last_exec_time + $delay) - time();
            if ($myDelay > (time() + 60) || $myDelay < 0) {
                $myDelay = 60;
            }
            if ($delay > (time() + 60) || $delay < 0) {
                $delay = 60;
            }
            queue(FollowTask::class, $task_detail, $myDelay);
        }
        /*if (($last_exec_time + $delay) > time() && $exec_num < $user_follow_upper_limit) {
            $member = db("member")->where("uid", $tasklistdetail['crux'])->find();
            $external_members = db("external_member")->where("is_follow", 1)->orderRaw("rand()")->select();
            $external_member = $external_members[0];
            $token = doToken($member['token']);
            //取http代理
            $proxy = getHttpProxy($token['user']['uid']);

            $parameter = [
                'member_uid' => $member['uid'],
                'uid' => $external_member['uid'],
                'sec_uid' => $external_member['sec_uid'],
                'from' => 19,
                'from_pre' => 13,
                'channel_id' => 3,
                'type' => 1, // # 1 表示关注，0 表示取消关注
                "token" => $token,
                "proxy" => $proxy,
                "rate_min" => $parameter['rate_min'],
                "rate_max" => $parameter['rate_max'],
                "user_follow_upper_limit" => $user_follow_upper_limit
            ];
            $this->add_new_task($tasklistdetail, $parameter);
        }*/
    }

    function ExecChat($tasklistdetail)
    {
        db("external_member")->where("uid", $tasklistdetail['curx'])->update(["if_chat" => 0]);
    }

    function ExecCommentDigg($tasklistdetail)
    {
        $parameter = json_decode($tasklistdetail['parameter'], true);
        $datas['uid'] = $parameter['uid'];
        $datas['aweme_id'] = $parameter['aweme_id'];
        $datas['cid'] = $parameter['cid'];
        $datas['create_time'] = time();
        db('videocommentdigg')->insert($datas);
    }

    /**
     * 黑名单检查
     */
    function checkInBlackList($user, $parameter)
    {
        return false;
        $parameter = is_string($parameter) ? json_decode($parameter, true) : $parameter;
        $black_list = $parameter['black_list'];
        if ($black_list) {
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                return false;
            } else {
                foreach ($black_list as $black) {
                    switch ($black) {
                        case "no_avatar":
                            $avatar = $user['avatar_medium']['url_list'][0];
                            $splFileInfo = new SplFileInfo($avatar);
                            $avatar_hash = hash_file("md5", $splFileInfo->getPathname());
                            return $avatar_hash == '6786ffc93d6a02f2b30a98ee94132937';
                        case "no_aweme":
                            return !$user['aweme_count'];
                        case "historical_users":
                            return (bool)db("external_member")->where("uid", $user['uid'])->count();
                        case "no_nickname":
                            return (bool)strpos($user['nickname'], "user");
                    }
                }
            }
        }
        return false;
    }

    function ExecCollectionComment($data, $tasklistdetail)
    {
        $total = $data['total'];
        if ($total <= 0) throw new ValidateException('没有评论数据');

        $has_more = $data['has_more']; //是否有更多
        $cursor = $data['cursor'];
        $comments = $data['comments']; //评论列表
        if (empty($comments)) throw new ValidateException('没有评论数据');
        $parameter = json_decode($tasklistdetail['parameter'], true);
        if ($has_more) {
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            if (isset($parameter['comment_id']) && isset($parameter['item_id'])) {
                //如果存在comment_id（一级评论id）与item_id（作品id）这两个参数，那么就是从一级评论获取二级评论
                $new_parameter = [
                    "uid" => $parameter['uid'],
                    "sec_user_id" => $parameter['sec_user_id'],
                    'unique_id' => $parameter['unique_id'],
                    'count' => 20,
                    'type' => 2,
                    'item_id' => $parameter['item_id'],
                    'cursor' => 0,
                    'comment_id' => $parameter['comment_id'],
                    "token" => $jstoken,
                    "proxy" => $proxy,
                ];
            } else {
                $new_parameter = [
                    "uid" => $parameter['uid'],
                    "sec_user_id" => $parameter['sec_user_id'],
                    'unique_id' => $parameter['unique_id'],
                    'count' => 20,
                    'type' => 1,
                    'aweme_id' => $parameter['aweme_id'],
                    'cursor' => $cursor,
                    'channel_id' => 0,
                    "token" => $jstoken,
                    "proxy" => $proxy,
                ];
            }
            $this->add_new_task($tasklistdetail, $new_parameter);
        }
        //$task = db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->field('tasklist_id,task_name')->cache()->find();
        $insert_u = [];
        $insert_list = [];
        foreach ($comments as $k => $v) {
            $cid = $v['cid'];
            $comment_language = $v['comment_language'];
            $text = $v['text'];
            $create_time = $v['create_time'];
            $digg_count = $v['digg_count'];
            $aweme_id = $v['aweme_id'];
            $reply_id = $v['reply_id'];
            $reply_comment_total = $v['reply_comment_total'];
            $user_info = $v['user'];
            if ($this->checkInBlackList($user_info, $parameter)) continue;
            $uid = $user_info['uid'];
            $sec_uid = $user_info['sec_uid'];
            $nickname = $user_info['nickname'];
            $signature = $user_info['signature'];
            $account_region = $user_info['account_region'] ?: $user_info['region'];
            $unique_id = $user_info['unique_id'];
            $aweme_count = $user_info['aweme_count'];
            $avatar_medium = $user_info['avatar_medium']['url_list'][0];
            $following_count = $user_info['following_count'];
            $follower_count = $user_info['follower_count'];
            $total_favorited = $user_info['total_favorited'];

            $insert_list = [
                'tasklist_id' => $tasklistdetail['tasklist_id'],
                'tasklistdetail_id' => $tasklistdetail['tasklistdetail_id'],
                'cid' => $cid,
                'comment_language' => $comment_language,
                'text' => $text,
                'create_time' => $create_time,
                'digg_count' => $digg_count,
                'aweme_id' => $aweme_id,
                'reply_id' => $reply_id,
                'reply_comment_total' => $reply_comment_total,
                'uid' => $uid,
                'sec_uid' => $sec_uid,
                'nickname' => $nickname,
                'signature' => $signature,
                'account_region' => $account_region,
                'unique_id' => $unique_id,
                'aweme_count' => $aweme_count,
                'avatar_medium' => $avatar_medium,
                'following_count' => $following_count,
                'follower_count' => $follower_count,
                'total_favorited' => $total_favorited,
                'ifpic' => 1
            ];
            if (db("comment_list")->where("cid", $cid)->count()) {
                db('comment_list')->where("cid", $cid)->update($insert_list);
            } else {
                db('comment_list')->insert($insert_list);
            }
            /*if (!$this->checkInBlackList($user_info, $parameter)) {
                $adddata['tasklist_id'] = $task['tasklist_id'];
                $adddata['task_name'] = $task['task_name'];
                $adddata['nickname'] = $v['nickname'];
                $adddata['blog_uid'] = $parameter['uid'];
                $adddata['blog_sec_uid'] = $parameter['sec_uid'];
                $adddata['blog_unique_id'] = $parameter['unique_id'];
                $adddata['create_time'] = $v['create_time'];
                $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
                $adddata['sec_uid'] = $v['sec_uid'];
                $adddata['following_count'] = $v['following_count'];
                $adddata['follower_count'] = $v['follower_count'];
                $adddata['favoriting_count'] = $v['favoriting_count'];
                $adddata['unique_id'] = $v['unique_id'];
                $adddata['uid'] = $v['uid'];
                $adddata['aweme_count'] = $v['aweme_count'];
                $adddata['region'] = transCountryCode($v['region']);
                $adddata['ifpic'] = 1;
                $where['blog_uid'] = $parameter['uid'];
                $where['uid'] = $v['uid'];
                $followinglistinfo = db('external_member')->where($where)->find();
                if ($followinglistinfo) {
                    $res = db('external_member')->where($where)->update($adddata);
                } else {
                    $res = db('external_member')->insert($adddata);
                }
            }*/
            if ($v['reply_comment_total']) {
                $new_parameter = [
                    "uid" => $parameter['uid'],
                    "sec_user_id" => $parameter['sec_user_id'],
                    'unique_id' => $parameter['unique_id'],
                    'count' => 20,
                    'type' => 2,
                    'item_id' => $aweme_id,
                    'cursor' => 0,
                    'comment_id' => $cid,
                    "token" => $parameter['token'],
                    "proxy" => $parameter['proxy'],
                ];
                unset($tasklistdetail['task_type']);
                $tasklistdetail['task_type'] = "CollectionComment";
                $this->add_new_task($tasklistdetail, $new_parameter);
            }
        }
    }

    function ExecCollectionVideo($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $max_cursor = $data['max_cursor'];
        $aweme_list = $data['aweme_list']; //视频列表
        $parameter = json_decode($tasklistdetail['parameter'], true);
        if ($has_more) {
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            $new_parameter = [
                "uid" => $parameter['uid'],
                "sec_uid" => $parameter['sec_uid'],
                'source' => 0,
                'count' => 20,
                'unique_id' => $parameter['unique_id'],
                "max_cursor" => $max_cursor,
                "token" => $jstoken,
                "proxy" => $proxy,
                'label' => $parameter['label'],
            ];
            $this->add_new_task($tasklistdetail, $new_parameter);
        }

        if (empty($aweme_list)) throw new ValidateException('没有视频');
        foreach ($aweme_list as $k => $v) {
            $insert['uid'] = $parameter['uid'];
            $insert['sec_uid'] = $parameter['sec_uid'];
            $insert['unique_id'] = $parameter['unique_id'];
            $insert['aweme_id'] = $v['aweme_id'];
            $insert['comment_count'] = $v['statistics']['comment_count']; //评论数量
            $insert['digg_count'] = $v['statistics']['digg_count'];
            $insert['share_count'] = $v['statistics']['share_count']; //分享数量
            $insert['play_count'] = $v['statistics']['play_count']; //播放数量
            $insert['video_desc'] = $v['desc'];
            $video = $v['video']; //视频数组
            $insert['video_url'] = $v['video']['play_addr']['url_list'][0]; //视频地址
            $insert['video_pic_url'] = $video['animated_cover']['url_list'][0];
            $insert['country'] = transCountryCode($v['author']['region']);
            $insert['addtime'] = time();
            $insert['ifvideo'] = 1;
            $where['uid'] = $parameter['uid'];
            $where['aweme_id'] = $v['aweme_id'];
            $videoinfo = db('video_capture')->where($where)->value('aweme_id');
            if ($videoinfo) {
                $res = db('video_capture')->where($where)->update($insert);
                // return $this->ajaxReturn($this->successCode,'更新成功');
                $msg = '更新成功' . $v['aweme_id'];
            } else {
                $res = db('video_capture')->insert($insert);
                $msg = '添加成功' . $v['aweme_id'];
            }
            if ($v['statistics']['comment_count']) {
                $new_parameter = [
                    "uid" => $parameter['uid'],
                    "sec_user_id" => $parameter['sec_uid'],
                    'unique_id' => $parameter['unique_id'],
                    'count' => 20,
                    'aweme_id' => $v['aweme_id'],
                    'cursor' => 0,
                    'channel_id' => 0,
                    "token" => $parameter['token'],
                    "proxy" => $parameter['proxy'],
                ];
                unset($tasklistdetail['task_type']);
                $tasklistdetail['task_type'] = "CollectionComment";
                $this->add_new_task($tasklistdetail, $new_parameter);
            }
        }
        db('user_cursor')->where(['user_id' => $parameter['uid']])->save(['max_cursor' => $max_cursor]);
    }

    /**
     * 关注列表采集结果
     */
    function ExecCollectionFollow($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $min_time = $data['min_time'];
        $followings = $data['followings']; //关注列表
        $parameter = json_decode($tasklistdetail['parameter'], true);
        $task_num = db("tasklist")->where("tasklist_id", $tasklistdetail['tasklist_id'])->where("exec_limit > (complete_num + fail_num)")->find();
        if ($has_more && $task_num) {
            $new_parameter = [
                "uid" => $parameter['uid'],
                "sec_uid" => $parameter['sec_uid'],
                'unique_id' => $parameter['unique_id'],
                "max_time" => $min_time,
                "token" => $parameter['token'],
                "proxy" => $parameter['proxy'],
                'label' => $parameter['label'],
                'source_type' => 1,
                'count' => 20,
                'black_list' => $parameter['black_list'],
                'vcdAuthFirstTime' => 0,
            ];
            $this->add_new_task($tasklistdetail, $new_parameter);
        }
        if (empty($followings)) throw new ValidateException('没有数据');
        foreach ($followings as $k => $v) {
            if ($this->checkInBlackList($v, $parameter)) continue;
            $adddata['tasklist_id'] = $tasklistdetail['tasklist_id'];
            $adddata['nickname'] = $v['nickname'];
            $adddata['blog_uid'] = $parameter['uid'];
            $adddata['blog_sec_uid'] = $parameter['sec_uid'];
            $adddata['blog_unique_id'] = $parameter['unique_id'];
            $adddata['create_time'] = $v['create_time'];
            $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
            $adddata['sec_uid'] = $v['sec_uid'];
            $adddata['following_count'] = $v['following_count'];
            $adddata['follower_count'] = $v['follower_count'];
            $adddata['favoriting_count'] = $v['favoriting_count'];
            $adddata['unique_id'] = $v['unique_id'];
            $adddata['uid'] = $v['uid'];
            $adddata['aweme_count'] = $v['aweme_count'];
            $adddata['country'] = transCountryCode($v['account_region'] ?: $v['region']);
            $adddata['signature'] = $v['signature'];
            $adddata['ifpic'] = 1;
            $adddata['create_time'] = $v['create_time'];
            $adddata['secret'] = $v['secret'];
            $adddata['sources'] = $tasklistdetail['task_type'];
            $adddata['label'] = $parameter['label'];
            $where['blog_uid'] = $parameter['uid'];
            $where['uid'] = $v['uid'];
            $followinglistinfo = db('external_member')->where($where)->find();
            if ($followinglistinfo) {
                $res = db('external_member')->where($where)->update($adddata);
            } else {
                $adddata['add_time'] = time();
                $res = db('external_member')->insert($adddata);
            }
        }
    }

    /**
     * 粉丝列表采集结果
     */
    function ExecCollectionFans($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $min_time = $data['min_time'];
        $parameter = json_decode($tasklistdetail['parameter'], true);
        $task_num = db("tasklist")->where("tasklist_id", $tasklistdetail['tasklist_id'])->where("exec_limit > (complete_num + fail_num)")->find();
        Log::write("task_num." . json_encode($task_num));
        if ($has_more && $task_num) {
            $new_parameter = [
                "uid" => $parameter['uid'],
                "sec_uid" => $parameter['sec_uid'],
                'unique_id' => $parameter['unique_id'],
                "max_time" => $min_time,
                "token" => $parameter['token'],
                "proxy" => $parameter['proxy'],
                'source_type' => 1,
                'count' => 20,
                'vcdAuthFirstTime' => 0,
                'label' => $parameter['label'],
                'black_list' => $parameter['black_list']
            ];
            $this->add_new_task($tasklistdetail, $new_parameter);
        }
        $followings = $data['followers']; //粉丝列表
        if (empty($followings)) throw new ValidateException('没有数据');
        $connect = config('database.default');
        $db = \think\facade\Db::connect($connect, false);
        foreach ($followings as $k => $v) {
            if ($this->checkInBlackList($v, $parameter)) continue;
            $adddata['tasklist_id'] = $tasklistdetail['tasklist_id'];
            $adddata['nickname'] = $v['nickname'];
            $adddata['blog_uid'] = $parameter['uid'];
            $adddata['blog_sec_uid'] = $parameter['sec_uid'];
            $adddata['blog_unique_id'] = $parameter['unique_id'];
            $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
            $adddata['sec_uid'] = $v['sec_uid'];
            $adddata['following_count'] = $v['following_count'];
            $adddata['follower_count'] = $v['follower_count'];
            $adddata['favoriting_count'] = $v['favoriting_count'];
            $adddata['unique_id'] = $v['unique_id'];
            $adddata['uid'] = $v['uid'];
            $adddata['aweme_count'] = $v['aweme_count'];
            $adddata['country'] = transCountryCode($v['account_region'] ?: $v['region']);
            $adddata['signature'] = $v['signature'];
            $adddata['ifpic'] = 1;
            $adddata['create_time'] = $v['create_time'];
            $adddata['secret'] = $v['secret'];
            $adddata['sources'] = $tasklistdetail['task_type'];
            @$adddata['label'] = $parameter['label'];
            $where['blog_uid'] = $parameter['uid'];
            $where['uid'] = $v['uid'];
            $sql = "insert into tt_external_member";
            $fields = $values = $update = "";
            foreach ($adddata as $field => $value) {
                $fields .= "`$field`,";
                $values .= "'$value',";
                $update .= "`$field`='$value',";
            }
            $fields = rtrim($fields, ",");
            $values = rtrim($values, ",");
            $update = rtrim($update, ",");
            $sql .= " ( " . $fields . ") values (" . $values . ") ON DUPLICATE KEY UPDATE " . $update;
            $db->execute($sql);


            /*
            "INSERT INTO userinfo (`uid`, token, udid, create_time, `update_time`) VALUES ('{uid}', '{token}', '{udid}', CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP()) ON DUPLICATE KEY UPDATE `token`='{token}', `update_time`=CURRENT_TIMESTAMP()"
            $followinglistinfo = db('external_member')->where($where)->count();
            if ($followinglistinfo > 0) {
                $res = db('external_member')->where($where)->update($adddata);
            } else {
                $res = db('external_member')->insert($adddata);
            }*/
        }
    }

    function ExecGetFollowList($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $min_time = $data['min_time'];
        $followings = $data['followings']; //关注列表
        if ($has_more) {
            $parameter = json_decode($tasklistdetail['parameter'], true);
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            $parameter = ["sec_uid" => $parameter['sec_uid'], "uid" => $parameter['uid'], "max_time" => $min_time, "token" => $jstoken, "proxy" => $proxy];
            $this->add_new_task($tasklistdetail, $parameter);
        }
        if (empty($followings)) throw new ValidateException('没有数据');
        foreach ($followings as $k => $v) {
            $member_id = db('member')->where('uid', $tasklistdetail['crux'])->value('member_id');
            $adddata['nickname'] = $v['nickname'];
            $adddata['member_id'] = $member_id;
            $adddata['create_time'] = $v['create_time'];
            $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
            $adddata['sec_uid'] = $v['sec_uid'];
            $adddata['following_count'] = $v['following_count'];
            $adddata['follower_count'] = $v['follower_count'];
            $adddata['favoriting_count'] = $v['favoriting_count'];
            $adddata['unique_id'] = $v['unique_id'];
            $adddata['uid'] = $v['uid'];
            $adddata['aweme_count'] = $v['aweme_count'];
            $adddata['region'] = transCountryCode($v['region']);
            $adddata['ifpic'] = 1;
            $where['member_id'] = $member_id;
            $where['uid'] = $v['uid'];
            $followinglistinfo = db('followinglist')->where($where)->find();
            if ($followinglistinfo) {
                $res = db('followinglist')->where($where)->update($adddata);
            } else {
                $res = db('followinglist')->insert($adddata);
            }
        }
    }

    function ExecGetCommentList($data, $tasklistdetail)
    {
        $total = $data['total'];
        if ($total <= 0) throw new ValidateException('没有评论数据');

        $has_more = $data['has_more']; //是否有更多
        $cursor = $data['cursor'];
        if ($has_more) {
            $parameter = json_decode($tasklistdetail['parameter'], true);
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            $parameter = ["aweme_id" => $parameter['aweme_id'], "type" => $parameter['type'], "cursor" => $cursor, "count" => $data['count'], "token" => $jstoken, "proxy" => $proxy];
            $this->add_new_task($tasklistdetail, $parameter);
        }
        $comments = $data['comments']; //评论列表
        if (empty($comments)) throw new ValidateException('没有评论数据');
        $insert_u = [];
        $insert_list = [];
        foreach ($comments as $k => $v) {
            $cid = $v['cid'];
            $comment_language = $v['comment_language'];
            $text = $v['text'];
            $create_time = $v['create_time'];
            $digg_count = $v['digg_count'];
            $aweme_id = $v['aweme_id'];
            $reply_id = $v['reply_id'];
            $reply_comment_total = $v['reply_comment_total'];
            $user_info = $v['user'];
            $uid = $user_info['uid'];
            $sec_uid = $user_info['sec_uid'];
            $nickname = $user_info['nickname'];
            $signature = $user_info['signature'];
            $account_region = $user_info['account_region'];
            $unique_id = $user_info['unique_id'];
            $aweme_count = $user_info['aweme_count'];
            $avatar_medium = $user_info['avatar_medium']['url_list'][0];
            $following_count = $user_info['following_count'];
            $follower_count = $user_info['follower_count'];
            $total_favorited = $user_info['total_favorited'];

            $insert_list = [
                'cid' => $cid,
                'comment_language' => $comment_language,
                'text' => $text,
                'create_time' => $create_time,
                'digg_count' => $digg_count,
                'aweme_id' => $aweme_id,
                'reply_id' => $reply_id,
                'reply_comment_total' => $reply_comment_total,
                'uid' => $uid,
                'sec_uid' => $sec_uid,
                'nickname' => $nickname,
                'signature' => $signature,
                'account_region' => $account_region,
                'unique_id' => $unique_id,
                'aweme_count' => $aweme_count,
                'avatar_medium' => $avatar_medium,
                'following_count' => $following_count,
                'follower_count' => $follower_count,
                'total_favorited' => $total_favorited,
                'ifpic' => 1
            ];
            if (db("comment_list")->where("cid", $cid)->count()) {
                db('comment_list')->where("cid", $cid)->update($insert_list);
            } else {
                db('comment_list')->insert($insert_list);
            }
        }
    }

    function ExecGetHomeVisitList($data, $tasklistdetail)
    {
        if (!$data['is_authorized']) {
            throw new ValidateException('未开启该隐私设置');
        }
        $userlist = $data['viewer_list'];
        foreach ($userlist as $k => $v) {
            $member_id = db('member')->where('uid', $tasklistdetail['crux'])->value('member_id');
            $user = $v['user'];
            $adddata['unique_id'] = $user['unique_id'];
            $adddata['avatar_thumb'] = $user['avatar_medium']['url_list'][0];
            $adddata['sec_uid'] = $user['sec_uid'];
            $adddata['nickname'] = $user['nickname'];
            $adddata['signature'] = $user['signature'];
            // $adddata['follower_status'] = $user['follower_status'];
            // $adddata['following_count'] = $user['following_count'];
            $adddata['total_favorited'] = $user['total_favorited'];
            $adddata['country'] = transCountryCode($user['region']);
            $adddata['aweme_count'] = $user['aweme_count'];
            $adddata['ifpic'] = 1;
            $adddata['member_id'] = $member_id;
            $adddata['uid'] = $user['uid'];
            $memberinfo = db('visitorlist')->where(['uid' => $user['uid'], 'member_id' => $member_id])->find();
            if ($memberinfo) {
                $res = db('visitorlist')->where(['uid' => $user['uid'], 'member_id' => $member_id])->update($adddata);
            } else {
                $res = db('visitorlist')->insert($adddata);
            }
        }
    }

    function add_new_task($tasklistdetail, $parameter)
    {
        $newtaskdetail = [
            "tasklist_id" => $tasklistdetail['tasklist_id'],
            "parameter" => is_string($parameter) ? $parameter : json_encode($parameter),
            "status" => 1,
            "create_time" => time(),
            "task_type" => $tasklistdetail['task_type'],
            "crux" => $tasklistdetail['crux']
        ];
        $detail_id = db("tasklistdetail")->insertGetId($newtaskdetail);
        db('tasklist')->where('tasklist_id', $tasklistdetail['tasklist_id'])->inc('task_num')->update();
        $newtaskdetail['tasklistdetail_id'] = $detail_id;
        $newtaskdetail['parameter'] = is_array($parameter) ? $parameter : json_decode($parameter, true);
        //$task = db('tasklist')->where('status', 1)->find();
        $redis_insert = connectRedis()->lPush(config("my.task_key"), json_encode($newtaskdetail));
        Log::write("redis_insert" . json_encode($redis_insert) . "\r\n");
    }

    function ExecGetAwemeList($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $max_cursor = $data['max_cursor'];
        if ($has_more) {
            $parameter = json_decode($tasklistdetail['parameter'], true);
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            $parameter = ["uid" => $parameter['uid'], "sec_uid" => $parameter['sec_uid'], "max_cursor" => $max_cursor, "token" => $jstoken, "proxy" => $proxy];
            $this->add_new_task($tasklistdetail, $parameter);
        }
        $aweme_list = $data['aweme_list']; //视频列表

        if (empty($aweme_list)) throw new ValidateException('没有视频');
        foreach ($aweme_list as $k => $v) {
            $member_id = db('member')->where('uid', $v['author']['uid'])->value('member_id');
            $insert['member_id'] = $member_id;
            // var_dump($v['author']['uid']);die;
            $insert['aweme_id'] = $v['aweme_id'];
            $insert['comment_count'] = $v['statistics']['comment_count']; //评论数量
            $insert['digg_count'] = $v['statistics']['digg_count'];
            $insert['share_count'] = $v['statistics']['share_count']; //分享数量
            $insert['play_count'] = $v['statistics']['play_count']; //播放数量
            $insert['video_desc'] = $v['desc'];
            $video = $v['video']; //视频数组
            $insert['video_url'] = $v['video']['play_addr']['url_list'][0]; //视频地址
            $insert['video_pic_url'] = $video['animated_cover']['url_list'][0];
            $insert['addtime'] = time();
            $insert['ifvideo'] = 1;
            $where['member_id'] = $member_id;
            $where['aweme_id'] = $v['aweme_id'];
            $videoinfo = db('membervideo')->where($where)->value('aweme_id');
            if ($videoinfo) {
                $res = db('membervideo')->where($where)->update($insert);
                // return $this->ajaxReturn($this->successCode,'更新成功');
                $msg = '更新成功' . $v['aweme_id'];
            } else {
                $res = db('membervideo')->insert($insert);
                $msg = '添加成功' . $v['aweme_id'];
            }
        }
        db('user_cursor')->where(['user_id' => $tasklistdetail['crux']])->save(['max_cursor' => $max_cursor]);
    }

    function ExecGetFansList($data, $tasklistdetail)
    {
        $has_more = $data['has_more']; //是否有更多
        $min_time = $data['min_time'];
        if ($has_more) {
            $parameter = json_decode($tasklistdetail['parameter'], true);
            $jstoken = doToken('', 2);
            $proxy = getHttpProxy($jstoken['user']['uid']);
            $parameter = ["uid" => $parameter['uid'], "sec_uid" => $parameter['sec_uid'], "max_time" => $min_time, "token" => $jstoken, "proxy" => $proxy];
            $this->add_new_task($tasklistdetail, $parameter);
        }
        $followings = $data['followers']; //粉丝列表
        if (empty($followings)) throw new ValidateException('没有数据');
        $member_id = db("member")->where("uid", $tasklistdetail['crux'])->value("member_id");
        foreach ($followings as $k => $v) {
            $adddata['nickname'] = $v['nickname'];
            $adddata['member_id'] = $member_id;
            $adddata['create_time'] = $v['create_time'];
            $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
            $adddata['sec_uid'] = $v['sec_uid'];
            $adddata['following_count'] = $v['following_count'];
            $adddata['follower_count'] = $v['follower_count'];
            $adddata['favoriting_count'] = $v['favoriting_count'];
            $adddata['unique_id'] = $v['unique_id'];
            $adddata['uid'] = $v['uid'];
            $adddata['aweme_count'] = $v['aweme_count'];
            $adddata['region'] = transCountryCode($v['region']);
            $adddata['ifpic'] = 1;
            $where['member_id'] = $member_id;
            $where['uid'] = $v['uid'];
            $followinglistinfo = db('fanslist')->where($where)->find();
            if ($followinglistinfo) {
                $res = db('fanslist')->where($where)->update($adddata);
            } else {
                $res = db('fanslist')->insert($adddata);
            }
        }
    }

    function ExecGetSelfUserInfo($data, $tasklistdetail)
    {
        $user_info = $data['user'];
        $updata = [];
        $updata['avatar_thumb'] = $user_info['avatar_medium']['url_list'][0];
        $updata['follower_status'] = $user_info['follower_count'];
        $updata['following_count'] = $user_info['following_count'];
        $updata['total_favorited'] = $user_info['total_favorited'];
        $updata['nickname'] = $user_info['nickname'];
        $updata['unique_id'] = $user_info['unique_id'];
        $updata['signature'] = $user_info['signature'];
        $updata['member_type'] = $user_info['account_type'];
        $updata['aweme_count'] = $user_info['aweme_count'];
        $updata['ifup'] = 0;
        $updata['ifpic'] = 1;
        $updata['updata_time'] = time();
        //return json_encode($updata);
        $res = db('member')->where('uid', $tasklistdetail['crux'])->update($updata);
    }

    function ExecPushVideo($data, $tasklistdetail)
    {
        $member_id = db('member')->where('uid', $tasklistdetail['crux'])->value('member_id');
        $insert['member_id'] = $member_id;
        $insert['aweme_id'] = $data['aweme']['aweme_id'];
        $insert['comment_count'] = 0; //评论数量
        $insert['digg_count'] = 0;
        $insert['share_count'] = 0; //分享数量
        $insert['play_count'] = 0; //播放数量
        $insert['video_desc'] = $data['aweme']['desc'];
        $insert['video_url'] = $data['aweme']['video']['play_addr']['url_list'][0]; //视频地址
        $insert['video_pic_url'] = $data['aweme']['video']['cover']['url_list'][0];
        $insert['addtime'] = time();
        $insert['ifvideo'] = 1;
        $res = db('membervideo')->insertGetId($insert);
    }

    function ExecUpdateUserData($data, $tasklistdetail)
    {
        $user_info = $data['user'];
        $memdata['avatar_thumb'] = $user_info['avatar_medium']['url_list'][0];
        $memdata['nickname'] = $user_info['nickname'];
        $memdata['unique_id'] = $user_info['unique_id'];
        $memdata['signature'] = $user_info['signature'];
        $memdata['member_type'] = $user_info['account_type'];

        if (isset($user_info['follower_count'])) $memdata['follower_status'] = $user_info['follower_count'];
        if (isset($user_info['following_count'])) $memdata['following_count'] = $user_info['following_count'];
        if (isset($user_info['total_favorited'])) $memdata['total_favorited'] = $user_info['total_favorited'];
        if (isset($user_info['aweme_count'])) $memdata['aweme_count'] = $user_info['aweme_count'];
        $memdata['ifup'] = 0;
        $memdata['ifpic'] = 1;
        $memdata['updata_time'] = time();
        $res = db('member')->where('uid', $tasklistdetail['crux'])->update($memdata);
    }






// 	function iftaskss(){
// 	    $user = db('member')->where('ifup',1)->field('sec_uid,uid')->select();
// 	    foreach ($user as $k => $v){
// 	        $adddata['parameter'] = json_encode($v);
// 	        $adddata['create_time'] = time();
// 	        $adddata['task_type'] = 'UpdateUserData';
// 	        $adddata['tasklist_id'] = 1;
//             $adddata['crux'] = $v['uid'];
// 	        db('tasklistdetail')->insert($adddata);
// 	    }

// 	}

    //每日自动更新用户数据（用获取个人的接口，请求需要带上代理）
    function Dsuser()
    {
        $user = db('member')->where('status', 1)->field('token,uid')->select();
        $addtask['task_name'] = '每日自动更新用户数据';
        $addtask['task_type'] = 'GetUserInfo';
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        foreach ($user as $k => $v) {
            $adddata['parameter'] = json_encode($v);
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetUserInfo';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            db('tasklistdetail')->insert($adddata);
        }
    }

    //更新粉丝
    function Dsfensi()
    {
        $user = db('member')->where('follower_status', '>', 0)->where('status', 1)->field('sec_uid,uid')->select();
        $addtask['task_name'] = '更新账户粉丝列表';
        $addtask['task_type'] = 'GetFansList';
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        foreach ($user as $k => $v) {
            $v['max_time'] = 0;
            // var_dump($v);die;
            $adddata['parameter'] = json_encode($v);
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetFansList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            db('tasklistdetail')->insert($adddata);
        }
        // var_dump($user);
    }

    //更新关注列表
    function Dsgzhu()
    {
        $user = db('member')->where('following_count', '>', 0)->where('status', 1)->field('sec_uid,uid')->select();
        // var_dump($user);die;
        $addtask['task_name'] = '更新账户关注列表';
        $addtask['task_type'] = 'GetFollowList';
        $addtask['task_num'] = count($user);
        $addtask['create_time'] = time();
        $addtask['status'] = 1;
        $usertask = db('tasklist')->insertGetId($addtask);
        foreach ($user as $k => $v) {
            $v['max_time'] = 0;
            // var_dump($v);die;
            $adddata['parameter'] = json_encode($v);
            $adddata['create_time'] = time();
            $adddata['task_type'] = 'GetFollowList';
            $adddata['tasklist_id'] = $usertask;
            $adddata['crux'] = $v['uid'];
            db('tasklistdetail')->insert($adddata);
        }
    }

    /**
     * @api {post} /Tasklist/update 03、修改
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            tasklist_id 主键ID (必填)
     * @apiParam (输入参数：) {int}                status 状态 未完成|1|success,已完成|0|danger
     * @apiParam (输入参数：) {string}            create_time 创建时间
     * @apiParam (输入参数：) {string}            task_num 任务数量
     * @apiParam (输入参数：) {string}            task_type 任务类型
     * @apiParam (输入参数：) {string}            task_name 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码  201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"操作失败"}
     */
    function update()
    {
        $postField = 'tasklist_id,status,create_time,task_num,task_type,task_name';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['tasklist_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['tasklist_id'] = $data['tasklist_id'];
        $res = TasklistService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Tasklist/delete 04、删除
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            tasklist_ids 主键id 注意后面跟了s 多数据删除
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功消息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","msg":"操作成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"操作失败"}
     */
    function delete()
    {
        $idx = $this->request->post('tasklist_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['tasklist_id'] = explode(',', $idx);
        try {
            TasklistModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


}

