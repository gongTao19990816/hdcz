<?php

namespace app\api\controller;

use app\api\job\FollowTask;
use app\api\model\Externalmember as ExternalmemberModel;
use think\exception\ValidateException;
use think\facade\Validate;

/**
 * 发布任务类
 */
class Push extends Common
{

    /**
     * @api {get} /Push/screen_chat 02、获取私信任务筛选后的用户数量
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  获取私信任务筛选后的用户数量
     * @apiParam (输入参数：) {int}              [reset_status] 重置粉丝状态
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_chat()
    {
        $params = $this->request->get();

        $external_member_num = db('external_member')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->where('country', 'in', $params['country_list']);
        })->field("uid,sec_uid")->select()->toArray();

        return $this->ajaxReturn($this->successCode, '返回成功', $external_member_num);
    }

    /**
     * @api {get} /Push/screen_comment_digg 02、获取评论任务筛选后的评论数量
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  获取评论任务筛选后的评论数量
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [comment_digg_count_lower_limit] 评论获赞小于
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_comment_digg()
    {
        $params = $this->request->get();

        $comment_num = db('comment_list')
            ->where(function ($query) use ($params) {
                $query->where('account_region', 'in', $params['country_list']);
                if ($params['comment_digg_count_lower_limit']) {
                    $query->where('digg_count', '<', $params['comment_digg_count_lower_limit']);
                }
            })
            ->where([
                'tasklist_id' => ['in', $params['tasklist_id_list']],
            ])
            ->field("cid,aweme_id")->count();
        if ($comment_num) {
            $num = $comment_num;
        } else {
            $num = 0;
        }

        return $this->ajaxReturn($this->successCode, '返回成功', $num);
    }

    /**
     * @api {get} /Push/screen_follow 02、获取关注任务筛选后的用户数量
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  获取关注任务筛选后的用户数量
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_follow_upper_limit] 单号关注上限
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"","data":0}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function screen_follow()
    {
        $params = $this->request->get();

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }

        $external_member_num = ExternalmemberModel::where(['country' => ['in', $params['country_list']], 'secret' => 0])
            ->where(function ($query) use ($params) {
                if ($params['follower_status']) $query->where('follower_status', '<', $params['follower_status']);
                if ($params['following_count']) $query->where('following_count', '<', $params['following_count']);
                if ($params['total_favorited']) $query->where('total_favorited', '<', $params['total_favorited']);
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 0);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_follow", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 0);
                }
            })
            ->where('tasklist_id', 'in', $params['tasklist_id_list'])
            ->field('external_member_id')->count();

        return $this->ajaxReturn($this->successCode, '返回成功', $external_member_num);
    }

    /**
     * @api {post} /Push/chat 02、发布私信任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布评论点赞任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_chat_upper_limit] 单号私信上限
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {int}              [total_task_num] 总私信上线
     * @apiParam (输入参数：) {int}              [privateletter_id] 私信素材库ID
     * @apiParam (输入参数：) {int}              [type_list] 素材类型
     * @apiParam (输入参数：) {int}              [reset_status] 重置粉丝状态
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    public function chat()
    {
        $params = $this->request->post();

        //验证规则
        $rule = [
            'grouping_id' => 'require',
            'typecronl_id' => 'require',
            'country_list' => 'require',
            'tasklist_id_list' => 'require',
            'user_chat_upper_limit' => 'require',
//            'total_task_num' => '',
            'privateletter_id' => 'require',
            'type_list' => 'require',
//            'reset_status' => '',
            'task_name' => 'require',
        ];

        //错误提示
        $msg = [
            'type_list.require' => 'type_list（私信类型）必传',
            'country_list.require' => '国家必传',
            'user_chat_upper_limit.require' => 'user_chat_upper_limit（单号私信上限）必传',
//            'total_task_num.require' => 'total_task_num（总私信上限）必传',
//            'reset_status.require' => 'reset_status（重置粉丝状态）必传',
            'task_name.require' => 'task_name（任务名称）必传',
            'privateletter_id.require' => 'privateletter_id（私信素材库ID）必传',
            'uid_list.require' => 'uid_list（账号列表）必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }
        $type_list = $params['type_list'];
        if (count($type_list) > 3) {
            throw new ValidateException('私信类型最多选择三种');
        }
        if (!in_array("ChatText", $type_list) && !in_array("ChatProfile", $type_list) && !in_array("ChatAweme", $type_list) && !in_array("ChatLink", $type_list)) {
            throw new ValidateException(['未知的私信类型', ['type_list' => ['ChatText', 'ChatProfile', 'ChatAweme', 'ChatLink']]]);
        }
        $total_task_num = $params['total_task_num'];
        //私信素材查询
        $privateletters_num = db('privateletter')->where("typecontrol_id", $params['b_typecontrol_id'])->count();
        if ($privateletters_num < $total_task_num) {
            throw new ValidateException('私信素材数量不足');
        }
        checkTaskNum($total_task_num);
        $redis_key = get_task_key('chat');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => "Chat",
            "task_num" => $total_task_num,
            'redis_key' => $redis_key,
            "create_time" => time(),
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        //操作用户查询
        $members = db('member')->where('uid', 'in', $params['uid_list'])->field('uid,sec_uid,unique_id,token')->select()->toArray();
        //被操作用户查询
        $external_members = db('external_member')->where(function ($query) use ($params) {
            if ($params['reset_status']) {
                $query->where("if_chat", 0);
            }
            $query->where('country', 'in', $params['country_list']);
        })->field("uid,sec_uid")->select()->toArray();
        $redis = connectRedis();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $params['user_chat_upper_limit'];
            db('task_uid')->insert($uid_task);
            if ($total_task_num) {
                for ($i = 0; $i < $params['user_chat_upper_limit']; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);

                    foreach ($params['type_list'] as $task_type) {
                        switch ($task_type) {
                            case "ChatText":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '0'])->value("content")->toArray();
                                break;
                            case "ChatProfile":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '2'])->value("content")->toArray();
                                break;
                            case "ChatAweme":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '3'])->value("content")->toArray();
                                break;
                            case "ChatLink":
                                $content = db('privateletter')->where(["typecontrol_id" => $params['b_typecontrol_id'], 'type' => '1'])->value("content")->toArray();
                                break;
                        }
                        if (!isset($content)) continue;
                        $token = doToken($member['token']);
                        //取http代理
                        $proxy = getHttpProxy($token['user']['uid']);

                        $parameter = [
                            'receiver' => $external_member['uid'],
                            'client_id' => create_uuid('client_id_'),
                            'content' => $content,
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $external_member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        $task_details[] = $task_detail;
                    }
                    $total_task_num--;
                }
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }

    /**
     * @api {post} /Push/follow 02、发布关注任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布关注任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_follow_upper_limit] 单号关注上限
     * @apiParam (输入参数：) {int}              [rate_min] 频率最小
     * @apiParam (输入参数：) {int}              [rate_max] 频率最大
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function follow()
    {
        $params = $this->request->post();
        $task_type = "Follow";

        //验证规则
        $rule = [
            'grouping_id' => 'require',
            'typecronl_id' => 'require',
            'country_list' => 'require',
            'tasklist_id_list' => 'require',
            'user_follow_upper_limit' => 'require',
            'rate_min' => 'require',
            'rate_max' => 'require',
            'can_fail_num' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
            'grouping_id.require' => '分组id必传',
            'typecronl_id.require' => '分组id必传',
            'country_list.require' => '国家必传',
            'tasklist_id_list.require' => 'tasklist_id_list（数据来源（采集任务ID））必传',
            'user_follow_upper_limit.require' => 'user_follow_upper_limit（单号关注上限）必传',
            'rate_min.require' => 'rate_min（关注频率最小值）必传',
            'rate_max.require' => 'rate_max（关注频率最大值）必传',
            'can_fail_num.require' => 'can_fail_num（可失败次数）必传',
            'task_name' => '任务名称必传'
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        //操作用户查询
        $members = db('member')->where(['typecontrol_id' => $params['typecronl_id'], 'grouping_id' => $params['grouping_id']])->field('uid,sec_uid,unique_id,token')->select();
        $user_follow_upper_limit = $params['user_follow_upper_limit'];
        $total_task_num = 0;
        foreach ($members as &$member) {
            // 该账号今日关注次数
            $member_follow_num = db("followuser")->where("uid", $member['uid'])->whereDay("create_time")->count();
            $member_task_num = ($user_follow_upper_limit - $member_follow_num);
            $member['today_follow_num'] = $member_follow_num;
            if (!$member_task_num) {
                continue;
            }
            $total_task_num += $member_task_num;
        }
        $external_members = ExternalmemberModel::where(['secret' => 0])
            ->where(function ($query) use ($params) {
                if ($params['follower_status']) $query->where('follower_status', '<', $params['follower_status']);
                if ($params['following_count']) $query->where('following_count', '<', $params['following_count']);
                if ($params['total_favorited']) $query->where('total_favorited', '<', $params['total_favorited']);
                $black_list = $params['black_list'];
                if (in_array("no_avatar", $black_list)) {
                    $query->where("has_avatar", 0);
                }
                if (in_array("no_aweme", $black_list)) {
                    $query->where("aweme_count", '>', 0);
                }
                if (in_array("historical_users", $black_list)) {
                    $query->where("is_follow", 1);
                }
                if (in_array("no_nickname", $black_list)) {
                    $query->where("has_nickname", 0);
                }
            })
            ->where(['tasklist_id' => ['in', implode($params['tasklist_id_list'], ",")], 'country' => ['in', implode($params['country_list'], ",")]])
            ->field("uid,sec_uid")->select()->toArray();

        if (count($external_members) < $total_task_num) {
            throw new ValidateException('当前条件下可关注博主仅剩' . count($external_members) . '个');
        }
        checkTaskNum($total_task_num);
        $redis_key = get_task_key('follow');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => $task_type,
            "task_num" => $total_task_num,
            "create_time" => time(),
            'redis_key' => $redis_key,
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
        //往中间表中添加数据

        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        foreach ($members as $member) {
            //往中间表中添加数据
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $user_follow_upper_limit;
            db('task_uid')->insert($uid_task);
            for ($i = 0; $i < ($user_follow_upper_limit - $member['today_follow_num']); $i++) {
                if ($external_members) {
                    $delay = rand($params['rate_min'], $params['rate_max']); //关注频率，延迟多少秒执行
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $external_member_index = array_rand($external_members);
                    $external_member = $external_members[$external_member_index];
                    unset($external_members[$external_member_index]);
                    if ($external_member) {
                        $token = doToken($member['token']);
                        //取http代理
                        $proxy = getHttpProxy($token['user']['uid']);

                        $parameter = [
                            'member_uid' => $member['uid'],
                            'user_id' => $external_member['uid'],
                            'sec_user_id' => $external_member['sec_uid'],
                            'from' => 19,
                            'from_pre' => 13,
                            'channel_id' => 3,
                            'type' => 1, // # 1 表示关注，0 表示取消关注
                            "token" => $token,
                            "proxy" => $proxy
                        ];
                        $task_detail = [
                            "tasklist_id" => $task_id,
                            "parameter" => $parameter,
                            "status" => 1,
                            "create_time" => time(),
                            "task_type" => $task_type,
                            "crux" => $member['uid']
                        ];
                        unset($task_detail['tasklistdetail_id']);
                        //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                        $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                        $task_detail['tasklistdetail_id'] = $task_detail_id;
                        queue(FollowTask::class, $task_detail, $delay);
                    }
                }
            }


        }
    }

    /**
     * @api {post} /Push/comment_digg 02、发布评论点赞任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布评论点赞任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {string}           [country_list] 国家
     * @apiParam (输入参数：) {array}            [tasklist_id_list] 数据来源ID列表
     * @apiParam (输入参数：) {int}              [user_digg_upper_limit] 单号关注上限
     * @apiParam (输入参数：) {int}              [can_fail_num] 连续失败次数
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function comment_digg()
    {
        $params = $this->request->post();
        $task_type = "CommentDigg";

        //验证规则
        $rule = [
            'grouping_id' => 'require',
            'typecronl_id' => 'require',
            'country_list' => 'require',
            'user_digg_upper_limit' => 'require',
            'can_fail_num' => 'require',
            'tasklist_id_list' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
            'grouping_id.require' => '分组id必传',
            'typecronl_id.require' => '分组id必传',
            'country_list.require' => '国家必传',
            'user_digg_upper_limit.require' => 'user_digg_upper_limit（单号点赞上限）必传',
            'can_fail_num.require' => 'can_fail_num（可失败次数）必传',
            'tasklist_id_list.require' => 'tasklist_id_list（数据来源（采集任务ID））必传',
            'task_name.require' => '任务名称必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        if ($params['black_list']) {
            $black_list = $params['black_list'];
            if (!in_array("no_avatar", $black_list) && !in_array("no_aweme", $black_list) && !in_array("historical_users", $black_list) && !in_array("no_nickname", $black_list)) {
                throw new ValidateException(['未知的黑名单类型', ['black_list' => ['no_avatar', 'no_aweme', 'historical_users', 'no_nickname']]]);
            }
        }
        //操作用户查询
        $members = db('member')->where('uid', 'in', $params['uid_list'])->field('uid,sec_uid,unique_id,token')->select();
        $comment_list = db('comment_list')
            ->where(function ($query) use ($params) {
                if ($params['comment_digg_count_lower_limit']) {
                    $query->where('digg_count', '<', $params['comment_digg_count_lower_limit']);
                }
            })
            ->where([
                'account_region' => ['in', $params['country_list']],
                'tasklist_id' => ['in', $params['tasklist_id_list']],
            ])
            ->field("cid,aweme_id")->select()->toArray();
        $user_digg_upper_limit = $params['user_digg_upper_limit'];
        $task_num = count($members) * $user_digg_upper_limit;
        $new_task_num = min(count($comment_list), $task_num);
        checkTaskNum($new_task_num);
        $redis_key = get_task_key('comment_digg');
        $task = [
            "task_name" => $params['task_name'],
            "task_type" => $task_type,
            "task_num" => $new_task_num,
            "create_time" => time(),
            'redis_key' => $redis_key,
            "status" => 1,
            "complete_num" => 0,
            'api_user_id' => $this->request->uid
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        $redis = connectRedis();
        $task_details = [];
        foreach ($members as $member) {
            $uid_task['uid'] = $member['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $user_digg_upper_limit;
            db('task_uid')->insertGetId($uid_task);
            if ($comment_list) {
                for ($i = 0; $i < $user_digg_upper_limit; $i++) {
                    // 从查询出来的评论列表随机取一个评论，并从评论列表删除
                    $comment_index = array_rand($comment_list);
                    $comment = $comment_list[$comment_index];
                    unset($comment_list[$comment_index]);

                    $token = doToken($member['token']);
                    //取http代理
                    $proxy = getHttpProxy($token['user']['uid']);

                    $parameter = [
                        'aweme_id' => $comment['aweme_id'],
                        'cid' => $comment['cid'],
                        'uid' => $member['uid'],
                        "token" => $token,
                        "proxy" => $proxy
                    ];
                    $task_detail = [
                        "tasklist_id" => $task_id,
                        "parameter" => $parameter,
                        "status" => 1,
                        "create_time" => time(),
                        "task_type" => $task_type,
                        "crux" => $comment['cid']
                    ];
                    unset($task_detail['tasklistdetail_id']);
                    //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                    $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                    $task_detail['tasklistdetail_id'] = $task_detail_id;

                }
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
    }


    /**
     * @api {post} /Push/video 02、发布视频任务
     * @apiGroup Push
     * @apiVersion 1.0.0
     * @apiDescription  发布视频任务
     * @apiParam (输入参数：) {int}              [grouping_id] 分组ID
     * @apiParam (输入参数：) {int}              [typecronl_id] 分类ID
     * @apiParam (输入参数：) {int}              [video_num] 视频数量
     * @apiParam (输入参数：) {int}              [label_num] 标签数量
     * @apiParam (输入参数：) {int}              [user_num] @用户数量
     * @apiParam (输入参数：) {int}              [push_time] 发布时间
     * @apiParam (输入参数：) {string}           [text] 主题内容
     * @apiParam (输入参数：) {bool}             [text_round] 是否随机主题内容
     * @apiParam (输入参数：) {string}           [task_name] 任务名称
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.msg 返回成功信息
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"创建成功"}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function video()
    {
        $params = $this->request->post();
        $task_type = "PushVideo";

        //验证规则
        $rule = [
            'typecontrol_id' => 'require',
            'video_num' => 'require',
            'grouping_id' => 'require',
            'task_name' => 'require'
        ];

        //错误提示
        $msg = [
            'typecontrol_id.require' => '分类必传',
            'video_num.require' => '视频数量必传',
            'grouping_id.require' => '分组id必传',
            'task_name.require' => '任务名称必传',
        ];
        //调用验证器
        $validate = Validate::rule($rule)->message($msg);
        if (!$validate->check($params)) {
            throw new ValidateException($validate->getError());
        }

        // $uid_list = $params["uid_list"];
        $typecontrol_id = $params["typecontrol_id"];
        $grouping_id = $params["grouping_id"];
        $video_num = $params["video_num"];
        $label_num = $params["label_num"];
        $user_num = $params["user_num"];
        $task_name = $params["task_name"];
        $push_time = $params['push_time'];
        $text = $params["text"];
        $text_round = $params["text_round"];
        if (!$text && !$text_round) {
            throw new ValidateException("主题内容与是否随机主题必传其一");
        }

        $uid_list = db('member')->where(['typecontrol_id' => $params['typecontrol_id'], 'grouping_id' => $params['grouping_id']])->field('token,uid')->select()->toArray();
        $total_need_push_video_num = count($uid_list) * $video_num;
        $can_use_video_num = db("material")->where(['typecontrol_id' => $typecontrol_id, 'grouping_id' => $params['grouping_id']])->count();
        if ($can_use_video_num < $total_need_push_video_num) {
            throw new ValidateException('视频素材库可用数量不足，剩余' . $can_use_video_num);
        }
        $text_list = [];
        if ($text_round) {
            $total_need_push_text_num = count($uid_list) * $video_num;
            $can_use_text_num = db("subjectcontent")->where(['typecontrol_id' => $typecontrol_id, 'grouping_id' => $params['grouping_id']])->count();
            if ($can_use_text_num < $total_need_push_text_num) {
                throw new ValidateException('主题内容素材库可用数量不足，剩余' . $can_use_text_num);
            }
            $text_list = db("subjectcontent")->where(['typecontrol_id' => $typecontrol_id, "status" => 1])->limit($total_need_push_text_num)->field("subjectcontent_id,content,use_num")->orderRaw("rand()")->select();
        }
        checkTaskNum($total_need_push_video_num);
        $redis_key = get_task_key('push_video');
        $task = [
            "task_name" => $task_name,
            "task_type" => $task_type,
            "task_num" => $total_need_push_video_num,
            "create_time" => time(),
            "api_user_id" => $this->request->uid,
            'redis_key' => $redis_key,
            'tmp_redis_key' => '',
            "status" => 1,
            "complete_num" => 0
        ];
        $task_id = db("tasklist")->insertGetId($task);
        echo json_encode(['status' => 200, 'msg' => "任务发布中，可使用GET传递task_id访问'/api/tasklist/get_task_create_progress'查询创建进度", "data" => ['task_id' => $task_id]]);
        flushRequest();
        $redis = connectRedis();
        $task_details = [];
        foreach ($uid_list as $uid) {
            $uid_task['uid'] = $uid['uid'];
            $uid_task['tasklist_id'] = $task_id;
            $uid_task['num'] = $video_num;
            db('task_uid')->insert($uid_task);
            //取登录后的token
//            $user_info = db('member')->field('token')->where(['uid' => $uid, 'status' => 1])->find();
//            if (empty($user_info)) continue;

            $video_list = db("material")->where(['typecontrol_id' => $typecontrol_id, "grouping_id" => $grouping_id])->limit($video_num)->field("video_url")->orderRaw("rand()")->select();
            foreach ($video_list as $item) {
                $domain = config("my.host_url");
                $video_url = $domain . $item['video_url'];
                $label_str = $user_str = '';
                //组装标签
                if ($label_num) {
                    $label_list = db("label")->where("status", 1)->limit($label_num)->field("label")->orderRaw("rand()")->select();
                    foreach ($label_list as $row) {
                        $user_str = $user_str . "#" . trim($row['label']);
                    }
                }
                //组装@用户
                if ($user_num) {
                    $user_list = db("member")->where("status", 1)->limit($user_num)->field("unique_id")->orderRaw("rand()")->select();
                    foreach ($user_list as $row) {
                        $user_str = $user_str . "@" . $row['unique_id'];
                    }
                }
                //获取随机主题内容
                if ($text_round && $text_list) {
                    $index = rand($text_list, 1);
                    $text = $text_list[$index];
                    unset($text_list[$index]);
                }
                //组装要发布的主题内容
                $text = $text . $label_str . $user_str;

                $token = doToken($uid['token']);
                //取http代理
                $proxy = getHttpProxy($uid['uid']);

                $parameter = ["video_url" => $video_url, "text" => $text, "uid" => $uid['uid'], "token" => $token, "proxy" => $proxy];
                $task_detail = [
                    "tasklist_id" => $task_id,
                    "parameter" => $parameter,
                    "status" => 1,
                    "api_user_id" => $this->request->uid,
                    "create_time" => time(),
                    "task_type" => $task_type,
                    "crux" => $uid
                ];
                unset($task_detail['tasklistdetail_id']);
                //$task_detail_id = db("tasklistdetail")->insertGetId($task_detail);
                $task_detail_id = \app\api\model\TaskListDetail::add($task_detail);
                $task_detail['tasklistdetail_id'] = $task_detail_id;
            }
        }
        foreach ($task_details as $detail) {
            $redis->lPush($redis_key, json_encode($detail));
        }
        //return $this->ajaxReturn($this->successCode, "视频任务发布成功");
    }
    //

}