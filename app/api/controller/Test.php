<?php

namespace app\api\controller;


use think\exception\ValidateException;

class Test extends Common
{
    //根据关键词搜索
    public function getRestByKeys()
    {
        $keyword = trim(request()->post("keyword"));
        if (empty($keyword)) throw new ValidateException('请输入用户关键词');
        preg_match('%@(.*)\?%', $keyword, $keyword1);
        if (!$keyword1) {
            preg_match('%@(.*)%', $keyword, $keyword2);
            if (!$keyword2) {
                preg_match('%(.*)\?%', $keyword, $keyword3);
                if (!$keyword3) {
                    preg_match('%(.*)%', $keyword, $keyword4);//这个其实没什么用，等效于 $keyword==$keyword
                    if (!$keyword4) {
                        throw new ValidateException('不是个有效的博主主页链接');
                    } else {
                        $keyword = $keyword4[1];
                    }
                } else {
                    $keyword = $keyword3[1];
                }
            } else {
                $keyword = $keyword2[1];
            }
        } else {
            $keyword = $keyword1[1];
        }

        //取接口授权
        $access = getAccessInfo();
        //处理token
        $token = doToken('', 2); //游客随机token
        //取http代理
        $proxy = getHttpProxy($token['user']['uid']);

        $params = [
            'keyword' => $keyword,
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];

        $url = config('my.main_link') . 'api/v1/ttapi/search_stream?impl=1&cached=1';
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access, "");
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
        if ($respone_arr['response_status'] == 0) {
            /*//TT接口请求
            $tt_respone_json = $this->getResToTT($respone_arr, "");*/
            $temp_array = explode(PHP_EOL, $respone_arr['response']['content']);
//            echo "<pre>";
//            var_dump($temp_array);die();
            $json_array = [];
            foreach ($temp_array as $k => $v) {
                if (strlen($v) > 10) $json_array[] = json_decode($v, true);
            }
            $user_insert_list = [];
            $json_array_return = [];
            foreach ($json_array as $json_v) {
                // $json_v_arr = json_decode($json_v, true);
                $json_v_arr = $json_v;
                $json_array_return[] = $json_v_arr;
                if ($json_v_arr['status_code'] == 0) {
                    $data_v = $json_v_arr['data'];
                    foreach ($data_v as $u_v) {
                        if ($u_v['type'] == 4) { //用户
                            $user_list = $u_v['user_list'];
                            foreach ($user_list as $uu_v) {
                                $uid = $uu_v['user_info']['uid'];
                                $nickname = $uu_v['user_info']['nickname'];
                                $avatar_pic = $uu_v['user_info']['avatar_medium']['url_list'][0];
                                $unique_id = $uu_v['user_info']['unique_id'];
                                $sec_uid = $uu_v['user_info']['sec_uid'];
                                $aweme_count = $uu_v['user_info']['aweme_count'];
                                $following_count = $uu_v['user_info']['following_count'];
                                $follower_count = $uu_v['user_info']['follower_count'];
                                $total_favorited = $uu_v['user_info']['total_favorited'];

                                $user_insert_list[] = [
                                    'uid' => $uid,
                                    'nickname' => $nickname,
                                    'avatar_pic' => $avatar_pic,
                                    'unique_id' => $unique_id,
                                    'sec_uid' => $sec_uid,
                                    'aweme_count' => $aweme_count,
                                    'following_count' => $following_count,
                                    'follower_count' => $follower_count,
                                    'total_favorited' => $total_favorited,
                                    // 'addtime' => time()
                                ];
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
            if (!empty($user_insert_list)) {
                return $this->ajaxReturn($this->successCode, "用户列表", $user_insert_list);
                //return json_encode($user_insert_list);
                //db("member")->insertAll($user_insert_list);
            } else {
                throw new ValidateException("没有搜索到用户列表");
            }
            /*if (empty($json_array_return)) {
                throw new ValidateException(, '没有取到数据');
            } else {
                return $this->ajaxReturn($this->successCode, '正常返回', $json_array_return);
            }*/
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }

    }

    //拉取私信接口
    public function getByUser($user_id = null)
    {
        set_time_limit(0);
        try {
            //取接口授权
            $access = getAccessInfo();

            $user_id = $user_id ?: trim(request()->post("user_id"));
            //取登录后的token
            $user_info = db("member")->field('token')->where(['uid' => $user_id, 'status' => 1])->find();
            if (empty($user_info)) throw new ValidateException('没有此用户');
            $token = doToken($user_info['token']);

            //取http代理
            $proxy = getHttpProxy($user_id);

            $cursor = request()->post("cursor/n") ? request()->post("cursor/n") : 0; //拉取私信索引，以0 开始，之后使用下一次接口返回的 nextCursor
            $limit = request()->post("limit/n") ? request()->post("limit/n") : 20;
            if ($limit > 20) $limit = 20;
            $data = [
                'proxy' => $proxy,
                'token' => $token,
            ];
            $url = config('my.main_link') . 'api/v1/chat/get_by_user?impl=1&cached=1&&cursor=' . $cursor . '&limit=' . $limit . '&cached=false&proxy=' . $proxy;
            //请求接口
            $respone = doHttpPosts($url, json_encode($data), $access, "");
            return $respone;
            $respone_arr = json_decode($respone, true);

            if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
            $messages_list = $respone_arr['body']['messagesPerUserBody']['messages'];
            if (empty($messages_list)) throw new ValidateException('没有私信');
            if ($respone_arr['statusCode'] == 0) {
                $insert_list = [];
                //数据需要入库
                foreach ($messages_list as $v) {
                    $insert_list[] = [
                        'user_id' => $user_id,
                        'nextCursor' => $respone_arr['body']['messagesPerUserBody']['nextCursor'],
                        'conversationId' => $v['conversationId'],
                        'conversationType' => $v['conversationType'],
                        'serverMessageId' => $v['serverMessageId'],
                        'indexInConversation' => $v['indexInConversation'],
                        'conversationShortId' => $v['conversationShortId'],
                        'messageType' => $v['messageType'],
                        'sender' => $v['sender'],
                        'content' => json_encode($v['content']),
                        'ext' => json_encode($v['ext']),
                        'createTime' => $v['createTime'],
                        'version' => $v['version'],
                        'status' => $v['status'],
                        'orderInConversation' => $v['orderInConversation'],
                        'secSender' => $v['secSender'],
                        'scene' => $v['scene'],
                    ];
                }
                M('notice_my_user')->addAll($insert_list, [], true);
                return $this->ajaxReturn($this->successCode, '获取成功', $respone_arr);
            } else {
                throw new ValidateException($respone_arr['errorDesc']);
            }
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    // 查看个人信息
    public function getProfile($user_id = null)
    {
        //取接口授权
        $access = getAccessInfo();
        $user_id = $user_id ?: trim(request()->post("user_id"));

        //处理token
        $user_info = db("member")->field('token')->where(['uid' => $user_id, 'status' => 1])->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = doToken($user_info['token']);
        //取http代理
        $proxy = getHttpProxy($token['user']['uid']);

        $data = [
            'proxy' => $proxy,
            'token' => $token,
        ];

        $url = config('my.main_link') . 'api/v1/ttapi/profile_self?impl=1&cached=1';
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access, "http://192.168.4.52:8888");
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
        if ($respone_arr['response']['status_code'] == 200) {
            //return $respone_arr['response']['content'];
            // viewer_entrance_info 访客数量
            var_dump($respone_arr['response']['content']);
            die;
            $tt_respone_arr = json_decode($respone_arr['response']['content'], true);

            if ($tt_respone_arr['status_code'] != 0) throw new ValidateException($tt_respone_arr['status_msg']);
            $user = $tt_respone_arr['user'];
            $updata = [];
            $updata['avatar_thumb'] = $user['avatar_medium']['url_list'][0];
            $updata['follower_status'] = $user['follower_count'];
            $updata['following_count'] = $user['following_count'];
            $updata['total_favorited'] = $user['total_favorited'];
            $updata['nickname'] = $user['nickname'];
            $updata['unique_id'] = $user['unique_id'];
            $updata['signature'] = $user['signature'];
            $updata['member_type'] = $user['account_type'];
            $updata['aweme_count'] = $user['aweme_count'];
            $updata['unread_viewer_count'] = $user['viewer_entrance_info']['unread_viewer_count'];
            $updata['ifup'] = 0;
            $updata['ifpic'] = 1;
            $updata['updata_time'] = time();
            //return json_encode($updata);
            $res = db('member')->where('uid', $user_id)->update($updata);
            return $this->ajaxReturn($this->successCode, "更新成功");
        } else {
            throw new ValidateException($respone_arr['response']['error_desc']);
        }
    }

    //作品评论、评论一级、二级回复
    public function cpublish()
    {

        set_time_limit(0);

        //取接口授权
        $access = getAccessInfo();

        $user_id = trim(request()->post("user_id"));
        //取登录后的token
        $user_info = db("member")->field('token')->where(['uid' => $user_id, 'status' => 1])->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = doToken($user_info['token']);

        //取http代理
        $proxy = getHttpProxy($user_id);

        $type = request()->post("type/n");
        if (!in_array($type, [0, 1, 2])) throw new ValidateException('参数非法');

        $text = request()->post("text");
        if ($text == '') throw new ValidateException('评论不能为空');

        $aweme_id = request()->post("aweme_id");
        if ($aweme_id == '') throw new ValidateException('作品ID不能为空');
        $params = [
            'aweme_id' => $aweme_id,
            'text' => $text,
            'action_type' => 0,
            'channel_id' => 0,
            'comment_source' => 0,
            'skip_rethink' => 0,
        ];

        if ($type == 1) { //一级评论回复
            $reply_id = request()->post("reply_id");
            if ($reply_id == '') throw new ValidateException('一级评论ID不能为空');

            $params['reply_id'] = $reply_id;
        }
        if ($type == 2) { //二级评论回复
            $reply_id = request()->post("reply_id");
            if ($reply_id == '') throw new ValidateException('一级评论ID不能为空');

            $reply_to_reply_id = request()->post("reply_to_reply_id");
            if ($reply_to_reply_id == '') throw new ValidateException('二级评论ID不能为空');

            $params['reply_id'] = $reply_id;
            $params['reply_to_reply_id'] = $reply_to_reply_id;
        }

        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/comment/publish?impl=1&cached=1';
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access, "");
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');

        if ($respone_arr['response_status'] == 0) {
            //TT接口请求
            /*$tt_respone_json = $this->getResToTT($respone_arr, $proxy);
            $tt_respone_arr = json_decode($tt_respone_json, true);*/
            $tt_respone_arr = json_decode($respone_arr['response']['content'], true);
            if (!isset($tt_respone_arr) || !is_array($tt_respone_arr)) throw new ValidateException('TT接口异常');
            if ($tt_respone_arr['status_code'] == 0) {
                $comment = $tt_respone_arr['comment'];
                $membervideo_id = db("membervideo")->where("aweme_id", $comment['aweme_id'])->value("membervideo_id");
                $iid = db("comment_list")->insertGetId([
                    "cid" => $comment['cid'],
                    "comment_language" => $comment['user']['language'],
                    "text" => $comment['text'],
                    "create_time" => $tt_respone_arr["comment"]['create_time'],
                    "digg_count" => $comment['digg_count'],
                    "aweme_id" => $comment['aweme_id'],
                    "reply_id" => $comment['reply_id'],
                    "reply_comment_total" => 0,
                    "uid" => $comment['user']['uid'],
                    "sec_uid" => $comment['user']['sec_uid'],
                    "avatar_medium" => $comment['user']['avatar_medium']['url_list'][0],
                    "nickname" => $comment['user']["nickname"],
                    "unique_id" => $comment['user']['unique_id'],
                    "aweme_count" => $comment['user']['aweme_count'],
                    "following_count" => $comment['user']['following_count'],
                    "follower_count" => $comment['user']['follower_count'],
                    "total_favorited" => $comment['user']['total_favorited'],
                    "signature" => $comment['user']['signature'],
                    "account_region" => $this->transCountryCode($comment['user']['region']),
                    "membervideo_id" => $membervideo_id,
                    "ifpic" => 1
                ]);
                return $this->ajaxReturn($this->successCode, '评论成功', array_merge($tt_respone_arr, ['iid' => $iid]));
            } else {
                throw new ValidateException($tt_respone_arr['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }
    }

    //上传视频
    public function upload()
    {
        set_time_limit(0);
        $file_name = request()->post("file");
        if (strstr($file_name, 'http')) {
            dlfile($file_name, app()->getRootPath() . "/public/uploads/xiazai/video/" . basename($file_name));
            $file_name = "uploads/xiazai/video/" . basename($file_name);
            // returnJsonp(-1, "远程文件不可使用");
        }

        //取接口授权
        $access = getAccessInfo();

        $text = request()->post('text'); //视频描述
        $user_id = request()->post('user_id');

        //取登录后的token
        $user_info = db('member')->field('token')->where(['uid' => $user_id, 'status' => 1])->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = doToken($user_info['token']);

        //取http代理
        $proxy = getHttpProxy($user_id);
        $data = [
            'proxy' => $proxy,
            'token' => $token,
        ];
        $url = config('my.main_link') . 'api/v1/aweme/create/prepare';
        //请求接口-准备上传
        $respone = doHttpPosts($url, json_encode($data), $access, "");
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr) || empty($respone_arr['creation_id'])) throw new ValidateException('接口异常');
        $creation_id = $respone_arr['creation_id'];

        //片段上传
        $respone_arr_2_temp = [];
        $i = 0;                               //分割的块编号
        $fp = fopen($file_name, "rb");     //要分割的文件
        $log_file = app()->getRootPath() . "/public/uploads/videotemp/" . $creation_id . ".txt";
        $file = fopen($log_file, "a");     //记录分割的信息的文本文件
        while (!feof($fp)) {
            fwrite($file, $file_name . ".{$i}\r\n");
            $pd = fread($fp, 524288);
            $url_2 = config('my.main_link') . 'api/v1/aweme/upload/' . $creation_id . "?impl=1&part_num=$i&proxy=$proxy";
            //请求接口
            $respone_2 = doHttpPostsVideo($url_2, $pd, $access, "");
            $respone_arr_2 = json_decode($respone_2, true);
            $i++;
            $respone_arr_2_temp[] = $respone_arr_2;
        }
        fclose($fp);
        fclose($file);
        unlink($log_file);
        if (count($respone_arr_2_temp) != $i) throw new ValidateException('视频片段上传到接口不全或出错，请重新上传');
        foreach ($respone_arr_2_temp as $k => $v) {
            if ($v['response_status'] != 0 && $v['response_msg'] != 'OK') throw new ValidateException('视频第' . ($k + 1) . '片段上传到接口失败，请重新上传');
        }
        /*//开始上传TT
        $tt_respone_arr_temp = [];
        foreach ($respone_arr_2_temp as $k1 => $v1) {
            //TT接口请求
            $tt_respone_json = $this->getResToTT($v1, "");
            $tt_respone_arr = json_decode($tt_respone_json, true);
            if (!isset($tt_respone_arr) || !is_array($tt_respone_arr)) throw new ValidateException(, 'TT上传视频第' . ($k1 + 1) . '接口异常，请重新上传');
            $tt_respone_arr_temp[] = $tt_respone_arr;
        }
        foreach ($tt_respone_arr_temp as $k2 => $v2) {
            if ($v2['code'] != 2000 && trim($v2['message']) != 'Success') throw new ValidateException(, 'TT上传视频第' . ($k2 + 1) . '片段上传到接口失败，请重新上传');
        }*/
        //privacy=public&is_private=false&
        //合并视频
        $url_params = 'impl=1&text=' . urlencode($text) . '&proxy=' . urlencode($proxy);
        $url_3 = config('my.main_link') . 'api/v1/aweme/upload/' . $creation_id . '/finish/?' . $url_params;
        $finish_res_json = curlGet($url_3, $access, "");
        $finish_res_arr = json_decode($finish_res_json, true);

        if (!isset($finish_res_arr) || !is_array($finish_res_arr)) throw new ValidateException('接口视频合成失败，请重新上传');
        if ($finish_res_arr['response_status'] == 0 && $finish_res_arr['response_status'] == 'OK') {
            /*//TT接口请求
            $tt_finish_respone_json = $this->getResToTT($finish_res_arr, $proxy);
            $tt_finish_respone_json_arr = json_decode($tt_finish_respone_json, true);*/
            $tt_finish_respone_json_arr = json_decode($finish_res_arr['response']['content'], true);

            if (!isset($tt_finish_respone_json_arr) || !is_array($tt_finish_respone_json_arr)) throw new ValidateException('TT视频合成失败，请重新上传');

            if ($tt_finish_respone_json_arr['status_code'] == 0 && !empty($tt_finish_respone_json_arr['aweme'])) {
                $member_id = db('member')->where('uid', $user_id)->value('member_id');
                $insert['member_id'] = $member_id;
                $insert['aweme_id'] = $tt_finish_respone_json_arr['aweme']['aweme_id'];
                $insert['comment_count'] = 0; //评论数量
                $insert['digg_count'] = 0;
                $insert['share_count'] = 0; //分享数量
                $insert['play_count'] = 0; //播放数量
                $insert['video_desc'] = $tt_finish_respone_json_arr['aweme']['desc'];
                $insert['video_url'] = config("my.host_url") . "/$file_name"; //视频地址
                $insert['video_pic_url'] = $tt_finish_respone_json_arr['aweme']['video']['cover']['url_list'][0];
                $insert['addtime'] = time();
                $insert['ifvideo'] = 0;
                $res = db('membervideo')->insertGetId($insert);

                $tt_finish_respone_json_arr['video_url'] = config("my.host_url") . "/$file_name";
                return $this->ajaxReturn($this->successCode, '上传成功', array_merge($tt_finish_respone_json_arr, ['iid' => $res]));
            } else {
                throw new ValidateException($tt_finish_respone_json_arr['status_msg']);
            }
        } else {
            throw new ValidateException($finish_res_arr['response_msg']);
        }
    }

    //下载评论的头像
    function getuserimg()
    {
        set_time_limit(0);
        $where['ifpic'] = 1;
        // $where['member_id'] = 20;
        $video = db('comment_list')->where($where)->find();
        if ($video) {
            $pic = config('my.host_url') . '/uploads/xiazai/commentlist/' . $video['uid'] . '.png';
            dlfile($video['avatar_medium'], '/www/wwwroot/192.168.4.30/admin.com/public/uploads/xiazai/commentlist/' . $video['uid'] . '.png');
            $data['ifpic'] = 0;
            $data['avatar_medium'] = $pic;
            $arr = db('comment_list')->where('comment_list_id', $video['comment_list_id'])->update($data);
            if ($arr) {
                echo '成功' . $pic;
            }
        } else {
            echo '没有有任务了';
        }
    }

    //博主的视频评论列表
    public function commentList()
    {
        set_time_limit(0);

        //取接口授权
        $access = getAccessInfo();
        //处理token
        $token = doToken('', 2); //游客随机token
        //取http代理
        $proxy = getHttpProxy($token['user']['uid']);

        $type = request()->post("type/n");
        if (!in_array($type, [0, 1])) throw new ValidateException('参数非法');
        $count = 50;

        if ($type == 0) { //一级评论
            $aweme_id = trim(request()->post("aweme_id")); //作品ID
            $cursor = request()->post("cursor/n") ? request()->post("cursor/n") : 0;
            $channel_id = 0;

            if (empty($aweme_id)) throw new ValidateException('作品ID未传');

            $params = [
                'aweme_id' => $aweme_id,
                'count' => $count,
                'cursor' => $cursor,
                'channel_id' => $channel_id,
            ];
            $data = [
                'proxy' => $proxy,
                'token' => $token,
                'params' => $params
            ];
            $url = config('my.main_link') . 'api/v1/ttapi/comment_list?impl=1&cached=1';
        } else { //二级评论
            $item_id = trim(request()->post("item_id")); //作品ID
            $cursor = request()->post("cursor/n") ? request()->post("cursor/n") : 0;
            $comment_id = trim(request()->post("comment_id")); //评论ID
            $count = 50;
            if (empty($item_id)) throw new ValidateException('作品ID未传 或 评论ID未传');

            $params = [
                'item_id' => $item_id,
                'count' => $count,
                'cursor' => $cursor,
                'comment_id' => $comment_id,
            ];
            $data = [
                'proxy' => $proxy,
                'token' => $token,
                'params' => $params
            ];
            $url = config('my.main_link') . 'api/v1/ttapi/comment_list/reply?impl=1&cached=1';
        }
        $proxy = "";
        //请求接口
        $respone = doHttpPosts($url, json_encode($data), $access, $proxy);
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
        if ($respone_arr['response_status'] == 0) {
            //TT接口请求
            /*$tt_respone_json = $this->getResToTT($respone_arr,$proxy);
            $tt_respone_arr = json_decode($tt_respone_json,true);*/
            $tt_respone_arr = json_decode($respone_arr['response']['content'], true);
            if (!isset($tt_respone_arr) || !is_array($tt_respone_arr)) throw new ValidateException('TT接口异常');
            if ($tt_respone_arr['status_code'] == 0) {
                $total = $tt_respone_arr['total'];
                if ($total <= 0) throw new ValidateException('没有评论数据');

                $has_more = $tt_respone_arr['has_more']; //是否有更多
                $cursor = $tt_respone_arr['cursor'];
                $comments = $tt_respone_arr['comments']; //评论列表
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

                    /*$insert_u = [
                        'uid' => $uid,
                        'sec_uid' => $sec_uid,
                        'nickname' => $nickname,
                        'avatar_pic' => $avatar_medium,
                        'unique_id' => $unique_id,
                        'aweme_count' => $aweme_count,
                        'following_count' => $$following_count,
                        'follower_count' => $follower_count,
                        'total_favorited' => $total_favorited,
                        'region' => $account_region,
                        'signature' => $signature,
                        'addtime' => time()
                    ];
                    db('member')->update($insert_u);*/
                    if (db("comment_list")->where("cid", $cid)->count()) {
                        db('comment_list')->where("cid", $cid)->update($insert_list);
                    } else {
                        db('comment_list')->insert($insert_list);
                    }
                }
                // db('member')->update($insert_u);
                // db('comment_list')->insertAll($insert_list);
                $tt_respone_arr['has_more'] = $has_more ? 1 : 0;
                $tt_respone_arr['cursor'] = $cursor;
                return $this->ajaxReturn($this->successCode, '获取成功', $tt_respone_arr);
            } else {
                throw new ValidateException($tt_respone_arr['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }
    }

    //上传图像
    public function uploadimg($user_id = null, $image = null)
    {
        //取接口授权
        $access = getAccessInfo();
        //处理token
        if (!$user_id) $user_id = request()->post("user_id");

        $info = db('member')->where(['uid' => $user_id, 'status' => 1])->find();
        if (empty($info['token'])) throw new ValidateException("无此用户token");
        $token = doToken($info['token']); //登录后的token

        //取http代理
        $proxy = getHttpProxy($user_id);

        if (!$image) $image = request()->post("image"); //图标base64编码
        if (empty($image) || trim($image) == '') throw new ValidateException('图片未传');
        $image = base64_encode(file_get_contents($image));
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'image' => $image
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/upload_image?impl=1&cached=1';
        //接口请求
        $proxy = "";
        $respone = doHttpPosts($url, json_encode($data), $access, $proxy);
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
        if ($respone_arr['response_status'] == 0) {
            //TT接口请求
            /*$tt_respone_json = $this->getResToTT($respone_arr, $proxy);
            $tt_respone_arr = json_decode($tt_respone_json, true);*/
            $tt_respone_arr = $respone_arr['response'];
            $tt_respone_content = json_decode($respone_arr['response']['content'], true);
            if (!isset($tt_respone_arr) || $tt_respone_arr['status_code'] != 200) throw new ValidateException('TT接口异常:' . $tt_respone_arr['error_desc']);
            if ($tt_respone_content['status_code'] == 0) {
                if (empty($tt_respone_content['data'])) {
                    throw new ValidateException('上传失败');
                } else {
                    return $tt_respone_content['data'];
                    return $this->ajaxReturn($this->successCode, '上传成功', $tt_respone_content['data']);
                }
            } else {
                throw new ValidateException($tt_respone_content['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }

    }

    //修改用户昵称、头像、签名
    public function edituserinfo()
    {

        //取接口授权
        $access = getAccessInfo();
        //处理token
        $user_id = request()->post("user_id");
        $info = db('member')->where(['uid' => $user_id, 'status' => 1])->find();
        if (empty($info['token'])) throw new ValidateException('无此用户token');
        $token = doToken($info['token']); //登录后的token

        //取http代理
        $proxy = getHttpProxy($user_id);

        $type = request()->post("type/n");
        $save = [];
        /*$save['grouping_id'] = request()->post("grouping_id");
        $save['typecontrol_id'] = request()->post("typecontrol_id");
        if ($save['typecontrol_id']) {
            db("member")->where("uid", $user_id)->update($save);
        }*/
        if (!in_array($type, [1, 2, 3, 4])) throw new ValidateException('参数非法');
        if ($type == 1) { //修改昵称
            $nickname = trim(request()->post("nickname"));
            if (empty($nickname) || empty($token)) throw new ValidateException('参数未传');
            $save['nickname'] = $nickname;
            $params = [
                'page_from' => 0,
                'nickname' => $nickname
            ];
        }
        if ($type == 2) { //修改签名
            $signature = trim(request()->post("signature"));
            if (empty($signature) || empty($token)) throw new ValidateException('参数未传');
            $save['signature'] = $signature;
            $params = [
                'page_from' => 0,
                'signature' => $signature
            ];
        }
        if ($type == 3) { //修改头像
            $avatar_uri = trim(request()->post("avatar_uri"));
            if (empty($avatar_uri) || empty($token)) throw new ValidateException('参数未传');
            $upload = $this->uploadimg($user_id, $avatar_uri);
            $avatar_uri = $upload['uri'];
            $save['avatar_thumb'] = $upload['url_list'][0];
            $save['ifpic'] = 1;
            $params = [
                'avatar_uri' => $avatar_uri
            ];
        }

        $url = config('my.main_link') . 'api/v1/ttapi/commit_user?impl=1&cached=1';
        if ($type == 4) { //修改unique_id
            $unique_id = trim(request()->post("unique_id"));
            if (empty($unique_id) || empty($token)) throw new ValidateException('参数未传');
            $save['unique_id'] = $unique_id;
            $params = [
                'unique_id' => $unique_id
            ];
            $url = config('my.main_link') . 'api/v1/ttapi/unique_id_check?impl=1&cached=1';
        }
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $proxy = "";
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access, $proxy);
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');

        if ($respone_arr['response_status'] == 0) {
            /*//TT接口请求
            $tt_respone_json = $this->getResToTT($respone_arr, "");
            echo "<pre>";
            var_dump($tt_respone_json);
            die();*/
            if (!isset($respone_arr['response'])) throw new ValidateException('response为空');
            $tt_respone_arr = $respone_arr['response'];
            $tt_respone_content = json_decode($respone_arr['response']['content'], true);

            if ($tt_respone_arr['status_code'] !== 200) throw new ValidateException($tt_respone_arr['error_desc']);
            if ($type == 4) {
                //合规检测失败
                if (!$tt_respone_content['is_valid'] || $tt_respone_content['status_code'] != 0) throw new ValidateException($tt_respone_arr['status_msg']);
                //开始修改
                $url = config('my.main_link') . 'api/v1/ttapi/update_unique_id?impl=1&cached=1';
                //接口请求
                $respone_update = doHttpPosts($url, json_encode($data), $access, $proxy);
                $respone_update_arr = json_decode($respone_update, true);
                if (!isset($respone_update_arr) || !is_array($respone_update_arr)) throw new ValidateException('接口修改异常');

                if ($respone_update_arr['response_status'] == 0) {
                    /*//TT接口请求
                    $tt_respone_update_json = $this->getResToTT($respone_update_arr, $proxy);*/
                    if (!isset($respone_update_arr['response'])) throw new ValidateException('response为空');
                    $tt_respone_update_arr = $respone_update_arr['response'];

                    if ($tt_respone_update_arr['status_code'] != 200) throw new ValidateException($tt_respone_update_arr['error_desc']);
                    $tt_respone_update_content = json_decode($respone_update_arr['response']['content'], true);
                    if (isset($tt_respone_update_content['data']) && $tt_respone_update_content['data']['error_code']) throw new ValidateException($tt_respone_update_content['data']['description']);

                    db("member")->where("uid", $user_id)->update($save);
                    return $this->ajaxReturn($this->successCode, '修改成功', $tt_respone_update_arr);
                }
            } else {
                if ($tt_respone_content['status_code'] == 0) {
                    db("member")->where("uid", $user_id)->update($save);
                    return $this->ajaxReturn($this->successCode, '修改成功', $tt_respone_arr);
                } else {
                    throw new ValidateException($tt_respone_content['status_msg']);
                }
            }
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }
    }

    //获取个人信息
    public function getuserinfo()
    {

        $users = db('member')->where("ifup", 1)->orderRaw("rand()")->select();
        $user = $users[0];

        if (!$user) {
            die("没有需要查询的数据");
        }
        //取接口授权
        $access = getAccessInfo();
        //随机游客token
        $jstoken = doToken('', 2);

        //取http代理
        $proxy = getHttpProxy($jstoken['user']['uid']);

        $params = [
            "user_id" => $user['uid'],
            "sec_user_id" => $user['sec_uid']
        ];
        $data = [
            "token" => $jstoken,
            "proxy" => $proxy,
            "params" => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/profile_other?impl=1&cached=1';
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access, "");

        $respone_arr = json_decode($respone, true);
        // if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException(, '接口异常');
        if ($respone_arr['response']['status_code'] == 200) {
            //TT接口请求
//            $tt_respone_json = $this->CurlRequest($respone_arr['request']['url'], null, $respone_arr['request']['headers']);
            $info = json_decode($respone_arr['response']['content'], true);
            // var_dump($info);die;
            $user_info = $info['user'];
            if (empty($user_info)) throw new ValidateException('无用户数据');
//                $avatar_pic = dlfile($user_info['avatar_medium']['url_list'][0], './Public/avatar_pic/' . $user_info['uid'] . '.webp');
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
            $res = db('member')->where('uid', $user['uid'])->update($updata);
            return json_encode([
                'code' => 0,
                'msg' => "成功：{$user['uid']}\r\n" . $respone_arr['response']['error_desc'],
                'data' => [],
            ]);
            throw new ValidateException("成功：{$user['uid']}\r\n" . $respone_arr['response']['error_desc']);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => "失败：" . $respone_arr['response']['error_desc'],
                'data' => [],
            ]);
            throw new ValidateException("失败：" . $respone_arr['response']['error_desc']);
        }
    }

    //获取个人信息
    public function getuserinfo1()
    {

        $users = db('member')->where("ifup", 1)->orderRaw("rand()")->select();
        $user = $users[0];

        if (!$user) {
            die("没有需要查询的数据");
        }
        //取接口授权
        $access = getAccessInfo();
        //随机游客token
        $jstoken = doToken('', 2);
        echo "游客uid：" . $jstoken['user']['uid'] . "\r\n";
        //取http代理
        $proxy = getHttpProxy($jstoken['user']['uid']);

        $params = [
            "user_id" => $user['uid'],
            "sec_user_id" => $user['sec_uid']
        ];
        $data = [
            "token" => $jstoken,
            "proxy" => $proxy,
            "params" => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/profile_other?impl=0';
        //接口请求
        $respone = doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) throw new ValidateException('接口异常');
        if ($respone_arr['response_status'] == 0) {
            //TT接口请求
//            $tt_respone_json = $this->CurlRequest($respone_arr['request']['url'], null, $respone_arr['request']['headers']);
            $tt_respone_json = $this->getResToTT($respone_arr, $proxy);
            $tt_respone_arr = json_decode($tt_respone_json, true);
            if (empty($tt_respone_arr) && trim($tt_respone_json) == '') throw new ValidateException("TT接口异常，{$user['uid']}");

            if ($tt_respone_arr['status_code'] == 0) {
                $user_info = $tt_respone_arr['user'];
                if (empty($user_info)) throw new ValidateException('无用户数据');
//                $avatar_pic = dlfile($user_info['avatar_medium']['url_list'][0], './Public/avatar_pic/' . $user_info['uid'] . '.webp');
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
                $res = db('member')->where('uid', $user['uid'])->update($updata);
                die("成功：{$user['uid']}\r\n" . $tt_respone_json);
            } else {
                throw new ValidateException($tt_respone_arr['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['response_msg']);
        }
    }
}