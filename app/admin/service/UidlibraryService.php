<?php
/*
 module:		uid库
 create_time:	2022-12-13 15:37:27
 author:		大怪兽
 contact:		
*/

namespace app\admin\service;

use app\admin\model\Uidlibrary;
use base\CommonService;
use think\exception\ValidateException;

class UidlibraryService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $order, $limit, $page)
    {
        try {
            $res = db('uidlibrary')->field($field)->alias('a')->join('libraryid b', 'a.libraryid_id=b.libraryid_id', 'left')->where($where)->order($order)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['add_time'] = time();
            $res = Uidlibrary::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        if (!$res) {
            throw new ValidateException ('操作失败');
        }
        return $res->uidlibrary_id;
    }


    /*
     * @Description  修改
     */
    public static function update($data)
    {
        try {
            $data['add_time'] = strtotime($data['add_time']);
            $res = Uidlibrary::update($data);
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

