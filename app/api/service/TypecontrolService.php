<?php
/*
 module:		类型管理
 create_time:	2022-11-17 15:34:10
 author:		大怪兽
 contact:		
*/

namespace app\api\service;

use app\api\model\Typecontrol;
use base\CommonService;
use think\exception\ValidateException;

class TypecontrolService extends CommonService
{


    /*
     * @Description  列表数据
     */
    public static function indexList($where, $field, $orderby, $limit, $page)
    {
        try {
            $res = Typecontrol::where($where)->field($field)->order($orderby)->paginate(['list_rows' => $limit, 'page' => $page])->toArray();
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
            validate(\app\api\validate\Typecontrol::class)->scene('add')->check($data);
            $data['pid'] = !is_null($data['pid']) ? $data['pid'] : '0';
            $res = Typecontrol::create($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res->typecontrol_id;
    }


    /*
     * @Description  修改
     */
    public static function update($where, $data)
    {
        try {
            $res = Typecontrol::where($where)->update($data);
        } catch (ValidateException $e) {
            throw new ValidateException ($e->getError());
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }
        return $res;
    }

    /*
 * @Description  数据的使用
 */
    public static function updatalist($nickname_id, $headimage_id, $autograph_id)
    {
        $updata['status'] = 2;//数据出去了
        $updata['usage_time'] = time();
        if ($nickname_id) {
            db('nickname')->where('nickname_id', $nickname_id)->update($updata);
        }
        // if($video_num){
        //     db('material')->where('video_num',$video_num)->update($updata);
        // }
        if ($headimage_id) {
            db('headimage')->where('headimage_id', $headimage_id)->update($updata);
        }
        if ($autograph_id) {
            db('autograph')->where('autograph_id', $autograph_id)->update($updata);
        }
    }


}

