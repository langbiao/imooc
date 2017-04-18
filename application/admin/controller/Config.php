<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | Copyright (c) http://thinkphp.cn All rights reserved. 
// +----------------------------------------------------------------------
// | 官方网站: http://www.thinkphp.cn/
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | Author: bb
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BasicAdmin {

    /**
     * 当前默认数据模型
     * @var string
     */
    protected $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    protected $title = '网站参数配置';

    /**
     * 显示系统常规配置
     */
    public function index() {
        if (!$this->request->isPost()) {
            parent::_list($this->table);
        } else {
            $data = $this->request->post();
            foreach ($data as $key => $vo) {
                DataService::save($this->table, ['name' => $key, 'value' => $vo], 'name');
            }
            $this->success('数据修改成功！', '');
        }
    }

    /**
     * 文件存储配置
     */
    public function file() {
        $alert = [
            'type'    => 'info',
            'title'   => '操作提示',
            'content' => '文件引擎参数影响全局文件上传功能，请勿随意修改！'
        ];
        $this->assign('alert', $alert);
        $this->title = '文件存储配置';
        $this->index();
    }

    /**
     * 邮件账号配置
     */
    public function mail() {
        $this->title = '邮箱账号配置';
        $this->index();
    }

    /**
     * 短信通道账号配置
     */
    public function sms() {
        $this->title = '短信账号配置';
        $this->index();
    }

}
