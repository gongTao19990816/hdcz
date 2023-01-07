<?php
/*
 module:		视频任务发布
 create_time:	2022-11-25 13:42:04
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Videotasks;
use base\CommonService;
use think\exception\ValidateException;

class VideotasksService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Videotasks::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Videotasks::class)->scene('add')->check($data);
            $data['release_time'] = time();
            $res = Videotasks::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->videotasks_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            validate(\app\api\validate\Videotasks::class)->scene('update')->check($data);
            !is_null($data['release_time']) && $data['release_time'] = strtotime($data['release_time']);
            $res = Videotasks::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

