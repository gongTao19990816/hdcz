<?php
/*
 module:		用户作品
 create_time:	2022-11-23 16:28:26
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Membervideo as MembervideoModel;
use app\api\service\MembervideoService;
use think\exception\ValidateException;

class Membervideo extends Common
{


    /**
     * @api {post} /Membervideo/index 01、首页数据列表
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [member_id] 用户
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
        $where['a.member_id'] = $this->request->post('member_id', '', 'serach_in');

        $field = 'a.*,b.nickname';
        $orderby = 'membervideo_id desc';

        $res = MembervideoService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {
            $row['comment_count'] = db('comment_list')->where('membervideo_id', $row['membervideo_id'])->count();

        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Membervideo/add 02、添加
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            member_id 用户
     * @apiParam (输入参数：) {string}            aweme_id 视频id
     * @apiParam (输入参数：) {string}            comment_count 评论数量
     * @apiParam (输入参数：) {string}            digg_count 点赞数量
     * @apiParam (输入参数：) {string}            share_count 分享数量
     * @apiParam (输入参数：) {string}            play_count 播放数量
     * @apiParam (输入参数：) {string}            video_desc 视频描述
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {string}            video_pic_url 封面图
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
        $postField = 'member_id,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,video_pic_url,addtime';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = MembervideoService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Membervideo/update 03、修改
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            membervideo_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            member_id 用户
     * @apiParam (输入参数：) {string}            aweme_id 视频id
     * @apiParam (输入参数：) {string}            comment_count 评论数量
     * @apiParam (输入参数：) {string}            digg_count 点赞数量
     * @apiParam (输入参数：) {string}            share_count 分享数量
     * @apiParam (输入参数：) {string}            play_count 播放数量
     * @apiParam (输入参数：) {string}            video_desc 视频描述
     * @apiParam (输入参数：) {string}            video_url 视频地址
     * @apiParam (输入参数：) {string}            video_pic_url 封面图
     * @apiParam (输入参数：) {string}            addtime 添加时间
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
        $postField = 'membervideo_id,member_id,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,video_pic_url,addtime';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['membervideo_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['membervideo_id'] = $data['membervideo_id'];
        $res = MembervideoService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Membervideo/delete 04、删除
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            membervideo_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('membervideo_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['membervideo_id'] = explode(',', $idx);
        try {
            MembervideoModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Membervideo/view 05、查看详情
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            membervideo_id 主键ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */
    function view()
    {
        $data['membervideo_id'] = $this->request->post('membervideo_id', '', 'serach_in');
        $field = 'membervideo_id,member_id,aweme_id,comment_count,digg_count,share_count,play_count,video_desc,video_url,video_pic_url,addtime';
        $res = checkData(MembervideoModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    /**
     * @api {post} /Membervideo/collection_video 05、查看详情
     * @apiGroup Membervideo
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            membervideo_id 主键ID
     * @apiParam (失败返回参数：) {object}        array 返回结果集
     * @apiParam (失败返回参数：) {string}        array.status 返回错误码 201
     * @apiParam (失败返回参数：) {string}        array.msg 返回错误消息
     * @apiParam (成功返回参数：) {string}        array 返回结果集
     * @apiParam (成功返回参数：) {string}        array.status 返回错误码 200
     * @apiParam (成功返回参数：) {string}        array.data 返回数据详情
     * @apiSuccessExample {json} 01 成功示例
     * {"status":"200","data":""}
     * @apiErrorExample {json} 02 失败示例
     * {"status":"201","msg":"没有数据"}
     */

    //接受推送的视频/Membervideo/ReceiveUserVideoData
    function ReceiveUserVideoData()
    {
        if (!$this->request->isPost()) {
            throw new ValidateException('请求错误');
        }
        $data = $this->request->post();
        if (empty($data)) {
            throw new ValidateException('参数错误');
        }
        if ($data) {
            // $aweme_list = $info['data']['aweme_list']; //视频列表
            foreach ($data as $k => $v) {
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
                $insert['video_url'] = $v['play_addr_url']; //视频地址
                $insert['video_pic_url'] = $video['animated_cover']['url_list'][0];
                $insert['addtime'] = time();
                $where['member_id'] = $member_id;
                $where['aweme_id'] = $v['aweme_id'];
                $videoinfo = db('membervideo')->where($where)->value('aweme_id');
                if ($videoinfo) {
                    $res = MembervideoModel::where($where)->update($insert);
                    // return $this->ajaxReturn($this->successCode,'更新成功');
                    $msg = '更新成功';
                } else {
                    $res = MembervideoModel::insert($insert);
                    $msg = '添加成功';
                }
            }
            return $this->ajaxReturn($this->successCode, $msg);

        } else {
            throw new ValidateException('没有数据');
        }

    }

    function collection_video()
    {
        $member_id = $this->request->post('member_id', '', 'serach_in');
        if (empty($member_id)) {
            throw new ValidateException('参数错误');
        }
        $memberinfo = db('member')->where('member_id', $member_id)->find();//用户信息
        $token = db('tourist')->where('tourist_id', 2)->find();//游客的信息
        $url = 'http://47.245.30.4:9999/rest/index/getvideobymy';
        $data['token'] = $token['token'];
        $data['user_id'] = $memberinfo['uid'];
        $data['sec_user_id'] = $memberinfo['sec_uid'];
        $data['max_cursor'] = 0;
        $data['has_more'] = 0;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['aweme_list']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $insert['member_id'] = $memberinfo['member_id'];
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['comment_count'] = $v['statistics']['comment_count']; //评论数量
                    $insert['digg_count'] = $v['statistics']['digg_count'];
                    $insert['share_count'] = $v['statistics']['share_count']; //分享数量
                    $insert['play_count'] = $v['statistics']['play_count']; //播放数量
                    $insert['video_desc'] = $v['desc'];
                    $video = $v['video']; //视频数组
                    $insert['video_url'] = $video['play_addr']['url_list'][0]; //视频地址
                    $insert['video_pic_url'] = $video['animated_cover']['url_list'][0];
                    $insert['addtime'] = time();
                    $videoinfo = db('membervideo')->where('aweme_id', $v['aweme_id'])->value('aweme_id');
                    if ($videoinfo) {
                        $res = MembervideoModel::where('aweme_id', $v['aweme_id'])->update($insert);
                    } else {
                        $res = MembervideoModel::insert($insert);
                    }
                }
                if ($info['result']['data']['has_more'] == 1) {
                    $this->videolistint($token['token'], $memberinfo['uid'], $memberinfo['sec_uid'], $info['result']['data']['has_more'], $info['result']['data']['max_cursor']);
                }
                return $this->ajaxReturn($this->successCode, '操作成功');
            } else {
                throw new ValidateException('没有视频了');
            }
        } else {
            return $userinfo;
        }

    }

    function videolistint($token, $uid, $sec_uid, $has_more, $max_cursor)
    {
        set_time_limit(0);
        $url = 'http://47.245.30.4:9999/rest/index/getvideobymy';
        $data['token'] = $token;
        $data['user_id'] = $uid;
        $data['sec_user_id'] = $sec_uid;
        $data['max_cursor'] = $max_cursor;
        $data['has_more'] = $has_more;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['aweme_list']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $insert['member_id'] = $memberinfo['member_id'];
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['comment_count'] = $v['statistics']['comment_count']; //评论数量
                    $insert['digg_count'] = $v['statistics']['digg_count'];
                    $insert['share_count'] = $v['statistics']['share_count']; //分享数量
                    $insert['play_count'] = $v['statistics']['play_count']; //播放数量
                    $insert['video_desc'] = $v['desc'];
                    $video = $v['video']; //视频数组
                    $insert['video_url'] = $video['play_addr']['url_list'][0]; //视频地址
                    $insert['video_pic_url'] = $video['animated_cover']['url_list'][0];
                    $insert['addtime'] = time();
                    $videoinfo = db('membervideo')->where('aweme_id', $v['aweme_id'])->value('aweme_id');
                    if ($videoinfo) {
                        $res = MembervideoModel::where('aweme_id', $v['aweme_id'])->update($insert);
                    } else {
                        $res = MembervideoModel::insert($insert);
                    }
                }
                if ($info['result']['data']['has_more'] == 1) {
                    $this->videolistint($token, $uid, $sec_uid, $info['result']['data']['has_more'], $info['result']['data']['max_cursor']);
                }
                return $this->ajaxReturn($this->successCode, '操作成功');
            } else {
                throw new ValidateException('没有视频了');
            }
        } else {
            return $userinfo;
        }
    }


    function getvideo()
    {
        set_time_limit(0);
        $where['ifvideo'] = 1;
        // $where['member_id'] = 20;
        $video = db('membervideo')->where($where)->find();
        $videourl = config('my.host_url') . '/uploads/xiazai/video/' . $video['aweme_id'] . '.mp4';
        if ($video) {
            $arr = $this->dlfile($video['video_url'], app()->getRootPath() . '/public/uploads/xiazai/video/' . $video['aweme_id'] . '.mp4');
            $data['ifvideo'] = 0;
            $data['video_url'] = $videourl;
            $arr = MembervideoModel::where('aweme_id', $video['aweme_id'])->update($data);
            if ($arr) {
                echo '成功' . $videourl;
            }
        } else {
            echo '没有有任务了';
        }
    }


}

