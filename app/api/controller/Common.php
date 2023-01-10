<?php

namespace app\api\controller;

use app\api\middleware\Auth;
use think\App;
use think\Exception;
use think\exception\FuncNotFoundException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Log;
use think\facade\Validate;
use think\File;
use think\Response;

class Common
{


    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = ["base/captcha"];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 权限Auth
     * @var Auth
     */
    protected $auth = null;
    protected $request;
    protected $app;

    protected $_data;
    protected $successCode;
    protected $errorCode;


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
    protected function returnJsonp($code = 0, $msg = '', $data = '', int $wait = 3, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];


        $response = json($result);

        throw new HttpResponseException($response);
    }

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        //exit('禁止访问');
        $this->app = $app;
        $this->request = $this->app->request;
        $this->_data = $this->request->param();

        //判断是否是json请求
        if (!$this->request->isJson()) {
            $this->_data = $this->request->param();
        } else {
            $this->_data = json_decode(file_get_contents('php://input'), true);
        }

        $this->_data['timestamp'] = date('Y-m-d H:i:s', time());

        $this->successCode = config('my.successCode');
        $this->errorCode = config('my.errorCode');

        if (config('my.api_input_log')) {
            Log::info('接口地址：' . request()->pathinfo() . ',接口输入：' . print_r($this->_data, true));
        }


        $this->auth = Auth::instance($this->request);

        $controllername = $this->request->controller();
        $actionname = strtolower($this->request->action());

        // token
        $token = $this->request->server('HTTP_TOKEN', $this->request->header('Authorization', ""));
        // var_dump($token);die;
        if ($token) {
            $jwt = Jwt::getInstance();
            $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))->setSecrect(config('my.jwt_secrect'))->setToken($token);
            // var_dump($jwt);die;
            if ($jwt->decode()->getClaim('exp') < time()) {
                $this->error('token过期', '', config('my.jwtExpireCode'));
            }
            $this->request->uid = $jwt->decode()->getClaim('uid');
        }

        $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        // 设置当前请求的URI
        $this->auth->setRequestUri($path);
//        $this->auth->logout();

        // 检测是否需要验证登录
        if (!$this->auth->match($this->noNeedLogin)) {
            //初始化
            if (!$this->auth->init($token)) {
                $this->error($this->auth->getError(), '', $this->errorCode);
            }
            //检测是否登录
            if (!$this->auth->isLogin()) {
                $this->error('请先登录', '', config('my.jwtErrorCode'));
//                throw new ValidateException('请先登录');
            }
            // 判断是否需要验证权限
            if (!$this->auth->match($this->noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->auth->check($path)) {
                    $this->error('你没有权限', '', $this->errorCode);
                }
            }
        } else {
            // 如果有传递token才验证是否登录状态
            if ($token) {
                $this->auth->init($token);
            }
        }
    }

    function common_upload(File $file)
    {
        $upload_config_id = $this->request->param('upload_config_id', '', 'intval');

        if (!Validate::fileExt($file, config('my.api_upload_ext')) || !Validate::fileSize($file, config('my.api_upload_max'))) {
            throw new \Exception('上传验证失败');
        }
        $upload_hash_status = !is_null(config('my.upload_hash_status')) ? config('my.upload_hash_status') : true;
        $fileinfo = $upload_hash_status && db("file")->where('hash', $file->hash('md5'))->find();
        if ($upload_hash_status && $fileinfo) {
            throw new \Exception('重复素材');
        }
        try {
            if (config('my.oss_status')) {
                $url = \utils\oss\OssService::OssUpload(['tmp_name' => $file->getPathname(), 'extension' => $file->extension()]);
            } else {
                $info = Filesystem::disk('temp')->putFile(\utils\oss\OssService::setFilepath(), $file, 'uniqid');


                $cand = '/www/wwwroot/copy_file --src="' . app()->getRootPath() . 'public/upload_temp/' . $info . '" --output="' .
                    app()->getRootPath() . 'public/uploads/uploadfiles/' . $info . '"';
                system($cand);
                unlink(app()->getRootPath() . 'public/upload_temp/' . $info);


                $url = \utils\oss\OssService::getNewApiFileName(basename($info));
                if ($upload_config_id && !config('my.oss_status') && in_array(pathinfo($info)['extension'], ['jpg', 'png', 'gif', 'jpeg', 'bmp'])) {
                    $this->thumb(config('my.new_upload_dir') . '/' . $info, $upload_config_id);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $upload_hash_status = is_null(config('my.upload_hash_status')) || config('my.upload_hash_status');
        $upload_hash_status && db('file')->insert(['filepath' => $url, 'hash' => $file->hash('md5'), 'create_time' => time()]);

        return $url;
    }

    /**
     * tp官方数组查询方法废弃，数组转化为现有支持的查询方法
     * @param array $data 原始查询条件
     * @param \think\Model $model 需要查询的模型
     * @return array
     */
    function apiFormatWhere($data, $model = null)
    {
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

        if ($model) {
            // 判断后台账号id字段是否存在
            // 判断当前登录账号是否超管
            // 查询带入后台账号id
            $tablename = (new $model())->getTable();
            $fields = (new $model())->getTableFields();
            if (in_array('api_user_id', $fields)) {
                if (!$this->auth->isSuperAdmin()) {
                    $where[] = [$tablename . '.api_user_id', '=', $this->request->uid];
                }
            }
        }
        return $where;
    }

    /**
     * 操作成功返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($msg = '', $data = null, $code = 1, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型，支持json/xml/jsonp
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断
        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : 'json');

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

    //处理token
    /*
     * token 用户token
     * type:0 接收的参数-登录后的token；1：游客固定的token；2：随机取的token
     */
    protected function doToken($token = '', $type = 0)
    {
        if ($type == 0) {
//             $token = trim(I('token'));
            if (empty($token) || $token == '' || $token == null) $this->returnJsonp('-1', 'token未取到值');;
        } else if ($type == 1) {
            $user_token = db('user_token_list')->field('values')->where(['id' => 1])->find();
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

    //根据分类id查询分组名称


    /**
     * @param number $user_id
     */
    protected function getHttpProxy($user_id = 0)
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

    /**
     * 生成token
     * @param uid 用户UID
     */
    protected function setToken($uid)
    {
        $jwt = Jwt::getInstance();
        $jwt->setIss(config('my.jwt_iss'))->setAud(config('my.jwt_aud'))
            ->setSecrect(config('my.jwt_secrect'))->setExpTime(config('my.jwt_expire_time'));
        $token = $jwt->setUid($uid)->encode()->getToken();
        return $token;
    }

    // 根据分类id查询分组名称
    protected function grouping_name($typecontrol_id)
    {
        $grouping_id = db('typecontrol')->where('typecontrol_id', $typecontrol_id)->value('grouping_id');
        $name = db('grouping')->where('grouping_id', $grouping_id)->value('grouping_name');
        return $name;
    }

    //接口返回
    protected function ajaxReturn($status, $msg, $data = '', $token = '')
    {
        $res = ['status' => $status, 'msg' => $msg];
        $res['data'] = $data;
        !empty($token) && $res['token'] = $token;
        return json($res);
    }

    public function __call($method, $args)
    {
        throw new FuncNotFoundException('方法不存在', $method);
    }

    //对外访问接口
    protected function doHttpPost($url, $data)
    {
        //var_dump($data);var_dump($url);die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;

    }

    protected function downloadImage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        // var_dump($url);var_dump($file);
        return $this->saveAsImage($url, $file);

    }

    protected function saveAsImage($url, $file)
    {
        $filename = 'pic' . time() . '.png';
        $path = 'uploads/xiazai/';
        $fullpath = 'uploads/xiazai' . '/' . $filename;
        $resource = fopen($fullpath, 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $fullpath;
    }

//随机游客token
    protected function randomToken()
    {
        $lists = db('user_token_list')->orderRaw('rand()')->limit(1)->select()->toArray();
        if ($lists) {
            throw new FuncNotFoundException('游客token获取失败');
        } else {
            return json_decode($lists, true);
        }
    }

    //取接口授权
    public function getAccessInfo()
    {
        $access_token = db('information')->where(['information_id' => 1])->value("access_token");
        if (empty($access_token) || trim($access_token) == '') $this->returnJsonp('-1', '未设置接口请求授权');
        return $access_token;
    }

    /**
     * 发起POST请求
     */
    protected function post($url, $msg)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $msg);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);
        return json_decode($res, true);
    }

    /**
     * $url 接口地址地址
     * $file 要推送的文件的路径
     */
    protected function post_files($url, $file, $uid, $text)
    {
        $data = [];
        $file = realpath($file);
        $data = ['video' => new \CURLFile($file)];
        $data['user_id'] = $uid;
        $data['text'] = $text;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    /*
     * 下载文件
     */
    //下载地址   储存地址
    protected function dlfile($file_url, $save_to)
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
            if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) $this->returnJsonp('-21', '代理链接不正确');
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

//TT-请求接口
    protected function doHttpPosts($url, $data, $access = '', $proxy = false)
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
            if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) $this->returnJsonp('-21', '代理链接不正确');
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

    protected function CurlRequest($url, $data = null, $header = null, $proxy = false)
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
            // HTTP 代理通道
            curl_setopt($ch, CURLOPT_PROXY, 'http://127.0.0.1');
            // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
            curl_setopt($ch, CURLOPT_PROXYPORT, '8888');
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
            if (strpos($proxy, 'http://') === false && strpos($proxy, 'https://') === false && strpos($proxy, 'socks5://') === false) $this->returnJsonp('-21', '代理链接不正确');
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

    //TT接口请求
    protected function getResToTT($respone_arr = [], $http_proxy = '')
    {

        if (empty($respone_arr)) $this->returnJsonp('-14', '接口未返回');
        $post_get_type = $respone_arr['request']['method'];
        $tt_url = $respone_arr['request']['url'];
        $headers = $respone_arr['request']['headers'];
        $header = [];
        //封装header
        foreach ($headers as $k => $v) {
            $header[] = $k . ':' . $v;
        }
        if (strpos($http_proxy, 'http://') === false && strpos($http_proxy, 'https://') === false && strpos($http_proxy, 'socks5://') === false) $this->returnJsonp('-21', '代理链接不正确');
        $proxy_temp = explode('//', $http_proxy);
        $proxy_pass_arr = explode('@', $proxy_temp[1]);
        $proxy_ipport_arr = explode(':', $proxy_pass_arr[0]);

        //TT接口请求
        if ($post_get_type == 'POST') {
            $body = $respone_arr['request']['body'];
            $body_data = base64_decode($body);
//            die(rawurldecode($body_data));
            $body_data = str_replace('+', ' ', $body_data);
            $tt_respone_json = $this->HttpsGetProxy($tt_url, $proxy_ipport_arr[0], $proxy_ipport_arr[1], $header, $proxy_pass_arr[0], $body_data);
        } else {
            $tt_respone_json = $this->HttpsGetProxy($tt_url, $proxy_ipport_arr[0], $proxy_ipport_arr[1], $header, $proxy_pass_arr[0]);
        }
        return $tt_respone_json;
    }

    //TT-代理请求
    function HttpsGetProxy($url, $host, $port, $header = '', $is_passwd = '', $data = '')
    {
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // false 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // 设置为 1 是检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。 设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header头
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        // true 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // HTTP 代理通道
        curl_setopt($ch, CURLOPT_PROXY, $host);
        // 代理服务器的端口。端口也可以在CURLOPT_PROXY中设置。
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        // HTTP 代理连接的验证方式。当前仅仅支持 CURLAUTH_BASIC和CURLAUTH_NTLM。
        curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
        if ($data !== '' || $data != null || !empty($data)) {
            //设置POST请求方式
            @curl_setopt($ch, CURLOPT_POST, true);
            //设置POST的数据包
            @curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //     // SOCKS5 代理通道
        if (!empty($is_passwd)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $is_passwd);
        }


        // true 时将会根据服务器返回 HTTP 头中的 "Location: " 重定向。（注意：这是递归的，"Location: " 发送几次就重定向几次，除非设置了 CURLOPT_MAXREDIRS，限制最大重定向次数。）
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //执行并获取HTML文档内容
        $output = curl_exec($ch);


        //释放curl句柄
        curl_close($ch);
        //获得的数据
        return $output;
    }


    //国家识别
    protected function transCountryCode($code)
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


}
