<?php
/*
 module:		视频任务详情
 create_time:	2022-11-25 13:50:01
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Videotaskdetails;
use base\CommonService;
use think\exception\ValidateException;

class VideotaskdetailsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('videotaskdetails')->field($field)->alias('a')->join('videotasks b', 'a.videotasks_id=b.videotasks_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['status'] = !is_null($data['status']) ? $data['status'] : '1';
            $res = Videotaskdetails::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->videotaskdetails_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            $data['pay_time'] = time();
            $res = Videotaskdetails::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

