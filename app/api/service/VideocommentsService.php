<?php
/*
 module:		视频评论任务
 create_time:	2022-12-27 14:49:35
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Videocomments;
use base\CommonService;
use think\exception\ValidateException;

class VideocommentsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Videocomments::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return ['list' => $res['data'], 'count' => $res['total']];
    }


    /*
     * @Description  添加
     */
    public static function add($data)
    {
        try {
            validate(\app\api\validate\Videocomments::class)->scene('add')->check($data);
            $data['add_time'] = time();
            $res = Videocomments::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->videocomments_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Videocomments::class)->scene('update')->check($data);
            !is_null($data['add_time']) && $data['add_time'] = strtotime($data['add_time']);
            $res = Videocomments::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

