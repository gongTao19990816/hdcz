<?php
// +----------------------------------------------------------------------
// | 应用公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------


use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Db;

error_reporting(0);


/**
 * 创建并返回redis任务key
 * @param string $middle
 * @return string
 */
function get_task_key(string $middle): string
{
    $key = config('my.task_key_prefix') . $middle . '_' . uniqid();
    return str_replace('_', ':', $key);
}

function create_uuid($prefix = "")
{
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr($chars, 0, 8) . '-'
        . substr($chars, 8, 4) . '-'
        . substr($chars, 12, 4) . '-'
        . substr($chars, 16, 4) . '-'
        . substr($chars, 20, 12);
    return $prefix . $uuid;
}

function getTypeParentNames($myid)
{
    $array = db("typecontrol")->select();
    $pid = 0;
    $newStr = "";
    foreach ($array as $value) {
        if (!isset($value["typecontrol_id"])) {
            continue;
        }
        if ($value["typecontrol_id"] == $myid) {
            $newStr = $value['type_title'] . "/";
            $pid = $value["pid"];
            break;
        }
    }
    if ($pid) {
        $str = getTypeParentNames($pid);
        $newStr = $str . $newStr;
    }
    return $newStr;
}

/**
 * 立即响应（中断）请求
 */
function flushRequest()
{
    if (function_exists('fastcgi_finish_request')) {            //Nginx使用
        fastcgi_finish_request();        //后面输出客户端获取不到
    } else {            //apache 使用
        $size = ob_get_length();
        header("Content-length: $size");
        header('Connection:close');
        ob_end_flush();
        //ob_flush();       //加了没效果
        flush();
    }
    ignore_user_abort(true);


    /*ob_end_clean();
    ob_start();

    echo '{"data":"OK"}';

    $size = ob_get_length();

    header("HTTP/1.1 200 OK");
    header("Content-Length: $size");
    header("Connection: close");
    header("Content-Type: application/json;charset=utf-8");

    ob_end_flush();
    if (ob_get_length()){
        ob_flush();
    }
    flush();

    if (function_exists("fastcgi_finish_request")) {
        fastcgi_finish_request();
    }

    sleep(1);

    ignore_user_abort(true);*/

}

/**
 * 检查当前任务量加已存在的任务量是否大于设置的可存在任务量
 */
function checkTaskNum($task_num)
{
    return true;
    $redis = connectRedis();
    $task_length = $redis->lLen(config("my.task_key_prefix"));
    if (($task_num + $task_length) > config('my.task_max_num')) {
        throw new ValidateException('可用任务量剩余' . (config('my.task_max_num') - $task_length) . '条，无法发布新任务');
    }
    return true;
}

/**
 * 连接任务redis
 */
function connectRedis()
{
    $redis = new \Redis();
    $redis->connect(config("cache.stores.redis.host"), config("cache.stores.redis.port"));
    $redis->auth(config("cache.stores.redis.password"));
    $redis->select(15);
    return $redis;
}

/**
 * 操作返回跳转的快捷方法
 * @access protected
 * @param mixed $msg 提示信息
 * @param mixed $msg 提示信息
 * @param mixed $data 返回的数据
 * @param integer $wait 跳转等待时间
 * @param array $header 发送的Header信息
 * @return void
 */
function returnJsonp($code = 0, $msg = '', $data = '', int $wait = 3, array $header = [])
{
    $result = [
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ];


    $response = json($result);

    throw new HttpResponseException($response);
}

//处理token
/*
 * token 用户token
 * type:0 接收的参数-登录后的token；1：游客固定的token；2：随机取的token
 */
function doToken($token = '', $type = 0)
{
    if ($type == 0) {
//             $token = trim(I('token'));
        if (empty($token) || $token == '' || $token == null) returnJsonp('-1', 'token未取到值');;
    } else if ($type == 1) {
        $user_token = db('user_token_list')->field('values')->where(['id' => 1])->find();
        if (empty($user_token) || trim($user_token['values']) == '') returnJsonp('-1', '请先设置游客固定的token');
        $token = $user_token['values'];
    } else { //游客列表随机的token
        $user_token_one = db('user_token_list')->limit(1)->orderRaw('rand()')->select();
        $user_token = $user_token_one[0];

        if (empty($user_token) || trim($user_token['token']) == '') returnJsonp('-1', '请先设置游客列表的token');
        $token = $user_token['token'];
    }
    $token_str = str_replace('&quot;', '"', $token);
    $token_str = str_replace('&amp;', '&', $token_str);
    $token = json_decode($token_str, true);
    return $token;
}

function getSocketProxy()
{
    $url = 'http://192.168.4.30:8080/?country=SG&num=1';
    $ctx = stream_context_create(array(
            'http' => array(
                'timeout' => 1//等待1秒
            )
        )
    );
    $res = file_get_contents($url, 0, $ctx);
    if ($res) {
        $res = json_decode($res, true);
        $proxys = $res['data'];
        $proxy = $proxys[0];
    } else {
        $proxy = getHttpProxy();
    }
    return $proxy;
}

/**
 * @param number $user_id
 */
function getHttpProxy($user_id = 0)
{
    //取http代理链接
    $info = Db('user_token_log')->where(['user_id' => $user_id])->order("id desc")->find();
    $url_http = config('my.TT_PRO');
    if (empty($info)) {
        $j = 1;
        for ($i = 0; $i < 3; $i++) {
            $j = $j + $i;
            $http_proxy = CurlRequest($url_http);
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
        if ($j >= 3) returnJsonp('-6', '获取代理频繁，请稍后再试');

        Db('user_token_log')->insert(['user_id' => $user_id, 'user_proxy' => $http_proxy, 'addtime' => time()]);
    } else {
        $http_proxy = $info['user_proxy'];
        if (($info['addtime'] + 1700) < time()) {
            //代理过期需要重新获取
            //$http_proxy = $this->CurlRequest($url_http);

            $j = 1;
            for ($i = 0; $i < 3; $i++) {
                $j = $j + $i;
                $http_proxy = CurlRequest($url_http);
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
            if ($j >= 3) returnJsonp('-6', '获取代理频繁，请稍后再试');


            Db('user_token_log')->where(['user_id' => $user_id])->save(['user_proxy' => $http_proxy, 'addtime' => time()]);
        }
    }
    Db('user_token_log')->insert(['user_id' => $user_id, 'user_proxy' => $http_proxy, 'addtime' => time()]);
    return $http_proxy;
}

function CurlRequest($url, $data = null, $header = null, $proxy = false)
{
    //初始化浏览器
    $ch = curl_init();
    //设置浏览器，把参数url传到浏览器的设置当中
    curl_setopt($ch, CURLOPT_URL, $url);
    //以字符串形式返回到浏览器当中
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //禁止https协议验证域名，0就是禁止验证域名且兼容php5.6
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //禁止https协议验证ssl安全认证证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //判断data是否有数据，如果有data数据传入那么就把curl的请求方式设置为POST请求方式
    if ($data !== '' && $data != null && !empty($data)) {
        //设置POST请求方式
        @curl_setopt($ch, CURLOPT_POST, true);
        //设置POST的数据包
        @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    //设置header头
    if ($header !== '' && $header != null && !empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    if ($proxy) {
        if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) returnJsonp('-21', '代理链接不正确');
        $proxy_temp = explode('//', $proxy);
        $proxy_pass_arr = explode('@', $proxy_temp[1]);
        $proxy_ipport_arr = explode(':', $proxy_pass_arr[0]);
        // HTTP 代理通道
        curl_setopt($ch, CURLOPT_PROXY, $proxy_ipport_arr[0]);
        // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_ipport_arr[1]);
        // HTTP 代理连接的验证方式。当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
    }
    //让curl发起请求
    $ret = curl_exec($ch);
    $err = curl_error($ch);

    if (false === $ret || !empty($err)) {
        $errno = curl_errno($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [
            'ret' => false,
            'errno' => $errno,
            'msg' => $err,
            'info' => $info,
        ];
    }
    //关闭curl浏览器
    curl_close($ch);
    //把请求回来的数据返回
    return $ret;
}


//取接口授权

function getAccessInfo()
{
    $access_token = db('information')->where(['information_id' => 1])->value("access_token");
    if (empty($access_token) || trim($access_token) == '') returnJsonp('-1', '未设置接口请求授权');
    return $access_token;
}

//TT-请求接口
function doHttpPosts($url, $data, $access = '', $proxy = false)
{
    $header = array(
        'Content-Type: application/json;charset=utf-8',
        'Accept: application/json',
        'Authorization:Bearer ' . $access
    );
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $data
    ));
    if ($proxy) {
        if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) returnJsonp('-21', '代理链接不正确');
        $proxy_temp = explode('//', $proxy);
        $proxy_pass_arr = explode('@', $proxy_temp[1]);
        $proxy_ipport_arr = explode(':', $proxy_pass_arr[0]);
        // HTTP 代理通道
        curl_setopt($ch, CURLOPT_PROXY, $proxy_ipport_arr[0]);
        // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_ipport_arr[1]);
        // HTTP 代理连接的验证方式。当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
    }
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $response = curl_exec($ch);
    $Json = json_decode($response);
    $request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    $link = $Json->data->payment_link;

    return $response;
}

/*
 * 下载文件
 */
//下载地址   储存地址
function dlfile($file_url, $save_to)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_URL, $file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file_content = curl_exec($ch);
    curl_close($ch);
    $downloaded_file = fopen($save_to, 'w');
    fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);
}

//上传视频
function doHttpPostsVideo($url, $data, $access, $proxy = false)
{
    $header = array(
        'Content-Type: application/octet-stream',
        'Authorization:Bearer ' . $access
    );
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => $header,
        CURLOPT_POSTFIELDS => $data
    ));
    if ($proxy) {
        if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) returnJsonp('-21', '代理链接不正确');
        $proxy_temp = explode('//', $proxy);
        $proxy_pass_arr = explode('@', $proxy_temp[1]);
        $proxy_ipport_arr = explode(':', $proxy_pass_arr[0]);
        // HTTP 代理通道
        curl_setopt($ch, CURLOPT_PROXY, $proxy_ipport_arr[0]);
        // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_ipport_arr[1]);
        // HTTP 代理连接的验证方式。当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
    }
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    $response = curl_exec($ch);
    $Json = json_decode($response);
    $request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    $link = $Json->data->payment_link;

    return $response;

}

//get请求
function curlGet($url, $access = '', $proxy = '')
{

    $header = [
        'Content-Type: application/json;charset=utf-8',
        'Accept: application/json',
        'Authorization:Bearer ' . $access
    ];

    $ch = curl_init();
    if ($proxy) {
        if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) returnJsonp('-21', '代理链接不正确');
        $proxy_temp = explode('//', $proxy);
        $proxy_pass_arr = explode('@', $proxy_temp[1]);
        $proxy_ipport_arr = explode(':', $proxy_pass_arr[0]);
        // HTTP 代理通道
        curl_setopt($ch, CURLOPT_PROXY, $proxy_ipport_arr[0]);
        // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_ipport_arr[1]);
        // HTTP 代理连接的验证方式。当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * 随机字符
 * @param int $length 长度
 * @param string $type 类型
 * @param int $convert 转换大小写 1大写 0小写
 * @return string
 */
function random($length = 10, $type = 'letter', $convert = 0)
{
    $config = array(
        'number' => '1234567890',
        'letter' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string' => 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if (!isset($config[$type])) $type = 'letter';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string[mt_rand(0, $strlen)];
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    return $code;
}

/*
 * 生成交易流水号
 * @param char(2) $type
 */
function doOrderSn($type)
{
    return date('YmdHis') . $type . substr(microtime(), 2, 3) . sprintf('%02d', rand(0, 99));
}


function deldir($dir)
{
//先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    //删除当前文件夹：
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}


/**
 * 数据签名认证
 * @param array $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

//通过字段值获取字段配置的名称
function getFieldVal($val, $fieldConfig)
{
    if ($fieldConfig) {
        foreach (explode(',', $fieldConfig) as $k => $v) {
            $tempstr = explode('|', $v);
            foreach (explode(',', $val) as $m => $n) {
                if ($tempstr[1] == $n) {
                    $fieldvals .= $tempstr[0] . ',';
                }
            }

        }
        return rtrim($fieldvals, ',');
    }
}


//通过字段名称获取字段配置的值
function getFieldName($val, $fieldConfig)
{
    if ($fieldConfig) {
        foreach (explode(',', $fieldConfig) as $k => $v) {
            $tempstr = explode('|', $v);
            if ($tempstr[0] == $val) {
                $fieldval = $tempstr[1];
            }
        }
        return $fieldval;
    }
}


//通过键值返回键名
function getKeyByVal($array, $data)
{
    foreach ($array as $key => $val) {
        if ($val == $data) {
            $data = $key;
        }
    }
    return $data;
}


//导出时候当有三级联动字段的时候 需要将查询字段重载
function formartExportWhere($field)
{
    foreach ($field as $k => $v) {
        if (strpos($v, '|') > 0) {
            $dt = $field[$k];
            unset($field[$k]);
        }
    }

    return \base\CommonService::filterEmptyArray(array_merge($field, explode('|', $dt)));
}


/*格式化列表*/
function formartList($fieldConfig, $list)
{
    $cat = new \org\Category($fieldConfig);
    $ret = $cat->getTree($list);
    return $ret;
}

/*写入
* @param  string  $type 1 为生成控制器
*/

function filePutContents($content, $filepath, $type)
{
    if (in_array($type, [1, 3])) {
        $str = file_get_contents($filepath);
        $parten = '/\s\/\*+start\*+\/(.*)\/\*+end\*+\//iUs';
        preg_match_all($parten, $str, $all);
        if ($all[0]) {
            foreach ($all[0] as $key => $val) {
                $ext_content .= $val . "\n\n";
            }
        }

        $content .= $ext_content . "\n\n";
        if ($type == 1) {
            $content .= "}\n\n";
        }
    }

    ob_start();
    echo $content;
    $_cache = ob_get_contents();
    ob_end_clean();

    if ($_cache) {
        $File = new \think\template\driver\File();
        $File->write($filepath, $_cache);
    }
}

function htmlOutList($list, $err_status = false)
{
    foreach ($list as $key => $row) {
        $res[$key] = checkData($row, $err_status);
    }
    return $res;
}

//err_status  没有数据是否抛出异常 true 是 false 否
function checkData($data, $err_status = true)
{
    if (empty($data) && $err_status) {
        abort(412, '没有数据');
    }

    if (is_object($data)) {
        $data = $data->toArray();
    }

    foreach ($data as $k => $v) {
        if ($v && is_array($v)) {
            $data[$k] = checkData($v);
        } else {
            $data[$k] = html_out($v);
        }
    }
    return $data;

}

//html代码输入
function html_in($str)
{
    $str = htmlspecialchars($str);
    $str = strip_tags($str);
    $str = addslashes($str);
    return $str;
}


//html代码输出
function html_out($str)
{
    $str = htmlspecialchars_decode($str);
    $str = stripslashes($str);
    return $str;
}

//后台sql输入框语句过滤
function sql_replace($str)
{
    $farr = ["/insert[\s]+|update[\s]+|create[\s]+|alter[\s]+|delete[\s]+|drop[\s]+|load_file|outfile|dump/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//上传文件黑名单过滤
function upload_replace($str)
{
    $farr = ["/php|php3|php4|php5|phtml|pht|/is"];
    $str = preg_replace($farr, '', $str);
    return $str;
}

//查询方法过滤
function serach_in($str)
{
    $farr = ["/^select[\s]+|insert[\s]+|and[\s]+|or[\s]+|create[\s]+|update[\s]+|delete[\s]+|alter[\s]+|count[\s]+|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile/i"];
    $str = preg_replace($farr, '', html_in($str));
    return trim($str);
}


//返回字段定义的时间格式
function getTimeFormat($val)
{
    $default_time_format = explode('|', $val['default_value']);
    $time_format = $default_time_format[0];
    if (!$time_format || $val['default_value'] == 'null') {
        $time_format = 'Y-m-d H:i:s';
    }
    return $time_format;
}

/**
 * 过滤掉空的数组
 * @access protected
 * @param array $data 数据
 * @return array
 */
function filterEmptyArray($data = [])
{
    foreach ($data as $k => $v) {
        if (!$v && $v !== 0)
            unset($data[$k]);
    }
    return $data;
}


/**
 * tp官方数组查询方法废弃，数组转化为现有支持的查询方法
 * @param array $data 原始查询条件
 * @param \think\Model $model 需要查询的模型
 * @return array
 */
function formatWhere($data, $model = null)
{
    if ($model) {
        // 判断后台账号id字段是否存在
        // 判断当前登录账号是否超管
        // 查询是否带入后台账号id
        $fields = $model->getTableFields();
        if (in_array('api_user_id', $fields)) {

        }
    }
    $where = [];
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            if (((string)$v[1] <> null && !is_array($v[1])) || (is_array($v[1]) && (string)$v[1][0] <> null)) {
                switch (strtolower($v[0])) {
                    //模糊查询
                    case 'like':
                        $v[1] = '%' . $v[1] . '%';
                        break;

                    //表达式查询
                    case 'exp':
                        $v[1] = Db::raw($v[1]);
                        break;
                }
                $where[] = [$k, $v[0], $v[1]];
            }
        } else {
            if ((string)$v != null) {
                $where[] = [$k, '=', $v];
            }
        }
    }
    return $where;
}


function getUploadServerUrl($upload_config_id = '')
{
    $appname = app('http')->getName();
    if (config('my.oss_status') && config('my.oss_upload_type') == 'client') {
        switch (config('my.oss_default_type')) {
            case 'qiniuyun';
                $serverurl = 'http://up-z0.qiniup.com?&' . url($appname . '/Base/getOssToken') . '&' . config('my.qny_oss_domain');
                break;

            case 'ali':
                $serverurl = getendpoint(config('my.ali_oss_endpoint')) . '?&' . url($appname . '/Base/getOssToken');
                break;
        }
    } else {
        $serverurl = url($appname . "/Upload/uploadImages", ['upload_config_id' => $upload_config_id]);
    }

    return $serverurl;
}

function getendpoint($str)
{
    if (strpos(config('my.ali_oss_endpoint'), 'aliyuncs.com') !== false) {
        if (strpos($str, 'https') !== false) {
            $point = 'https://' . config('my.ali_oss_bucket') . '.' . substr($str, 8);
        } else {
            $point = 'http://' . config('my.ali_oss_bucket') . '.' . substr($str, 7);
        }
    } else {
        $point = config('my.ali_oss_endpoint');
    }
    return $point;
}

//导出excel表头设置
function getTag($key3, $no = 100)
{
    $data = [];
    $key = ord("A");//A--65
    $key2 = ord("@");//@--64
    for ($n = 1; $n <= $no; $n++) {
        if ($key > ord("Z")) {
            $key2 += 1;
            $key = ord("A");
            $data[$n] = chr($key2) . chr($key);//超过26个字母时才会启用
        } else {
            if ($key2 >= ord("A")) {
                $data[$n] = chr($key2) . chr($key);//超过26个字母时才会启用
            } else {
                $data[$n] = chr($key);
            }
        }
        $key += 1;
    }
    return $data[$key3];
}

/**
 * 实例化数据库类
 * @param string $name 操作的数据表名称（不含前缀）
 * @param array|string $config 数据库配置参数
 * @param bool $force 是否强制重新连接
 * @return \think\db\Query
 */
if (!function_exists('db')) {

    function db($name = '', $connect = '')
    {
        if (empty($connect)) {
            $connect = config('database.default');
        }
        return Db::connect($connect, false)->name($name);
    }
}


//根据国家标识获取中文国家名
function transCountryCode($code)
{
    $index = array(
        'AA' => '阿鲁巴',
        'AD' => '安道尔',
        'AE' => '阿联酋',
        'AF' => '阿富汗',
        'AG' => '安提瓜和巴布达',
        'AL' => '阿尔巴尼亚',
        'AM' => '亚美尼亚',
        'AN' => '荷属安德列斯',
        'AO' => '安哥拉',
        'AQ' => '南极洲',
        'AR' => '阿根廷',
        'AS' => '东萨摩亚',
        'AT' => '奥地利',
        'AU' => '澳大利亚',
        'AZ' => '阿塞拜疆',
        'Av' => '安圭拉岛',
        'BA' => '波黑',
        'BB' => '巴巴多斯',
        'BD' => '孟加拉',
        'BE' => '比利时',
        'BF' => '巴哈马',
        'BF' => '布基纳法索',
        'BG' => '保加利亚',
        'BH' => '巴林',
        'BI' => '布隆迪',
        'BJ' => '贝宁',
        'BM' => '百慕大',
        'BN' => '文莱布鲁萨兰',
        'BO' => '玻利维亚',
        'BR' => '巴西',
        'BS' => '巴哈马',
        'BT' => '不丹',
        'BV' => '布韦岛',
        'BW' => '博茨瓦纳',
        'BY' => '白俄罗斯',
        'BZ' => '伯里兹',
        'CA' => '加拿大',
        'CB' => '柬埔寨',
        'CC' => '可可斯群岛',
        'CD' => '刚果',
        'CF' => '中非',
        'CG' => '刚果',
        'CH' => '瑞士',
        'CI' => '象牙海岸',
        'CK' => '库克群岛',
        'CL' => '智利',
        'CM' => '喀麦隆',
        'CN' => '中国',
        'CO' => '哥伦比亚',
        'CR' => '哥斯达黎加',
        'CS' => '捷克斯洛伐克',
        'CU' => '古巴',
        'CV' => '佛得角',
        'CX' => '圣诞岛',
        'CY' => '塞普路斯',
        'CZ' => '捷克',
        'DE' => '德国',
        'DJ' => '吉布提',
        'DK' => '丹麦',
        'DM' => '多米尼加共和国',
        'DO' => '多米尼加联邦',
        'DZ' => '阿尔及利亚',
        'EC' => '厄瓜多尔',
        'EE' => '爱沙尼亚',
        'EG' => '埃及',
        'EH' => '西撒哈拉',
        'ER' => '厄立特里亚',
        'ES' => '西班牙',
        'ET' => '埃塞俄比亚',
        'FI' => '芬兰',
        'FJ' => '斐济',
        'FK' => '福兰克群岛',
        'FM' => '米克罗尼西亚',
        'FO' => '法罗群岛',
        'FR' => '法国',
        'FX' => '法国-主教区',
        'GA' => '加蓬',
        'GB' => '英国',
        'GD' => '格林纳达',
        'GE' => '格鲁吉亚',
        'GF' => '法属圭亚那',
        'GH' => '加纳',
        'GI' => '直布罗陀',
        'GL' => '格陵兰岛',
        'GM' => '冈比亚',
        'GN' => '几内亚',
        'GP' => '法属德洛普群岛',
        'GQ' => '赤道几内亚',
        'GR' => '希腊',
        'GT' => '危地马拉',
        'GU' => '关岛',
        'GW' => '几内亚比绍',
        'GY' => '圭亚那',
        'HK' => '中国香港特区',
        'HM' => '赫德和麦克唐纳群岛',
        'HN' => '洪都拉斯',
        'HR' => '克罗地亚',
        'HT' => '海地',
        'HU' => '匈牙利',
        'ID' => '印度尼西亚',
        'IE' => '爱尔兰',
        'IL' => '以色列',
        'IN' => '印度',
        'IO' => '英属印度洋领地',
        'IQ' => '伊拉克',
        'IR' => '伊朗',
        'IS' => '冰岛',
        'IT' => '意大利',
        'JM' => '牙买加',
        'JO' => '约旦',
        'JP' => '日本',
        'KE' => '肯尼亚',
        'KG' => '吉尔吉斯斯坦',
        'KH' => '柬埔寨',
        'KI' => '基里巴斯',
        'KM' => '科摩罗',
        'KN' => '圣基茨和尼维斯',
        'KP' => '韩国',
        'KR' => '朝鲜',
        'KW' => '科威特',
        'KY' => '开曼群岛',
        'KZ' => '哈萨克斯坦',
        'LA' => '老挝',
        'LB' => '黎巴嫩',
        'LC' => '圣卢西亚',
        'LI' => '列支顿士登',
        'LK' => '斯里兰卡',
        'LR' => '利比里亚',
        'LS' => '莱索托',
        'LT' => '立陶宛',
        'LU' => '卢森堡',
        'LV' => '拉托维亚',
        'LY' => '利比亚',
        'MA' => '摩洛哥',
        'MC' => '摩纳哥',
        'MD' => '摩尔多瓦',
        'MG' => '马达加斯加',
        'MH' => '马绍尔群岛',
        'MK' => '马其顿',
        'ML' => '马里',
        'MM' => '缅甸',
        'MN' => '蒙古',
        'MO' => '中国澳门特区',
        'MP' => '北马里亚纳群岛',
        'MQ' => '法属马提尼克群岛',
        'MR' => '毛里塔尼亚',
        'MS' => '蒙塞拉特岛',
        'MT' => '马耳他',
        'MU' => '毛里求斯',
        'MV' => '马尔代夫',
        'MW' => '马拉维',
        'MX' => '墨西哥',
        'MY' => '马来西亚',
        'MZ' => '莫桑比克',
        'NA' => '纳米比亚',
        'NC' => '新卡里多尼亚',
        'NE' => '尼日尔',
        'NF' => '诺福克岛',
        'NG' => '尼日利亚',
        'NI' => '尼加拉瓜',
        'NL' => '荷兰',
        'NO' => '挪威',
        'NP' => '尼泊尔',
        'NR' => '瑙鲁',
        'NT' => '中立区(沙特-伊拉克间)',
        'NU' => '纽爱',
        'NZ' => '新西兰',
        'OM' => '阿曼',
        'PA' => '巴拿马',
        'PE' => '秘鲁',
        'PF' => '法属玻里尼西亚',
        'PG' => '巴布亚新几内亚',
        'PH' => '菲律宾',
        'PK' => '巴基斯坦',
        'PL' => '波兰',
        'PM' => '圣皮艾尔和密克隆群岛',
        'PN' => '皮特克恩岛',
        'PR' => '波多黎各',
        'PT' => '葡萄牙',
        'PW' => '帕劳',
        'PY' => '巴拉圭',
        'QA' => '卡塔尔',
        'RE' => '法属尼留旺岛',
        'RO' => '罗马尼亚',
        'RU' => '俄罗斯',
        'RW' => '卢旺达',
        'SA' => '沙特阿拉伯',
        'SC' => '塞舌尔',
        'SD' => '苏丹',
        'SE' => '瑞典',
        'SG' => '新加坡',
        'SH' => '圣赫勒拿',
        'SI' => '斯罗文尼亚',
        'SJ' => '斯瓦尔巴特和扬马延岛',
        'SK' => '斯洛伐克',
        'SL' => '塞拉利昂',
        'SM' => '圣马力诺',
        'SN' => '塞内加尔',
        'SO' => '索马里',
        'SR' => '苏里南',
        'ST' => '圣多美和普林西比',
        'SU' => '前苏联',
        'SV' => '萨尔瓦多',
        'SY' => '叙利亚',
        'SZ' => '斯威士兰',
        'Sb' => '所罗门群岛',
        'TC' => '特克斯和凯科斯群岛',
        'TD' => '乍得',
        'TF' => '法国南部领地',
        'TG' => '多哥',
        'TH' => '泰国',
        'TJ' => '塔吉克斯坦',
        'TK' => '托克劳群岛',
        'TM' => '土库曼斯坦',
        'TN' => '突尼斯',
        'TO' => '汤加',
        'TP' => '东帝汶',
        'TR' => '土尔其',
        'TT' => '特立尼达和多巴哥',
        'TV' => '图瓦卢',
        'TW' => '中国台湾省',
        'TZ' => '坦桑尼亚',
        'UA' => '乌克兰',
        'UG' => '乌干达',
        'UK' => '英国',
        'UM' => '美国海外领地',
        'US' => '美国',
        'UY' => '乌拉圭',
        'UZ' => '乌兹别克斯坦',
        'VA' => '梵蒂岗',
        'VC' => '圣文森特和格陵纳丁斯',
        'VE' => '委内瑞拉',
        'VG' => '英属维京群岛',
        'VI' => '美属维京群岛',
        'VN' => '越南',
        'VU' => '瓦努阿鲁',
        'WF' => '瓦里斯和福图纳群岛',
        'WS' => '西萨摩亚',
        'YE' => '也门',
        'YT' => '马约特岛',
        'YU' => '南斯拉夫',
        'ZA' => '南非',
        'ZM' => '赞比亚',
        'ZR' => '扎伊尔',
        'ZW' => '津巴布韦'
    );
    $code = strtoupper($code);
    $name = $index[$code];
    if (empty($name)) {
        return null;
    }
    return $name;
}