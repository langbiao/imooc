<?php
namespace app\index\controller;

use think\Db;
use think\Controller;

class Base extends Controller
{
    public $uid = 0;
    public function _initialize()
    {
        if ($_SESSION['user']) {
            $is_login = 1;
            $this->getHistory($_SESSION['user']['id']);
            $this->assign('is_login', $is_login);
            $this->assign('user', $_SESSION['user']);
        }
//        dump($_SESSION['user']);die;

        $this->uid = $_SESSION['user']['id'] ? $_SESSION['user']['id'] : 0;

        $this->assign('title', '慕课网');
        $this->assign('keywords', '慕课网');
        $this->assign('description', '慕课网');
    }

    private function getHistory($uid)
    {
        $his = Db::name('study_history')->where('sh_uid', '=', $uid)->order('sh_addtime', 'desc')->find();

        // 视频数据
        $video = Db::name('study_video')->where('sv_id', '=', $his['sh_sv_id'])->find();

        // 章节数据
        $chapter = Db::name('study_video_list')->where('svl_id', '=', $his['sh_svl_id'])->where('svl_pid', '<>', 0)->find();
        
        $this->assign('his_video', $video);
        $this->assign('his_chapter', $chapter);
    }

}
