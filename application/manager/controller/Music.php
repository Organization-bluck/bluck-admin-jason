<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/14
 * Time: 下午10:17
 */

namespace app\manager\controller;

use controller\BasicAdmin;
use think\Db;

class Music extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'music_source';

    public function index()
    {
        $this->title = '音乐管理';
        $get = $this->request->get();
        $db = Db::name($this->table);
        foreach (['title'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "{$get[$key]}%");
        }

        return parent::_list($db);
    }

    /**
     * 分类添加
     */
    public function add()
    {
        $this->title = '添加诗人信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑分类
     * @return string
     */
    public function edit()
    {
        $this->title = '编辑诗人信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 表单处理
     * @param $data
     */
    protected function _form_filter($data)
    {
        if ($this->request->isPost() && isset($data['name'])) {
            $db = Db::name($this->table)->where('name', $data['name']);
            !empty($data['id']) && $db->where('id', 'neq', $data['id']);
            $db->count() > 0 && $this->error('此诗人已存在！');
        }
    }
}