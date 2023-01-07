<?php
/*
 module:		uid库
 create_time:	2022-12-13 15:38:52
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Uidlibrary;
use base\CommonService;
use think\exception\ValidateException;

class UidlibraryService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = db('uidlibrary')->field($field)->alias('a')->join('libraryid b', 'a.libraryid_id=b.libraryid_id', 'left')->where($where)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            $data['add_time'] = time();
            $res = Uidlibrary::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->uidlibrary_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            !is_null($data['add_time']) && $data['add_time'] = strtotime($data['add_time']);
            $res = Uidlibrary::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }


}

