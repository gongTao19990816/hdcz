<?php

namespace app\api\controller;

use think\exception\ValidateException;
use think\facade\Filesystem;
use think\facade\Validate;
use think\Image;


class Base extends Common
{
    protected $noNeedLogin = ["base/upload", "base/captcha"];

    /**
     * @api {post} /Base/new_upload 01、文件上传
     * @apiGroup Base
     * @apiVersion 1.0.0
     * @apiDescription  文件上传，只能传后台设定的文件
     */
    public function new_upload($file = null)
    {
        if (!$_FILES) throw new ValidateException('上传验证失败');
        $file = $this->request->file(array_keys($_FILES)[0]);
        $upload_config_id = $this->request->param('upload_config_id', '', 'intval');

        if (!Validate::fileExt($file, config('my.api_upload_ext')) || !Validate::fileSize($file, config('my.api_upload_max'))) {
            throw new ValidateException('上传验证失败');
        }
        $upload_hash_status = !is_null(config('my.upload_hash_status')) ? config('my.upload_hash_status') : true;
        $fileinfo = $upload_hash_status ? db("file")->where('hash', $file->hash('md5'))->find() : false;
        if ($upload_hash_status && $fileinfo) {
            $url = $fileinfo['filepath'];
            return json(['status' => config('my.errorCode'), 'msg' => '重复素材']);
        } else {
            $url = $this->new_up($file, $upload_config_id);
            return json(['status' => config('my.successCode'), 'data' => $url, 'imageurl' => $this->request->domain() . $url]);
        }

    }

    protected function new_up($file, $upload_config_id)
    {
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
            abort(config('my.error_log_code'), $e->getMessage());
        }

        $upload_hash_status = is_null(config('my.upload_hash_status')) || config('my.upload_hash_status');
        $upload_hash_status && db('file')->insert(['filepath' => $url, 'hash' => $file->hash('md5'), 'create_time' => time()]);

        return $url;
    }

    /**
     * @api {post} /Base/upload 01、文件上传
     * @apiGroup Base
     * @apiVersion 1.0.0
     * @apiDescription  文件上传，只能传后台设定的文件
     */
    public function upload()
    {
        if (!$_FILES) throw new ValidateException('上传验证失败');
        $file = $this->request->file(array_keys($_FILES)[0]);
        $upload_config_id = $this->request->param('upload_config_id', '', 'intval');

        if (!Validate::fileExt($file, config('my.api_upload_ext')) || !Validate::fileSize($file, config('my.api_upload_max'))) {
            throw new ValidateException('上传验证失败');
        }
        $upload_hash_status = !is_null(config('my.upload_hash_status')) ? config('my.upload_hash_status') : true;
        /*$fileinfo = $upload_hash_status ? db("file")->where('hash', $file->hash('md5'))->find() : false;
        if ($upload_hash_status && $fileinfo) {
            $url = $fileinfo['filepath'];
            return json(['status' => config('my.errorCode'), 'msg' => '重复素材']);
        } else {*/
            $url = $this->up($file, $upload_config_id);
            return json(['status' => config('my.successCode'), 'data' => $url, 'imageurl' => $this->request->domain() . $url]);
        //}
    }

    protected function up($file, $upload_config_id)
    {
        try {
            if (config('my.oss_status')) {
                $url = \utils\oss\OssService::OssUpload(['tmp_name' => $file->getPathname(), 'extension' => $file->extension()]);
            } else {
                $info = Filesystem::disk('temp')->putFile(\utils\oss\OssService::setFilepath(), $file, 'uniqid');


                $cand = '/www/wwwroot/copy_file --src="' . app()->getRootPath() . 'public/upload_temp/' . $info . '" --output="' . app()->getRootPath() . 'public/uploads/hdcz/' . $info . '"';
                system($cand);
                unlink(app()->getRootPath() . 'public/upload_temp/' . $info);


                $url = \utils\oss\OssService::getApiFileName(basename($info));
                if ($upload_config_id && !config('my.oss_status') && in_array(pathinfo($info)['extension'], ['jpg', 'png', 'gif', 'jpeg', 'bmp'])) {
                    $this->thumb(config('my.upload_dir') . '/' . $info, $upload_config_id);
                }
            }
        } catch (\Exception $e) {
            abort(config('my.error_log_code'), $e->getMessage());
        }

        $upload_hash_status = !is_null(config('my.upload_hash_status')) ? config('my.upload_hash_status') : true;
        $upload_hash_status && db('file')->insert(['filepath' => $url, 'hash' => $file->hash('md5'), 'create_time' => time()]);

        return $url;
    }

    //生成缩略图或水印
    private function thumb($imagesUrl, $upload_config_id)
    {
        $configInfo = db("upload_config")->where('id', $upload_config_id)->find();
        if ($configInfo) {
            $image = Image::open($imagesUrl);
            $targetimages = $imagesUrl;

            //当设置不覆盖生成新的文件名
            if (!$configInfo['upload_replace']) {
                $fileinfo = pathinfo($imagesUrl);
                $targetimages = $fileinfo['dirname'] . '/s_' . $fileinfo['basename'];
                copy($imagesUrl, $targetimages);
            }

            //生成缩略图
            if ($configInfo['thumb_status']) {
                $image->thumb($configInfo['thumb_width'], $configInfo['thumb_height'], $configInfo['thumb_type'])->save($targetimages);
            }

            $config = db("config")->column('data', 'name');

            //生成水印
            if (file_exists('.' . $config['water_logo']) && $config['water_status'] && $config['water_position']) {
                $image->water('.' . $config['water_logo'], $config['water_position'])->save($targetimages);
            }
        }
    }


    /**
     * @api {get} /Base/captcha 02、图片验证码地址
     * @apiGroup Base
     * @apiVersion 1.0.0
     * @apiDescription  图片验证码
     * @apiSuccessExample {json} 01 调用示例
     * <img src="http://xxxx.com/Base/captcha" onClick="this.src=this.src+'?'+Math.random()" alt="点击刷新验证码">
     */
    public function captcha()
    {
        ob_clean();
        return captcha();
    }

}

