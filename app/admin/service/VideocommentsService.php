<?php
/*
 module:		视频评论任务
 create_time:	2022-11-26 13:46:15
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Videocomments;
use base\CommonService;
use think\exception\ValidateException;

class VideocommentsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = Videocomments::where($where)->field($field)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['rows' => $res['data'], 'total' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            validate(\app\admin\validate\Videocomments::class)->scene('add')->check($data);
            $data['add_time'] = time();
            $res = Videocomments::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->videocomments_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            validate(\app\admin\validate\Videocomments::class)->scene('update')->check($data);
            $data['add_time'] = strtotime($data['add_time']);
            $res = Videocomments::update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res;
    }


}

