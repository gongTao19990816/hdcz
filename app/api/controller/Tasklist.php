<?php
/*
 module:		任务表
 create_time:	2022-12-09 16:35:00
 author:		大怪兽
 contact:
*/

namespace app\api\controller;

use app\api\model\Tasklist as TasklistModel;
use app\api\model\TaskUid;
use app\api\service\TasklistService;
use RedisException;
use think\Exception;
use think\exception\ValidateException;

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
     * @apiParam (输入参数：) {int}            [status] 状态 未完成|1|success,已完成|0|danger,暂停|2|danger
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

        $res = TasklistService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['create_time'] = date("Y-m-d H:i:s", $row['create_time']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    function task_uids()
    {
        $task_id = $this->request->get("tasklist_id");
        $page = $this->request->get("page",1,"intval");
        $limit = $this->request->get('limit', 20, 'intval');
        // var_dump($limit);die;
        $list = TaskUid::where("tasklist_id", $task_id)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        $res['count'] = $list['total'];
        $res['list'] = $list['data'];
        foreach ($res['list'] as &$item) {
            $member = \app\api\model\Member::where("uid", $item['uid'])->field('avatar_thumb,uid,nickname,signature,phone_number,backups_name,typecontrol_id')->find()->toArray();
            $member['type_parent_names_text'] = getTypeParentNames($member['typecontrol_id']);
            $item['member'] = $member;
            $task_type = db('tasklist')->where('tasklist_id',$item['tasklist_id'])->value('task_type');
            $item['pici_num'] = db('tasklistdetail')->where(['task_type'=>$task_type,'crux'=>$item['uid']])->count();
            $item['success_num'] = db('tasklistdetail')->where(['tasklist_id'=>$item['tasklist_id'],'crux'=>$item['uid'],'status'=>0])->count();
            $item['fail_num'] = db('tasklistdetail')->where(['tasklist_id'=>$item['tasklist_id'],'crux'=>$item['uid'],'status'=>2])->count();
            $item['tasklistdetail'] = db('tasklistdetail')->where(['tasklist_id'=>$item['tasklist_id'],'crux'=>$item['uid']])->order('tasklist_id desc')->find();

        }
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    /**
     * @api {get} /tasklist/get_exec_progress 01、获取任务执行进度
     * @apiGroup tasklist
     * @apiVersion 1.0.0
     * @apiDescription  获取任务执行进度
     * @apiParam (输入参数：) {int}              [tasklist_id] 任务ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"获取任务执行进度","data":{"now":1,"total":10}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function get_exec_progress()
    {
        $taskId = $this->request->get("tasklist_id/n");
        if (!$taskId) {
            throw new ValidateException("请传递任务ID");
        }
        $taskNums = db("tasklist")->where("tasklist_id", $taskId)->field("task_num,complete_num,fail_num");
        return $this->ajaxReturn($this->successCode, "获取任务创建进度成功", ['now' => $taskNums['fail_num'] + $taskNums['complete_num'], 'total' => $taskNums['task_num']]);
    }

    /**
     * @api {get} /tasklist/get_create_progress 01、获取任务创建进度
     * @apiGroup tasklist
     * @apiVersion 1.0.0
     * @apiDescription  获取任务创建进度
     * @apiParam (输入参数：) {int}              [tasklist_id] 任务ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数量
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","mas":"获取任务创建进度成功","data":{"now":1,"total":10}}
     * @apiErrorExample {json} 02 失败示例
     * {"status":" 201","msg":"查询失败"}
     */
    function get_task_create_progress()
    {
        $taskId = $this->request->get("tasklist_id/n");
        if (!$taskId) {
            throw new ValidateException("请传递任务ID");
        }
        $taskNum = db("tasklist")->where("tasklist_id", $taskId)->value("task_num");
        $taskDetailNum = db("tasklistdetail")->where("tasklist_id", $taskId)->count();
        //$progress = $taskDetailNum && $taskNum ? round(($taskDetailNum * 100) / $taskNum, 2) : 0;
        return $this->ajaxReturn($this->successCode, "获取任务创建进度成功", ['now' => $taskDetailNum, 'total' => $taskNum]);
    }

    /**
     * @api {post} /Tasklist/pause 02、暂停任务
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  暂停任务
     * @apiParam (输入参数：) {int}           tasklist_id 任务ID
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
    function pause()
    {
        $task_id = $this->request->post('tasklist_id/n');
        try {
            $task = \app\api\model\Tasklist::where("tasklist_id", $task_id)->field('redis_key')->find();
            if ($task) {
                $key = $task->redis_key;
                $redis = connectRedis();
                try {
                    if (!$redis->exists($key)) {
                        throw new \think\Exception('key：' . $key . '不存在');
                    }
                } catch (RedisException $e) {
                    throw new \think\Exception('获取redis键是否存在时：' . $e->getMessage());
                }
                try {
                    $rows = $redis->lRange($key, 0, -1);
                } catch (RedisException $e) {
                    throw new \think\Exception('获取给定key全部数据时：' . $e->getMessage());
                }
                try {
                    $redis->unlink($key);
                } catch (RedisException $e) {
                    throw new \think\Exception('删除key时：' . $e->getMessage());
                }
                $tmp_key = 'tmp_' . $key;
                $task->status = 2;
                $task->tmp_redis_key = $tmp_key;
                $task->save();
                foreach ($rows as $row) {
                    try {
                        $redis->lPush($tmp_key, $row);
                    } catch (RedisException $e) {
                        throw new \think\Exception('给新的key添加数据时：' . $e->getMessage());
                    }
                }
            } else {
                throw new \think\Exception('未从任务表中获取到redis_key');
            }
        } catch (Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Tasklist/pause 03、继续任务
     * @apiGroup Tasklist
     * @apiVersion 1.0.0
     * @apiDescription  继续任务
     * @apiParam (输入参数：) {int}           tasklist_id 任务ID
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
    function continue()
    {
        $task_id = $this->request->post('tasklist_id/n');
        try {
            $task = \app\api\model\Tasklist::where("tasklist_id", $task_id)->field('redis_key,tmp_redis_key')->find();
            if ($task) {
                $tmp_key = $task->tmp_redis_key;
                $redis = connectRedis();
                try {
                    if (!$redis->exists($tmp_key)) {
                        throw new \think\Exception('key：' . $tmp_key . '不存在');
                    }
                } catch (RedisException $e) {
                    throw new \think\Exception('获取redis键是否存在时：' . $e->getMessage());
                }
                try {
                    $rows = $redis->lRange($tmp_key, 0, -1);
                } catch (RedisException $e) {
                    throw new \think\Exception('获取给定key全部数据时：' . $e->getMessage());
                }
                try {
                    $redis->unlink($tmp_key);
                } catch (RedisException $e) {
                    throw new \think\Exception('删除key时：' . $e->getMessage());
                }
                $task->status = 1;
                $task->tmp_redis_key = '';
                $task->save();
                foreach ($rows as $row) {
                    try {
                        $redis->lPush($task->redis_key, $row);
                    } catch (RedisException $e) {
                        throw new \think\Exception('给新的key添加数据时：' . $e->getMessage());
                    }
                }
            } else {
                throw new \think\Exception('未从任务表中获取到tmp_redis_key');
            }
        } catch (Exception $e) {
            throw new ValidateException($e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
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

