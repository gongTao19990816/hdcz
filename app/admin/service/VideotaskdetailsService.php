<?php
/*
 module:		视频任务详情
 create_time:	2022-11-25 13:49:30
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Videotaskdetails;
use base\CommonService;
use think\exception\ValidateException;

class VideotaskdetailsService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('videotaskdetails')->field($field)->alias('a')->join('videotasks b', 'a.videotasks_id=b.videotasks_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $res = Videotaskdetails::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->videotaskdetails_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['pay_time'] = time();
            $res = Videotaskdetails::update($data);
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

