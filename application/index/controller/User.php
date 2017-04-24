<?php
namespace app\index\controller;

use think\Db;

class User extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 个人主页
     */
    public function index()
    {
        return view();
    }

    /**
     * 我的课程
     */
    public function courses()
    {
        // 观看历史数据
        $his = Db::name('study_history')->where('sh_uid', '=', $this->uid)->order('sh_addtime', 'desc')->select();

        $his_data = [];
        foreach ($his as $value) {
            $his_data[$value['sh_svl_id']]['sh_svl_id'] = $value['sh_svl_id'];
            $his_data[$value['sh_svl_id']]['sh_addtime'] = $value['sh_addtime'];
            $his_data[$value['sh_svl_id']]['sh_id'] = $value['sh_id'];
        }

        $svl_ids = array_column($his, 'sh_svl_id');
        // 章节数据
        $video_list = Db::name('study_video_list')->where('svl_id', 'in', $svl_ids)->select();

        $sv_ids = array_column($video_list, 'svl_sv_id');
        // 视频数据
        $video = Db::name('study_video')->where('sv_id', 'in', $sv_ids)->select();

        $data = [];
        foreach ($video as $value) {
            $data[$value['sv_id']]['sv_name'] = $value['sv_name'];
            $data[$value['sv_id']]['sv_cover'] = $value['sv_cover'];
            $data[$value['sv_id']]['sv_id'] = $value['sv_id'];
        }

        $res = [];
        foreach ($video_list as $v) {
            $res[$v['svl_id']]['svl_id'] = $v['svl_id'];
            $res[$v['svl_id']]['svl_name'] = $v['svl_name'];
            $res[$v['svl_id']]['svl_chapter_no'] = $v['svl_chapter_no'];
            $res[$v['svl_id']]['year'] = date('Y', strtotime($his_data[$v['svl_id']]['sh_addtime']));
            $res[$v['svl_id']]['month'] = date('m月d日', strtotime($his_data[$v['svl_id']]['sh_addtime']));
            $res[$v['svl_id']]['id'] = $his_data[$v['svl_id']]['sh_id'];
            $res[$v['svl_id']]['video'] = $data[$v['svl_sv_id']];
        }
//dump($res);
        $this->assign('res', $res);
        return view();
    }

    // 删除历史记录
    public function delHis()
    {
        $id = $this->request->post('id');

        Db::name('study_history')->where('sh_id', '=', $id)->delete();
    }

    // 收藏记录
    public function collect()
    {
        $id = $this->request->post('id');
        $act = $this->request->post('act');

        if ($act == 1) {
            $data['sc_sv_id'] = $id ? $id : 0;
            $data['sc_uid'] = $this->uid;
            $data['sc_addtime'] = date('Y-m-d H:i:s');
            Db::name('study_collect')->insert($data);
            echo 1;
        } else {
            Db::name('study_collect')->where('sc_sv_id', '=', $id)->where('sc_uid', '=', $this->uid)->delete();
            echo 2;
        }
    }
   
}
