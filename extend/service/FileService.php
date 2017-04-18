<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | Copyright (c) http://thinkphp.cn All rights reserved. 
// +----------------------------------------------------------------------
// | 官方网站: http://www.thinkphp.cn/
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: bb
// +----------------------------------------------------------------------

namespace service;

use Exception;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Config;
use think\Log;

/**
 * 系统文件服务
 * Class FileService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/15 15:17
 */
class FileService {

    /**
     * 获取文件MINE信息
     * @param string $exts
     * @return string
     */
    static public function getFileMine($exts) {
        $_exts = is_string($exts) ? explode(',', $exts) : $exts;
        $_mines = [];
        $mines = Config::get('mines');
        foreach ($_exts as $_e) {
            if (isset($mines[strtolower($_e)])) {
                $_exinfo = $mines[strtolower($_e)];
                $_mines[] = is_array($_exinfo) ? join(',', $_exinfo) : $_exinfo;
            }
        }
        return join(',', $_mines);
    }

    /**
     * 获取文件当前URL地址
     * @param string $filename
     * @param string|null $storage
     * @return bool|string
     */
    static public function getFileUrl($filename, $storage = null) {
        if (self::hasFile($filename, $storage) === false) {
            return false;
        }
        switch (empty($storage) ? sysconf('storage_type') : $storage) {
            case 'local':
                return self::getBaseUriLocal() . $filename;
            case 'qiniu':
                return self::getBaseUriQiniu() . $filename;
        }
        return false;
    }

    /**
     * 获取服务器URL前缀
     * @return string
     */
    static public function getBaseUriLocal() {
        $request = request();
        $base = $request->root();
        $root = strpos($base, '.') ? ltrim(dirname($base), DS) : $base;
        if ('' != $root) {
            $root = '/' . ltrim($root, '/');
        }
        return ($request->isSsl() ? 'https' : 'http') . '://' . $request->host() . "{$root}/upload/";
    }

    /**
     * 获取七牛云URL前缀
     * @return string
     */
    static public function getBaseUriQiniu() {
        return (sysconf('storage_qiniu_is_https') ? 'https' : 'http') . '://' . sysconf('storage_qiniu_domain') . '/';
    }

    /**
     * 检查文件是否已经存在
     * @param string $filename
     * @param string|null $storage
     * @return bool
     */
    static public function hasFile($filename, $storage = null) {
        switch (empty($storage) ? sysconf('storage_type') : $storage) {
            case 'local':
                return file_exists(ROOT_PATH . 'public/upload/' . $filename);
            case 'qiniu':
                $auth = new Auth(sysconf('storage_qiniu_access_key'), sysconf('storage_qiniu_secret_key'));
                $bucketMgr = new BucketManager($auth);
                list($ret, $err) = $bucketMgr->stat(sysconf('storage_qiniu_bucket'), $filename);
                return $err === null;
        }
        return false;
    }

    /**
     * 根据Key读取文件内容
     * @param string $filename
     * @param string|null $storage
     * @return string|null
     */
    static public function readFile($filename, $storage = null) {
        switch (empty($storage) ? sysconf('storage_type') : $storage) {
            case 'local':
                if (file_exists(ROOT_PATH . 'public/upload/' . $filename)) {
                    return file_get_contents(ROOT_PATH . 'public/upload/' . $filename);
                }
            case 'qiniu':
                $auth = new Auth(sysconf('storage_qiniu_access_key'), sysconf('storage_qiniu_secret_key'));
                return file_get_contents($auth->privateDownloadUrl(self::getBaseUriQiniu() . $filename));
        }
        Log::error("通过{$storage}读取文件{$filename}的不存在！");
        return null;
    }

    /**
     * 根据当前配置存储文件
     * @param string $filename
     * @param string $bodycontent
     * @param string|null $file_storage
     * @return array|null
     */
    static public function save($filename, $bodycontent, $file_storage = null) {
        $type = empty($file_storage) ? sysconf('storage_type') : $file_storage;
        if (!method_exists(__CLASS__, $type)) {
            Log::error("保存存储失败，调用{$type}存储引擎不存在！");
            return null;
        }
        return self::$type($filename, $bodycontent);
    }

    /**
     * 文件储存在本地
     * @param string $filename
     * @param string $bodycontent
     * @return string
     */
    static public function local($filename, $bodycontent) {
        $filepath = ROOT_PATH . 'public/upload/' . $filename;
        try {
            !is_dir(dirname($filepath)) && mkdir(dirname($filepath), '0755', true);
            if (file_put_contents($filepath, $bodycontent)) {
                return [
                    'file' => $filepath,
                    'hash' => md5_file($filepath),
                    'key'  => "upload/{$filename}",
                    'url'  => pathinfo(request()->baseFile(true), PATHINFO_DIRNAME) . '/upload/' . $filename,
                ];
            }
        } catch (Exception $err) {
            Log::error('本地文件存储失败, ' . var_export($err, true));
        }
        return null;
    }

    /**
     * 七牛云存储
     * @param string $filename
     * @param string $bodycontent
     * @return string
     */
    static public function qiniu($filename, $bodycontent) {
        $auth = new Auth(sysconf('storage_qiniu_access_key'), sysconf('storage_qiniu_secret_key'));
        $token = $auth->uploadToken(sysconf('storage_qiniu_bucket'));
        $uploadMgr = new UploadManager();
        list($result, $err) = $uploadMgr->put($token, $filename, $bodycontent);
        if ($err !== null) {
            Log::error('七牛云文件上传失败, ' . var_export($err, true));
            return null;
        }
        $result['file'] = $filename;
        $result['url'] = self::getBaseUriQiniu() . $filename;
        return $result;
    }

}
