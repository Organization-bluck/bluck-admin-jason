<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/30
 * Time: 上午10:28
 */

namespace app\manager\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;

class Ctag extends BasicAdmin
{
    /**
     * 默认数据表
     * @var string
     */
    public $table = 'content_tag';

    public function index()
    {
        $this->title = '内容标签列表';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_del' => 0])->order('id desc');
        // 搜索条件
        foreach (['tname'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        return parent::_list($db);
    }

    /**
     * 添加信息标签
     */
    public function add()
    {
        $this->title = '添加信息标签';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑信息标签
     * @return string
     */
    public function edit()
    {
        $this->title = '编辑信息标签';
        return $this->_form($this->table, 'form');
    }

    /**
     * 信息标签禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("信息禁用成功！", '');
        }
        $this->error("信息禁用失败，请稍候再试！");
    }

    /**
     * 信息标签启用
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("信息启用成功！", '');
        }
        $this->error("信息启用失败，请稍候再试！");
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
     * 表单处理
     * @param $data
     */
    protected function _form_filter($data)
    {
        if ($this->request->isPost() && isset($data['ctitle'])) {
            $db = Db::table($this->table)->where('ctitle', $data['ctitle']);
            !empty($data['id']) && $db->where('id', 'neq', $data['id']);
            $db->count() > 0 && $this->error('此标签已存在！');
        }
    }

}