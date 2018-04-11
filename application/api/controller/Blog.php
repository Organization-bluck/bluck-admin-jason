<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/4/11
 * Time: 下午10:19
 */

namespace app\api\controller;


use think\Db;
use think\Exception;

class Blog extends Base
{

    public function getHomePage()
    {
        try{
            $list = Db::table('content')
                ->field('id, title, content, create_at')
                ->where(['status' => 1, 'is_del' => 0])
                ->limit(10)
                ->order('create_at desc, sort desc')
                ->select();
            $content_ids = array_column($list, 'id');

            $tag_list = Db::table('content_tag t')
                ->field('t.id, ti.cid, t.tname')
                ->join('content_tag_info ti', 'ti.tid = t.id')
                ->where(['status' => 1, 'is_del' => 0, 'ti.cid' => ['IN', $content_ids]])
                ->select();
            $tag_list_info = [];
            if($tag_list) {
                foreach ((array)$tag_list as $v) {
                    $tag_list_info[$v['cid']][] = $v;
                }
            }
            array_walk($list, function (&$v) use($tag_list_info) {
                if(!empty($tag_list_info[$v['id']])) {
                    $v['tag_list'] = $tag_list_info[$v['id']];
                } else {
                    $v['tag_list'] = [];
                }
            });
            $this->data = [
                'list'      => $list,
                'category'  => Db::table('content_category')->field('id, ctitle')->where(['status' => 1, 'is_del' => 0])->select(),
            ];
        } catch (Exception $e) {
            $this->response(-1, $e->getMessage());
        }
    }

    public function getList()
    {
        try{
            $cat_id = input('get.cid/d', 0);
            if(!$cat_id) {
                throw new Exception('分类不存在');
            }

            $this->page_size = input('get.pagesize/d', $this->page_size);

            $list = Db::table('content')
                ->field('id, title, content, create_at')
                ->limit($this->_getStartCount(), $this->page_size)
                ->order('create_at desc, sort desc')
                ->where(['status' => 1, 'is_del' => 0, 'cc_id' => $cat_id])
                ->select();

            $content_ids = array_column($list, 'id');

            $tag_list = Db::table('content_tag t')
                ->field('t.id, ti.cid, t.tname')
                ->join('content_tag_info ti', 'ti.tid = t.id')
                ->where(['status' => 1, 'is_del' => 0, 'ti.cid' => ['IN', $content_ids]])
                ->select();
            $tag_list_info = [];
            if($tag_list) {
                foreach ((array)$tag_list as $v) {
                    $tag_list_info[$v['cid']][] = $v;
                }
            }
            array_walk($list, function (&$v) use($tag_list_info) {
                if(!empty($tag_list_info[$v['id']])) {
                    $v['tag_list'] = $tag_list_info[$v['id']];
                } else {
                    $v['tag_list'] = [];
                }
            });
            $this->data = $list;
        } catch (Exception $e) {
            $this->response(-1, $e->getMessage());
        }
    }

    public function getDetail()
    {
        try{
            $id = input('get.id/d');
            if(!$id) {
                throw new Exception('信息不存在');
            }
            $info = Db::table('content')
                ->field('id, title, content, create_at')
                ->where(['status' => 1, 'is_del' => 0, 'id' => $id])
                ->find();
            $info['tag_list'] = Db::table('content_tag t')
                ->field('t.id, ti.cid, t.tname')
                ->join('content_tag_info ti', 'ti.tid = t.id')
                ->where(['status' => 1, 'is_del' => 0, 'ti.cid' => $info['id']])
                ->select();
            $this->data = $info;
        } catch (Exception $e) {
            $this->response(-1, $e->getMessage());
        }
    }
}