<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/23
 * Time: 上午11:50
 */

namespace app\manager\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;
use think\Exception;

class Content extends BasicAdmin
{
    /**
     * 默认数据表
     * @var string
     */
    public $table = 'content';

    /**
     * 列表
     */
    public function index()
    {
        $this->title = '内容信息列表';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_del' => 0])->order('id desc');
        // 搜索条件
        if (isset($get['title']) && $get['title'] !== '') {
            $db->where('title', 'like', "%{$get['title']}%");
        }
        if(!empty($get['cc_id'])) {
            $db->where('cc_id', 'eq', $get['cc_id']);
        }

        $this->assign('content_category', Db::table('content_category')->where(['status'=>1,'is_del'=>0])->select());
        return parent::_list($db);
    }

    /**
     * 添加信息
     */
    public function add()
    {
        if($this->request->isPost()) {
            try{
                Db::startTrans();
                //添加主信息
                $data = $this->request->post();
                $insert_id = Db::table($this->table)->insert($data, false, true);
                if(!$insert_id) {
                    throw new Exception('添加信息失败');
                }
                if(empty($data['tid']) && !is_array($data['tid'])) {
                    throw new Exception('请选择标签');
                }
                $tag_data = array_map(function ($v) use($insert_id) {
                    return [
                        'cid' => $insert_id,
                        'tid' => $v,
                    ];
                }, $data['tid']);
                if(!Db::table('content_tag_info')->insertAll($tag_data)) {
                    throw new Exception('添加标签失败');
                }
                Db::commit();
                $this->success('操作成功', '');
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
        $this->title = '添加信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑信息
     * @return string
     */
    public function edit()
    {
        if($this->request->isPost()) {
            try{
                Db::startTrans();
                //添加主信息
                $data = $this->request->post();
                $insert_id = Db::table($this->table)->update($data);
                if(!$insert_id) {
                    throw new Exception('更新信息失败');
                }
                if(empty($data['tid']) && !is_array($data['tid'])) {
                    throw new Exception('请选择标签');
                }
                if(!Db::table('content_tag_info')->where(['cid' => $data['id']])->delete()) {
                    throw new Exception('删除标签失败');
                }
                $tag_data = array_map(function ($v) use($insert_id) {
                    return [
                        'cid' => $insert_id,
                        'tid' => $v,
                    ];
                }, $data['tid']);
                if(!Db::table('content_tag_info')->insertAll($tag_data)) {
                    throw new Exception('添加标签失败');
                }
                Db::commit();
                $this->success('操作成功', '');
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
        $this->title = '编辑信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 删除
     */
    public function del() {
        if (DataService::update($this->table)) {
            $this->success("删除成功！", '');
        }
        $this->error("删除失败，请稍候再试！");
    }

    /**
     * 信息禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("信息禁用成功！", '');
        }
        $this->error("信息禁用失败，请稍候再试！");
    }

    /**
     * 信息启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("信息启用成功！", '');
        }
        $this->error("信息启用失败，请稍候再试！");
    }

//    public function ajaxGetInfo()
//    {
//        try{
//            $id = input('get.id');
//            if(!$id) {
//                throw new Exception('id不存在');
//            }
//
//            $info = Db::table('content_tag_info cti')
//                ->field('ct.tname')
//                ->join('content_tag ct'. 'ct.id = cti.tid', 'LEFT')
//                ->where(['ct.status' => ['eq', 1], 'ct.is_del' => ['eq', 0]])
//                ->select();
//
//            echo json_encode(['code' => 0, 'data' => json_encode($info)]);
//        } catch (Exception $e) {
//            echo json_encode(['code' => -1, 'msg' => $e->getMessage()]);
//        }
//        exit;
//    }

    /**
     * 表单处理
     * @param $data
     */
    protected function _form_filter($data)
    {
        if($data) {
            $this->assign('content_tag_info', Db::table('content_tag_info')->where(['cid' => $data['id']])->column('tid'));
        }
        $this->assign('content_category', Db::table('content_category')->where(['status'=>1,'is_del'=>0])->select());
        $this->assign('content_tag', Db::table('content_tag')->where(['status'=>1,'is_del'=>0])->select());
    }
}