<?php
/*
 module:		评论
 create_time:	2022-11-23 19:43:17
 author:		大怪兽
 contact:		
*/

namespace app\api\controller;

use app\api\model\Commentlist as CommentlistModel;
use app\api\service\CommentlistService;
use think\exception\ValidateException;
use think\facade\Validate;

class Commentlist extends Common
{


    /**
     * @api {post} /Commentlist/index 01、首页数据列表
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  首页数据列表
     * @apiParam (输入参数：) {int}            [limit] 每页数据条数（默认20）
     * @apiParam (输入参数：) {int}            [page] 当前页码
     * @apiParam (输入参数：) {string}        [reply_id] reply_id
     * @apiParam (输入参数：) {string}        [membervideo_id] membervideo_id
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
        $where['reply_id'] = $this->request->post('reply_id', '', 'serach_in');
        $where['membervideo_id'] = $this->request->post('membervideo_id', '', 'serach_in');
        $where['aweme_id'] = $this->request->post('aweme_id', '', 'serach_in');
        // $this->Commentview($where['membervideo_id']);
        $field = '*';
        $orderby = 'digg_count desc';
        $res = CommentlistService::indexList($this->apiFormatWhere($where), $field, $orderby, $limit, $page);
        foreach ($res['list'] as &$row) {

            $row['create_time'] = date("Y-m-d H:i:s", $row['create_time']);
        }
        return $this->ajaxReturn($this->successCode, '返回成功', htmlOutList($res));
    }

    /**
     * @api {post} /Commentlist/add 02、添加
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  添加
     * @apiParam (输入参数：) {string}            cid 评论ID
     * @apiParam (输入参数：) {string}            comment_language 评论语言
     * @apiParam (输入参数：) {string}            text 评论内容
     * @apiParam (输入参数：) {string}            create_time 评论时间
     * @apiParam (输入参数：) {string}            digg_count 评论点赞数
     * @apiParam (输入参数：) {string}            aweme_id 作品ID
     * @apiParam (输入参数：) {string}            reply_id reply_id
     * @apiParam (输入参数：) {string}            reply_comment_total 评论回复总数
     * @apiParam (输入参数：) {string}            uid 评论用户ID
     * @apiParam (输入参数：) {string}            sec_uid 评论用户sec_uid
     * @apiParam (输入参数：) {string}            avatar_medium 评论用户头像
     * @apiParam (输入参数：) {string}            nickname 评论人昵称
     * @apiParam (输入参数：) {string}            unique_id 评论人unique_id
     * @apiParam (输入参数：) {string}            aweme_count 评论人作品数量
     * @apiParam (输入参数：) {string}            following_count 评论人关注数量
     * @apiParam (输入参数：) {string}            follower_count 评论人粉丝数量
     * @apiParam (输入参数：) {string}            total_favorited 评论人点赞数量
     * @apiParam (输入参数：) {string}            signature 评论人签名
     * @apiParam (输入参数：) {string}            account_region 评论人国家
     * @apiParam (输入参数：) {string}            membervideo_id membervideo_id
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
        $postField = 'cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        $res = CommentlistService::add($data);
        return $this->ajaxReturn($this->successCode, '操作成功', $res);
    }

    /**
     * @api {post} /Commentlist/update 03、修改
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  修改
     * @apiParam (输入参数：) {string}            comment_list_id 主键ID (必填)
     * @apiParam (输入参数：) {string}            cid 评论ID
     * @apiParam (输入参数：) {string}            comment_language 评论语言
     * @apiParam (输入参数：) {string}            text 评论内容
     * @apiParam (输入参数：) {string}            create_time 评论时间
     * @apiParam (输入参数：) {string}            digg_count 评论点赞数
     * @apiParam (输入参数：) {string}            aweme_id 作品ID
     * @apiParam (输入参数：) {string}            reply_id reply_id
     * @apiParam (输入参数：) {string}            reply_comment_total 评论回复总数
     * @apiParam (输入参数：) {string}            uid 评论用户ID
     * @apiParam (输入参数：) {string}            sec_uid 评论用户sec_uid
     * @apiParam (输入参数：) {string}            avatar_medium 评论用户头像
     * @apiParam (输入参数：) {string}            nickname 评论人昵称
     * @apiParam (输入参数：) {string}            unique_id 评论人unique_id
     * @apiParam (输入参数：) {string}            aweme_count 评论人作品数量
     * @apiParam (输入参数：) {string}            following_count 评论人关注数量
     * @apiParam (输入参数：) {string}            follower_count 评论人粉丝数量
     * @apiParam (输入参数：) {string}            total_favorited 评论人点赞数量
     * @apiParam (输入参数：) {string}            signature 评论人签名
     * @apiParam (输入参数：) {string}            account_region 评论人国家
     * @apiParam (输入参数：) {string}            membervideo_id membervideo_id
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
        $postField = 'comment_list_id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $data = $this->request->only(explode(',', $postField), 'post', null);
        if (empty($data['comment_list_id'])) {
            throw new ValidateException('参数错误');
        }
        $where['comment_list_id'] = $data['comment_list_id'];
        $res = CommentlistService::update($where, $data);
        return $this->ajaxReturn($this->successCode, '操作成功');
    }

    /**
     * @api {post} /Commentlist/delete 04、删除
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  删除
     * @apiParam (输入参数：) {string}            comment_list_ids 主键id 注意后面跟了s 多数据删除
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
        $idx = $this->request->post('comment_list_ids', '', 'serach_in');
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $data['comment_list_id'] = explode(',', $idx);
        try {
            CommentlistModel::destroy($data, true);
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $this->ajaxReturn($this->successCode, '操作成功');
    }


    //二级评论/Commentlist/twoComment
    function twoComment()
    {
        set_time_limit(0);
        $comment_list_id = $this->request->post('comment_list_id', '', 'serach_in');
        if (empty($comment_list_id)) {
            throw new ValidateException('参数错误');
        }
        $arr = CommentlistModel::where('comment_list_id', $comment_list_id)->field('cid,aweme_id,membervideo_id')->find();
        $token = db('tourist')->where('tourist_id', 2)->find();//游客的信息
        $url = 'http://47.245.30.4:9999/rest/Aweme/commentList';
        $data['item_id'] = $arr['aweme_id'];
        $data['token'] = $token['token'];
        $data['comment_id'] = $arr['cid'];
        $data['cursor'] = 0;
        $data['type'] = 1;
        $data['has_more'] = 0;
// 		print_r($data);die;
        $userinfo = $this->doHttpPost($url, $data);
// 		var_dump($userinfo);die;
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['comments']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $insert['cid'] = $v['cid'];
                    $insert['comment_language'] = $v['comment_language'];
                    $insert['text'] = $v['text'];
                    $insert['create_time'] = $v['create_time'];
                    $insert['digg_count'] = $v['digg_count'];
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['reply_id'] = $v['reply_id'];
                    $insert['reply_comment_total'] = $v['reply_comment_total'];
                    $user_info = $v['user'];
                    $insert['uid'] = $user_info['uid'];
                    $insert['sec_uid'] = $user_info['sec_uid'];
                    $insert['nickname'] = $user_info['nickname'];
                    $insert['signature'] = $user_info['signature'];
                    $insert['account_region'] = $user_info['account_region'];
                    $insert['unique_id'] = $user_info['unique_id'];
                    $insert['aweme_count'] = $user_info['aweme_count'];
                    $insert['avatar_medium'] = $user_info['avatar_medium']['url_list'][0];
                    $insert['following_count'] = $user_info['following_count'];
                    $insert['follower_count'] = $user_info['follower_count'];
                    $insert['total_favorited'] = $user_info['total_favorited'];
                    $insert['membervideo_id'] = 0;
                    $cid = db('comment_list')->where('cid', $v['cid'])->value('cid');
                    if ($cid) {
                        $res = db('comment_list')->where('cid', $v['cid'])->update($insert);
                    } else {
                        $res = db('comment_list')->insert($insert);
                    }
                }
                $rearr = CommentlistModel::where('reply_id', $arr['cid'])->select()->toArray();
                return $this->ajaxReturn($this->successCode, '二级评论拉取成功', $rearr);


            } else {
                throw new ValidateException('没有评论数据');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }

    /**
     * @api {post} /Commentlist/view 05、查看详情
     * @apiGroup Commentlist
     * @apiVersion 1.0.0
     * @apiDescription  查看详情
     * @apiParam (输入参数：) {string}            comment_list_id 主键ID
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
        $data['comment_list_id'] = $this->request->post('comment_list_id', '', 'serach_in');
        $field = 'comment_list_id,cid,comment_language,text,create_time,digg_count,aweme_id,reply_id,reply_comment_total,uid,sec_uid,avatar_medium,nickname,unique_id,aweme_count,following_count,follower_count,total_favorited,signature,account_region,membervideo_id';
        $res = checkData(CommentlistModel::field($field)->where($data)->find());
        return $this->ajaxReturn($this->successCode, '返回成功', $res);
    }

    ///Commentlist/Commentview
    function Commentview($membervideo_id)
    {
        set_time_limit(0);
        $idx = $membervideo_id;
        if (empty($idx)) {
            throw new ValidateException('参数错误');
        }
        $member = db('membervideo')->where('membervideo_id', $idx)->find();
        $token = db('tourist')->where('tourist_id', 2)->find();//游客的信息
        $url = 'http://47.245.30.4:9999/rest/Aweme/commentList';
        $data['token'] = $token['token'];
        $data['aweme_id'] = $member['aweme_id'];
        $data['type'] = 0;
        $data['cursor'] = 0;
        $data['has_more'] = 0;
        $userinfo = $this->doHttpPost($url, $data);
        $info = json_decode($userinfo, true);
        if ($info['result'] == 0) {
            $aweme_list = $info['data']['comments']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $insert['cid'] = $v['cid'];
                    $insert['comment_language'] = $v['comment_language'];
                    $insert['text'] = $v['text'];
                    $insert['create_time'] = $v['create_time'];
                    $insert['digg_count'] = $v['digg_count'];
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['reply_id'] = $v['reply_id'];
                    $insert['reply_comment_total'] = $v['reply_comment_total'];
                    $user_info = $v['user'];
                    $insert['uid'] = $user_info['uid'];
                    $insert['sec_uid'] = $user_info['sec_uid'];
                    $insert['nickname'] = $user_info['nickname'];
                    $insert['signature'] = $user_info['signature'];
                    $insert['account_region'] = $user_info['account_region'];
                    $insert['unique_id'] = $user_info['unique_id'];
                    $insert['aweme_count'] = $user_info['aweme_count'];
                    $insert['avatar_medium'] = $user_info['avatar_medium']['url_list'][0];
                    $insert['following_count'] = $user_info['following_count'];
                    $insert['follower_count'] = $user_info['follower_count'];
                    $insert['total_favorited'] = $user_info['total_favorited'];
                    $insert['membervideo_id'] = $idx;
                    $cid = db('comment_list')->where('cid', $v['cid'])->value('cid');
                    if ($cid) {
                        $res = db('comment_list')->where('cid', $v['cid'])->update($insert);
                    } else {
                        $res = db('comment_list')->insert($insert);
                    }
                }
                if ($info['data']['has_more'] == 1) {
                    // var_dump($token['token']);var_dump($data['aweme_id']);var_dump($info['data']['cursor']);var_dump($info['data']['has_more']);var_dump($idx);die;
                    return $this->videolistint($token['token'], $data['aweme_id'], $info['data']['cursor'], $info['data']['has_more'], $idx);
                } else {
                    return $this->ajaxReturn($this->successCode, '操作成功1');
                }

            } else {
                throw new ValidateException('没有评论数据');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }


    function videolistint($token, $aweme_id, $cursor, $has_more, $idx)
    {
        // echo '2222';die;
        set_time_limit(0);
        $url = 'http://47.245.30.4:9999/rest/Aweme/commentList';
        $data['token'] = $token;
        $data['aweme_id'] = $aweme_id;
        $data['type'] = 0;
        $data['cursor'] = $cursor;
        $data['has_more'] = $has_more;
// 		print_r($data);die;
        $userinfo = $this->doHttpPost($url, $data);
// 		var_dump($userinfo);die;
        $info = json_decode($userinfo, true);

        if ($info['result'] == 0) {
            $aweme_list = $info['data']['comments']; //视频列表
            if (!empty($aweme_list)) {
                foreach ($aweme_list as $k => $v) {
                    $insert['cid'] = $v['cid'];
                    $insert['comment_language'] = $v['comment_language'];
                    $insert['text'] = $v['text'];
                    $insert['create_time'] = $v['create_time'];
                    $insert['digg_count'] = $v['digg_count'];
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['reply_id'] = $v['reply_id'];
                    $insert['reply_comment_total'] = $v['reply_comment_total'];
                    $user_info = $v['user'];
                    $insert['uid'] = $user_info['uid'];
                    $insert['sec_uid'] = $user_info['sec_uid'];
                    $insert['nickname'] = $user_info['nickname'];
                    $insert['signature'] = $user_info['signature'];
                    $insert['account_region'] = $user_info['account_region'];
                    $insert['unique_id'] = $user_info['unique_id'];
                    $insert['aweme_count'] = $user_info['aweme_count'];
                    $insert['avatar_medium'] = $user_info['avatar_medium']['url_list'][0];
                    $insert['following_count'] = $user_info['following_count'];
                    $insert['follower_count'] = $user_info['follower_count'];
                    $insert['total_favorited'] = $user_info['total_favorited'];
                    $insert['membervideo_id'] = $idx;
                    $cid = db('comment_list')->where('cid', $v['cid'])->value('cid');
                    if ($cid) {
                        $res = db('comment_list')->where('cid', $v['cid'])->update($insert);
                    } else {
                        $res = db('comment_list')->insert($insert);
                    }
                }
                if ($info['data']['has_more'] == 1) {
                    $this->videolistint($token, $data['aweme_id'], $info['result']['data']['cursor'], $info['result']['data']['has_more']);
                }
                return $this->ajaxReturn($this->successCode, '操作成功22');

            } else {
                throw new ValidateException('没有评论数据');
            }
        } else {
            throw new ValidateException($info['message']);
        }
    }


}

