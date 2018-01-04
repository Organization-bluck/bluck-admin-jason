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

        $db = Db::name($this->table .' ps')
            ->field('ps.id, ps.title, ps.created_at, p.name')
            ->join('poets p', 'p.id=ps.poet_id')
            ->order('ps.created_at desc');
        // 搜索条件
        $keyword = $this->request->param('title', '');
        if ($keyword) {
            $db->where('ps.title', 'like', "%{$keyword}%");
        }
        $result = array();
        $page = $db->paginate(20, false);
        $result['lists'] = $page->all();
        $result['page'] = preg_replace(['|href="(.*?)"|', '|pagination|'], ['data-open="$1" href="javascript:void(0);"', 'pagination pull-right'], $page->render());

        $this->assign('keyword', $keyword);
        $this->assign('title', '企业用户激活列表');
        return view('', $result);
    }

}