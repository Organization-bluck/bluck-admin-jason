<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/14
 * Time: 下午10:32
 */

namespace app\manager\controller;


use controller\BasicAdmin;
use think\Db;

class Poetries extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'poetries';

    /**
     * 信息列表
     */
    public function index()
    {
        $this->title = '诗集管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_delete' => 0]);
        if(!empty($get['poet_id'])) {
            $db->where(['poet_id' => $get['poet_id']]);
        }
        foreach (['title'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        return parent::_list($db);
    }

}