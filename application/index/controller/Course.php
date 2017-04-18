<?php
namespace app\index\controller;

use think\Db;

class Course extends Base
{
    private $uid = 0;
    public function _initialize()
    {
        parent::_initialize();

        $this->uid = 0;
    }

    // 课程列表页
    public function list()
    {

        // 分类
        $category = Db::name('study_category')->order('sc_sort')->select();

        // 分类id
        $sc_id = array_column($category, 'sc_id');

        // 分类下的课程
        $course = Db::name('study_course')->where('sc_c_id', 'in', $sc_id)->order('sc_sort')->select();

//        echo "<pre>";
//        print_r($data);die;

        $t = $this->request->get('t', '0');
        $c = $this->request->get('c', '0');
        $x = $this->request->get('x', '');
        $keyword = $this->request->get('keyword', '');
        $p = $this->request->get('page', '');

        $model = Db::name('study_video');

        if ($t) {
            $model->where('sv_cat_id', '=', $t);
            $this->assign('t', $t);
        }
        if ($c) {
            $model->where('sv_co_id', '=', $c);
            $this->assign('c', $c);
        }
        if ($keyword) {
            $model->whereLike('sv_name', "%{$keyword}%");
            $this->assign('keyword', $keyword);
        }
        switch ($x) {
            case 'new':
                $model->order('sv_addtime', 'desc');
                break;
            case 'hot':
                $model->order('sv_click', 'desc');
                break;
            case 'score':
                $model->order('sv_score', 'desc');
                break;
            default:
                $model->order('sv_addtime', 'desc');
                break;
        }
        $this->assign('x', $x ? $x : 'new');

        $list = $model->paginate(config('list_rows'),false,array('query' => $this->request->param()));

        // 获取分页显示
        $page = $list->render();

        $totalPage = ceil($model->count()/config('paginate.list_rows'));

        if ($p <= 0) {
            $p = 1;
        }
        if ($p > $totalPage) {
            $p = $totalPage;
        }

        $html1 = htmlspecialchars_decode(strip_tags($page, '<a><span>'));
        $html1 = preg_replace (['/<span>(\d+)<\/span>/', '/<a\s+href=\"(.*?)\">(\d+)<\/a>/'] , ['<a href="javascript:void(0);" class="active text-page-tag">\1</a>', '<a href="\1" class="text-page-tag">\2</a>'] , $html1);
        $html1 = str_replace(['&laquo;', '&raquo;'], ['上一页', '下一页'], $html1);

        $this->assign('list', $list);
        $this->assign('page', $html1);
        $this->assign('category', $category);
        $this->assign('course', $course);
        return view();
    }

    // 课程详情页
    public function detail()
    {
        $d = $this->request->get('d', '0');
        $act = $this->request->get('act', 'detail');
        if (empty($d)) {
            $this->redirect('/');
        }
        // 学习人数+1
        Db::name('study_video')->where('sv_id', '=', $d)->setInc('sv_study_num');

        // 点击率+1
        Db::name('study_video')->where('sv_id', '=', $d)->setInc('sv_click');

        // 是否收藏
        $is_collect = Db::name('study_collect')->where('sc_sv_id', '=', $d)->where('sc_uid', '=', $this->uid)->find();

        // 视频数据
        $video = Db::name('study_video')->where('sv_id', '=', $d)->find();

        // 分类数据
        $category = Db::name('study_category')->where('sc_id', '=', $video['sv_cat_id'])->find();

        // 课程数据
        $course = Db::name('study_course')->where('sc_id', '=', $video['sv_co_id'])->find();

        // 章节数据
        $chapter = Db::name('study_video_list')->where('svl_sv_id', '=', $d)->where('svl_pid', '=', 0)->select();
        $chapter_ids = array_column($chapter, 'svl_id');
        $chapter_sub = Db::name('study_video_list')->where('svl_pid', 'in', $chapter_ids)->order('svl_sort')->select();

        if ($act == 'detail') {

            $data = [];
            // 格式化章节数据
            foreach ($chapter as $value) {
                $data[$value['svl_id']]['id'] = $value['svl_id'];
                $data[$value['svl_id']]['name'] = $value['svl_name'];
                $data[$value['svl_id']]['title'] = $value['svl_title'];

                foreach ($chapter_sub as $v) {
                    if ($v['svl_pid'] == $value['svl_id']) {
                        $tmp['id'] = $v['svl_id'];
                        $tmp['name'] = $v['svl_name'];
                        $data[$value['svl_id']]['child'][] = $tmp;
                    }
                }
            }
            $this->assign('data', $data);
        }
        $this->assign('first', $chapter_sub[0]['svl_id']);
        if ($act == 'comment') {
            // 评论数据
            $comment = Db::name('study_comment')->where('sc_sv_id', '=', $d)->order('sc_addtime', 'desc')->select();

            // 章节id
            $chapter_ids = array_column($comment, 'sc_svl_id');
            // 章节数据
            $res = Db::name('study_video_list')->where('svl_id', 'in', $chapter_ids)->where('svl_pid', '<>', 0)->field('svl_id, svl_name')->select();

            $chapter = [];
            foreach ($res as $cha) {
                $chapter[$cha['svl_id']] = $cha['svl_name'];
            }

            $comments = [];
            foreach ($comment as $com) {
                $tmp['username'] ='飞粉4350286';
                $tmp['img'] = 'http://img.mukewang.com/545868250001157802200220-40-40.jpg';
                $tmp['content'] = $com['sc_content'];
                $tmp['zan'] = $com['sc_zan'];
                $tmp['addtime'] = $com['sc_addtime'];
                $tmp['from'] = $chapter[$com['sc_svl_id']];
                $tmp['from_id'] = $com['sc_svl_id'];
                $comments[] = $tmp;
            }

            $this->assign('comments', $comments);
        }

        $this->assign('category', $category);
        $this->assign('course', $course);
        $this->assign('video', $video);
        $this->assign('act', $act);
        $this->assign('is_collect', $is_collect ? 1 : 0);

        return view();
    }

    // 收藏
    public function collect()
    {
        $id = $this->request->post('id', '0');
        $act = $this->request->post('act', '1');

        if ($act ==1) {
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

    // 课程点赞
    public function videozan()
    {
        $id = $this->request->post('id', '0');

        // 学习人数+1
        Db::name('study_video')->where('sv_id', '=', $id)->setInc('sv_zan');
    }

    public function video()
    {
        $v = $this->request->get('v', 0);
        // 章节数据
        $chapter = Db::name('study_video_list')->where('svl_id', '=', $v)->find();
        // 课程数据
        $video = Db::name('study_video')->where('sv_id', '=', $chapter['svl_sv_id'])->find();

        // 评论数据
        $comment = Db::name('study_comment')->where('sc_svl_id', '=', $chapter['svl_id'])->order('sc_addtime', 'desc')->select();

        $comments = [];
        foreach ($comment as $com) {
            $tmp['username'] ='飞粉4350286';
            $tmp['img'] = 'http://img.mukewang.com/545868250001157802200220-40-40.jpg';
            $tmp['content'] = $com['sc_content'];
            $tmp['zan'] = $com['sc_zan'];
            $tmp['addtime'] = $com['sc_addtime'];
            $tmp['from_id'] = $com['sc_svl_id'];
            $comments[] = $tmp;
        }

        $this->assign('comments', $comments);

        // 获取下一节数据
        $next_chapter = Db::name('study_video_list')
            ->where('svl_pid', '<>', 0)
            ->where('svl_id', '>', $v)
            ->order('svl_addtime', 'desc')
            ->limit(1)
            ->find();

        $this->assign('next_chapter', $next_chapter);
        $this->assign('chapter', $chapter);
        $this->assign('video', $video);
        $this->assign('v', $v);

        return view();
    }

    // 评论
    public function comment()
    {
        $id = $this->request->post('id', 0);
        $v_id = $this->request->post('v_id', 0);
        $content = $this->request->post('content', 0);

        if (empty($id) || empty($v_id) || empty($content)) {
            return ['code'=>1001, 'msg'=>'ok', 'data'=>[]];
        }
        $data['sc_sv_id'] = $v_id;
        $data['sc_svl_id'] = $id;
        $data['sc_uid'] = $this->uid;
        $data['sc_zan'] = 0;
        $data['sc_content'] = $content;
        $data['sc_addtime'] = date('Y-m-d H:i:s');

        Db::name('study_comment')->insert($data);

        $data['username'] = '飞粉4350286';
        $data['img'] = 'http://img.mukewang.com/5333a2b70001a5a802000200-40-40.jpg';

        return ['code'=>1000, 'msg'=>'ok', 'data'=>$data];
    }
}
