<?php

namespace app\api\controller;

use think\exception\ValidateException;

class Apidata extends Common
{
    //获取作品列表
    public function getvideobymy()
    {
        set_time_limit(0);

        //取接口授权
        $access = $this->getAccessInfo();
        //处理token
        $token = $this->doToken('', 2); //游客随机token
        // var_dump($token);die;
        //取http代理
        $proxy = $this->getHttpProxy($token['user']['uid']);
        // var_dump($proxy);die;
        $uid = $this->request->post('uid', '', 'serach_in');
        $sec_uid = $this->request->post('sec_uid', '', 'serach_in');
        $max_cursor = $this->request->post('max_cursor', 0, 'serach_in');
        if (empty($uid)) {
            throw new ValidateException('参数错误');
        }
        $user_id = db('member')->where('uid', $uid)->find();
        $params = [
            'user_id' => $uid,
            'sec_user_id' => $sec_uid ?: $user_id['sec_uid'],
            'source' => 0,
            'count' => 20,
            'max_cursor' => $max_cursor
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];

        $url = config('my.main_link') . 'api/v1/ttapi/aweme_post?impl=1&cached=1';
        //接口请求
        $respone = $this->doHttpPosts($url, json_encode($data), $access, "");
        // var_dump($respone);die;
        $respone_arr = json_decode($respone, true);
        if (!isset($respone_arr) || !is_array($respone_arr)) $this->returnJsonp('-12', '接口异常');
        if ($respone_arr['response']['status_code'] == 200) {
            //TT接口请求
            var_dump($respone_arr['response']['content']);
            die;
            $content = json_decode($respone_arr['response']['content'], true);
            // var_dump($content);die;

            if ($content['status_code'] == 0) {
                $has_more = $content['has_more']; //是否有更多
                $max_cursor = $content['max_cursor'];
                $aweme_list = $content['aweme_list']; //视频列表

                if (empty($aweme_list)) $this->returnJsonp('-7', '没有视频');
                foreach ($aweme_list as $k => $v) {
                    $member_id = db('member')->where('uid', $v['author']['uid'])->value('member_id');
                    $insert['member_id'] = $member_id;
                    // var_dump($v['author']['uid']);die;
                    $insert['aweme_id'] = $v['aweme_id'];
                    $insert['comment_count'] = $v['statistics']['comment_count']; //评论数量
                    $insert['digg_count'] = $v['statistics']['digg_count']; //点赞
                    $insert['share_count'] = $v['statistics']['share_count']; //分享数量
                    $insert['play_count'] = $v['statistics']['play_count']; //播放数量
                    $insert['collect_count'] = $v['statistics']['collect_count']; //收藏数量
                    $insert['download_count'] = $v['statistics']['download_count']; //下载数量
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
                db('user_cursor')->where(['user_id' => $user_id['uid']])->save(['max_cursor' => $max_cursor]);
                $datas['has_more'] = $has_more;
                $datas['max_cursor'] = $max_cursor;
                return $this->ajaxReturn($this->successCode, $msg, $datas);

            } else {
                $this->returnJsonp('-6', $content['status_msg']);
            }
        } else {
            $this->returnJsonp('-2', $respone_arr['error_desc']);
        }

    }

    //博主的关注列表
    public function followingList()
    {
        set_time_limit(0);
        //取接口授权
        $access = $this->getAccessInfo();
        //处理token
        $token = $this->doToken('', 2); //游客随机token
        //取http代理
        $proxy = $this->getHttpProxy($token['user']['uid']);
        $user_id = $this->request->post('uid');
        $sec_user_id = $this->request->post('sec_uid');
        if (empty($user_id)) {
            throw new ValidateException('参数错误');
        }
        // $sec_user_id = db('member')->where('uid', $user_id)->find();
        $max_time = $this->request->post('max_time', 0, 'serach_in');
        // if(empty($user_id) || empty($sec_user_id)) $this->returnJsonp('-1','用户信息未传');

        $params = [
            'user_id' => $user_id,
            'sec_user_id' => $sec_user_id,
            'source_type' => 1,
            'count' => 20,
            'vcdAuthFirstTime' => 0,
            'max_time' => $max_time
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];

        $url = config('my.main_link') . 'api/v1/ttapi/following_list?impl=1&cached=1';
        //请求接口
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        if ($respone_arr['response']['status_code'] == 200) {
            return $respone_arr['response']['content'];
            var_dump($respone_arr['response']['content']);
            die;
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                $has_more = $content['has_more']; //是否有更多
                $max_time = $content['min_time'];
                $followings = $content['followings']; //关注列表
                if (empty($followings)) throw new ValidateException('没有数据');
                foreach ($followings as $k => $v) {
                    $adddata['nickname'] = $v['nickname'];
                    $adddata['member_id'] = $sec_user_id['member_id'];
                    $adddata['create_time'] = $v['create_time'];
                    $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
                    $adddata['sec_uid'] = $v['sec_uid'];
                    $adddata['following_count'] = $v['following_count'];
                    $adddata['follower_count'] = $v['follower_count'];
                    $adddata['favoriting_count'] = $v['favoriting_count'];
                    $adddata['unique_id'] = $v['unique_id'];
                    $adddata['uid'] = $v['uid'];
                    $adddata['aweme_count'] = $v['aweme_count'];
                    $adddata['region'] = $this->transCountryCode($v['region']);
                    $adddata['ifpic'] = 1;
                    $where['member_id'] = $sec_user_id['member_id'];
                    $where['uid'] = $v['uid'];
                    $followinglistinfo = db('followinglist')->where($where)->find();
                    if ($followinglistinfo) {
                        $res = db('followinglist')->where($where)->update($adddata);
                    } else {
                        $res = db('followinglist')->insert($adddata);
                    }
                }
                // db('user_cursor')->where(['user_id' => $user_id['uid']])->save(['max_cursor' => $max_cursor]);
                $datas['has_more'] = $has_more;
                $datas['min_time'] = $max_time;
                return $this->ajaxReturn($this->successCode, '拉取成功', $datas);

            } else {
                throw new ValidateException($content['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }
    }

    //博主的粉丝列表
    public function FensiList()
    {
        //取接口授权
        $access = $this->getAccessInfo();
        //处理token
        $token = $this->doToken('', 2); //游客随机token
        //取http代理
        $proxy = $this->getHttpProxy($token['user']['uid']);

        $user_id = $this->request->post('uid');
        $sec_user_id = db('member')->where('uid', $user_id)->find();
        $max_time = $this->request->post('max_time', 0, 'serach_in');
        if (empty($user_id)) {
            throw new ValidateException('参数错误');
        }

        $params = [
            'user_id' => $user_id,
            'sec_user_id' => $sec_user_id['sec_uid'],
            'source_type' => 1,
            'count' => 20,
            'offset' => 0,
            'max_time' => $max_time
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/follower_list?impl=1&cached=1';
        //接口请求
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        if ($respone_arr['response']['status_code'] == 200) {
            // var_dump($respone_arr['response']['content']);
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                $has_more = $content['has_more']; //是否有更多
                $max_time = $content['min_time'];
                $followings = $content['followers']; //关注列表
                if (empty($followings)) throw new ValidateException('没有数据');
                foreach ($followings as $k => $v) {
                    $adddata['nickname'] = $v['nickname'];
                    $adddata['member_id'] = $sec_user_id['member_id'];
                    $adddata['create_time'] = $v['create_time'];
                    $adddata['avatar_thumb'] = $v['avatar_medium']['url_list'][0];
                    $adddata['sec_uid'] = $v['sec_uid'];
                    $adddata['following_count'] = $v['following_count'];
                    $adddata['follower_count'] = $v['follower_count'];
                    $adddata['favoriting_count'] = $v['favoriting_count'];
                    $adddata['unique_id'] = $v['unique_id'];
                    $adddata['uid'] = $v['uid'];
                    $adddata['aweme_count'] = $v['aweme_count'];
                    $adddata['region'] = $this->transCountryCode($v['region']);
                    $adddata['ifpic'] = 1;
                    $where['member_id'] = $sec_user_id['member_id'];
                    $where['uid'] = $v['uid'];
                    $followinglistinfo = db('fanslist')->where($where)->find();
                    if ($followinglistinfo) {
                        $res = db('fanslist')->where($where)->update($adddata);
                    } else {
                        $res = db('fanslist')->insert($adddata);
                    }
                }
                $datas['has_more'] = $has_more;
                $datas['min_time'] = $max_time;
                return $this->ajaxReturn($this->successCode, '拉取成功', $datas);
            } else {
                throw new ValidateException($content['status_msg']);
            }
            // var_dump($respone);die;
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }

    }

    //关注用户
    public function followUser()
    {
        //取接口授权
        $access = $this->getAccessInfo();
        $user_id = $this->request->post('user_id');//关注者的uid
        //取登录后的token
        $user_info = db('member')->field('token')->where('uid', $user_id)->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = $this->doToken($user_info['token']);
        //取http代理
        $proxy = $this->getHttpProxy($user_id);

        $b_user_id = $this->request->post('b_user_id'); //被关注对象的用户user_id
        $b_sec_user_id = $this->request->post('b_sec_user_id'); //被关注对象的用户sec_user_id
        if (empty($b_user_id) || empty($b_sec_user_id)) throw new ValidateException('被关注者缺少参数');

        if ($user_id == $b_user_id) throw new ValidateException('不能关注自己');
        $followuserinfo = db('followuser')->where(['uid' => $user_id, 'b_uid' => $b_user_id, 'type' => 1])->find();
        if ($followuserinfo) {
            throw new ValidateException('已经关注过了');
        }
        $params = [
            'user_id' => $b_user_id,
            'sec_user_id' => $b_sec_user_id,
            'from' => 19,
            'from_pre' => 13,
            'channel_id' => 3,
            'type' => 1  // # 1 表示关注，0 表示取消关注
        ];

        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        // print_r($data);die;
        $url = config('my.main_link') . 'api/v1/ttapi/follow_user?impl=1&cached=1';
        //请求接口
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        // var_dump($respone);die;
        $respone_arr = json_decode($respone, true);
        if ($respone_arr['response']['status_code'] == 200) {
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                $datas['uid'] = $user_id;
                $datas['b_uid'] = $b_user_id;
                $datas['b_sec_user_id'] = $b_sec_user_id;
                $datas['create_time'] = time();
                $datas['type'] = 1;
                $res = db('followuser')->insert($datas);
                if ($res) {
                    return $this->ajaxReturn($this->successCode, '关注成功', $res);
                }
            } else {
                throw new ValidateException($content['status_msg']);
            }
            // var_dump($respone);die;
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }

    }

    //作品点赞
    public function digg()
    {
        set_time_limit(0);

        //取接口授权
        $access = $this->getAccessInfo();

        $user_id = $this->request->post('user_id');
        //取登录后的token
        $user_info = db('member')->field('token')->where('uid', $user_id)->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = $this->doToken($user_info['token']);

        //取http代理
        $proxy = $this->getHttpProxy($user_id);

        $user_use_count = db('Videodigg')->where(['uid' => $user_id, 'type' => 1])->find();
        if ($user_use_count) {
            throw new ValidateException('该用户给这个作品已经点过赞了');
        }

        $aweme_id = $this->request->post('aweme_id'); //作品ID
        if (empty($aweme_id)) throw new ValidateException('作品ID未传');

        $params = [
            'aweme_id' => $aweme_id,
            'type' => 1,
            'enter_from' => 'homepage_hot',
            'channel_id' => 0,
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        // print_r($data);die;
        $url = config('my.main_link') . 'api/v1/ttapi/aweme/digg?impl=1&cached=1';
        //接口请求
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        if ($respone_arr['response']['status_code'] == 200) {
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                $datas['uid'] = $user_id;
                $datas['aweme_id'] = $aweme_id;
                $datas['create_time'] = time();
                $datas['type'] = 1;
                $res = db('videodigg')->insert($datas);
                if ($res) {
                    return $this->ajaxReturn($this->successCode, '点赞成功', $res);
                }
            } else {
                throw new ValidateException($content['status_msg']);
            }
            // var_dump($respone);die;
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }


    }

    //评论点赞
    public function cdigg()
    {
        set_time_limit(0);
        //取接口授权
        $access = $this->getAccessInfo();

        $user_id = $this->request->post('user_id');
        //取登录后的token
        $user_info = db('member')->field('token')->where('uid', $user_id)->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = $this->doToken($user_info['token']);

        //取http代理
        $proxy = $this->getHttpProxy($user_id);

        $aweme_id = $this->request->post('aweme_id'); //作品ID
        if (empty($aweme_id)) throw new ValidateException('作品ID未传');

        $cid = $this->request->post('cid'); //评论ID
        if (empty($cid)) throw new ValidateException('评论ID未传');
        $user_use_count = db('videocommentdigg')->where(['uid' => $user_id, 'aweme_id' => $aweme_id, 'cid' => $cid])->count();
        if ($user_use_count > 0) throw new ValidateException('该用户对该评论已经点过赞了');

        $params = [
            'aweme_id' => $aweme_id,
            'cid' => $cid,
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/comment/digg?impl=1&cached=1';
        //接口请求
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        // var_dump($respone);die;
        if ($respone_arr['response']['status_code'] == 200) {
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                $datas['uid'] = $user_id;
                $datas['aweme_id'] = $aweme_id;
                $datas['cid'] = $cid;
                $datas['create_time'] = time();
                $res = db('videocommentdigg')->insert($datas);
                if ($res) {
                    return $this->ajaxReturn($this->successCode, '点赞成功', $res);
                }
            } else {
                throw new ValidateException($content['status_msg']);
            }
            // var_dump($respone);die;
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }

    }

    //获取来访者记录
    public function view_record()
    {
        set_time_limit(0);
        //取接口授权
        $access = $this->getAccessInfo();
        $user_id = $this->request->post('user_id');
        //取登录后的token
        $user_info = db('member')->field('token')->where('uid', $user_id)->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = $this->doToken($user_info['token']);
        //取http代理
        $proxy = $this->getHttpProxy($user_id);
        $params = [
            'count' => 20,
            'from' => 1,
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/profile/view_record?impl=1&cached=1';
        //接口请求
        // var_dump($data);die;
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        $respone_arr = json_decode($respone, true);
        // var_dump($respone_arr['response']['content']);die;
        if ($respone_arr['response']['status_code'] == 200) {
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                if ($content['is_authorized'] == false) {
                    throw new ValidateException('未开启该隐私设置');
                }
                $userlist = $content['viewer_list'];
                foreach ($userlist as $k => $v) {
                    $member_id = db('member')->where('uid', $user_id)->value('member_id');
                    $user = $v['user'];
                    $adddata['unique_id'] = $user['unique_id'];
                    $adddata['avatar_thumb'] = $user['avatar_medium']['url_list'][0];
                    $adddata['sec_uid'] = $user['sec_uid'];
                    $adddata['nickname'] = $user['nickname'];
                    $adddata['signature'] = $user['signature'];
                    $adddata['follower_status'] = $user['follower_status'];
                    $adddata['follow_status'] = $user['follow_status'];
                    $adddata['total_favorited'] = $user['total_favorited'];
                    $adddata['country'] = $this->transCountryCode($user['region']);
                    $adddata['aweme_count'] = $user['aweme_count'];
                    $adddata['ifpic'] = 1;
                    $adddata['member_id'] = $member_id;
                    $adddata['uid'] = $user['uid'];
                    $memberinfo = db('visitorlist')->where(['uid' => $user['uid'], 'member_id' => $member_id])->find();
                    if ($memberinfo) {
                        $res = db('visitorlist')->where(['uid' => $user['uid'], 'member_id' => $member_id])->update($adddata);
                        $msg = '更新成功';
                    } else {
                        $res = db('visitorlist')->insert($adddata);
                        $msg = '添加成功';
                    }
                }

                return $this->ajaxReturn($this->successCode, $msg, $datas);

            } else {
                throw new ValidateException($content['status_msg']);
            }
            // var_dump($respone);die;
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }
        // var_dump($respone);die;
    }

    //开启来访者隐私设置
    public function profile_setting()
    {
        set_time_limit(0);
        //取接口授权
        $access = $this->getAccessInfo();
        $user_id = $this->request->post('user_id');
        //取登录后的token
        $user_info = db('member')->field('token')->where('uid', $user_id)->find();
        if (empty($user_info)) throw new ValidateException('没有此用户');
        $token = $this->doToken($user_info['token']);
        //取http代理
        $proxy = $this->getHttpProxy($user_id);
        $params = [
            'field' => 'profile_view_history',
            'value' => 1,
        ];
        $data = [
            'proxy' => $proxy,
            'token' => $token,
            'params' => $params
        ];
        $url = config('my.main_link') . 'api/v1/ttapi/profile_setting/update?impl=1&cached=1';
        //接口请求
        // var_dump($data);die;
        $respone = $this->doHttpPosts($url, json_encode($data), $access);
        // var_dump($respone);die;
        $respone_arr = json_decode($respone, true);
        if ($respone_arr['response']['status_code'] == 200) {
            $content = json_decode($respone_arr['response']['content'], true);
            if ($content['status_code'] == 0) {
                return $this->ajaxReturn($this->successCode, '开启成功');
            } else {
                throw new ValidateException($content['status_msg']);
            }
        } else {
            throw new ValidateException($respone_arr['error_desc']);
        }
    }

    //下载来访人的头像
    function getimage()
    {
        set_time_limit(0);
        $where['ifpic'] = 1;
        // $where['member_id'] = 20;
        $head_img = db('visitorlist')->where($where)->find();
        if ($head_img) {
            $imageurl = config('my.host_url') . '/uploads/xiazai/' . $head_img['uid'] . '.png';
            $arr = $this->dlfile($head_img['avatar_thumb'], app()->getRootPath() . '/public/uploads/xiazai/' . $head_img['uid'] . '.png');
            $data['ifpic'] = 0;
            $data['avatar_thumb'] = $imageurl;
            $arr = db('visitorlist')->where('uid', $head_img['uid'])->update($data);
            if ($arr) {
                echo '成功' . $imageurl;
            }
        } else {
            echo '没有图片或者没有任务了';
        }
    }




    //处理token
    /*
     * token 用户token
     * type:0 接收的参数-登录后的token；1：游客固定的token；2：随机取的token
     */
    public function doToken($token = '', $type = 0)
    {
        if ($type == 0) {
//             $token = trim(I('token'));
            if (empty($token) || $token == '' || $token == null) $this->returnJsonp('-1', 'token未取到值');;
        } else if ($type == 1) {
            $user_token = M('token_config')->field('values')->where(['id' => 1])->find();
            if (empty($user_token) || trim($user_token['values']) == '') $this->returnJsonp('-1', '请先设置游客固定的token');
            $token = $user_token['values'];
        } else { //游客列表随机的token
            $user_token_one = db('user_token_list')->limit(1)->orderRaw('rand()')->select();
            $user_token = $user_token_one[0];

            if (empty($user_token) || trim($user_token['token']) == '') $this->returnJsonp('-1', '请先设置游客列表的token');
            $token = $user_token['token'];
        }
        $token_str = str_replace('&quot;', '"', $token);
        $token_str = str_replace('&amp;', '&', $token_str);
        $token = json_decode($token_str, true);
        return $token;
    }

    /**
     * @param number $user_id
     */
    public function getHttpProxy($user_id = 0)
    {
        //取http代理链接
        $info = Db('user_token_log')->where(['user_id' => $user_id])->order("id desc")->find();
        $url_http = config('my.TT_PRO_HTTP');
        if (empty($info)) {
            $j = 1;
            for ($i = 0; $i < 3; $i++) {
                $j = $j + $i;
                $http_proxy = $this->CurlRequest($url_http);
                if (strpos($http_proxy, 'http://') === false && strpos($http_proxy, 'https://') === false && strpos($http_proxy, 'socks5://') === false) {
                    continue;
                }
                $where = ['user_proxy' => $http_proxy];
                $r = Db('user_token_log')->where($where)->find();
                if ($r) {
                    continue;
                } else {
                    break;
                }
            }
            if ($j >= 3) $this->returnJsonp('-6', '获取代理频繁，请稍后再试');

            Db('user_token_log')->insert(['user_id' => $user_id, 'user_proxy' => $http_proxy, 'addtime' => time()]);
        } else {
            $http_proxy = $info['user_proxy'];
            if (($info['addtime'] + 1700) < time()) {
                //代理过期需要重新获取
                //$http_proxy = $this->CurlRequest($url_http);

                $j = 1;
                for ($i = 0; $i < 3; $i++) {
                    $j = $j + $i;
                    $http_proxy = $this->CurlRequest($url_http);
                    if (strpos($http_proxy, 'http://') === false && strpos($http_proxy, 'https://') === false && strpos($http_proxy, 'socks5://') === false) {
                        continue;
                    }
                    $where = ['user_proxy' => $http_proxy];
                    $r = Db('user_token_log')->where($where)->find();
                    if ($r) {
                        continue;
                    } else {
                        break;
                    }
                }
                if ($j >= 3) $this->returnJsonp('-6', '获取代理频繁，请稍后再试');


                Db('user_token_log')->where(['user_id' => $user_id])->save(['user_proxy' => $http_proxy, 'addtime' => time()]);
            }
        }
        Db('user_token_log')->insert(['user_id' => $user_id, 'user_proxy' => $http_proxy, 'addtime' => time()]);
        return $http_proxy;
    }

    //取接口授权
    public function getAccessInfo()
    {
        $access_token = db('information')->where(['information_id' => 1])->value("access_token");
        if (empty($access_token) || trim($access_token) == '') return $this->returnJsonp('-1', '未设置接口请求授权');
        return $access_token;
    }

    public function returnJsonp($result = '', $message = '', $data = '', $page = '', $type = 'json')
    {
        $json = array('result' => $result, 'message' => $message, 'data' => $data, 'page' => $page);
        die(json_encode($json));
    }
}