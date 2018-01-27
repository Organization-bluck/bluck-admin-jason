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
     * 添加音乐
     */
    public function add()
    {
        $this->title = '添加音乐信息';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑音乐
     * @return string
     */
    public function edit()
    {
        $this->title = '编辑音乐信息';
        return $this->_form($this->table, 'form');
    }
}