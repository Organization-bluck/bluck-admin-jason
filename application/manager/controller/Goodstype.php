<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/23
 * Time: 下午2:07
 */

namespace app\manager\controller;


use controller\BasicAdmin;
use service\DataService;
use think\Db;

class Goodstype extends BasicAdmin
{
    /**
     * 默认数据表
     * @var string
     */
    public $table = 'goods_type';

    /**
     * 列表
     */
    public function index()
    {
        $this->title = '商品类型信息列表';
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
     * 添加商品类型
     */
    public function add()
    {
        $this->title = '添加商品类型';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑商品类型
     * @return string
     */
    public function edit()
    {
        $this->title = '编辑商品类型';
        return $this->_form($this->table, 'form');
    }

    /**
     * 商品类型禁用
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("信息禁用成功！", '');
        }
        $this->error("信息禁用失败，请稍候再试！");
    }

    /**
     * 商品类型启用
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
        if(Db::table('goods')->where(['type_id' => $this->request->get('id')])->count()) {
            $this->error('存在该类型商品');
        }
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
        if ($this->request->isPost() && isset($data['tname'])) {
            $db = Db::table($this->table)->where('tname', $data['tname']);
            !empty($data['id']) && $db->where('id', 'neq', $data['id']);
            $db->count() > 0 && $this->error('此类型已存在！');
        }
    }
}