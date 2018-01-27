<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/14
 * Time: 下午10:32
 */

namespace app\manager\controller;


use controller\BasicAdmin;
use service\DataService;
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
        return view('', $result);
    }

    /**
     * 添加诗集
     */
    public function add()
    {
        $this->title = '添加诗集';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑编辑
     * @return string
     */
    public function edit()
    {
        $this->title = '编辑诗集';
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
     * 表单处理
     * @param $data
     */
    protected function _form_filter($data=[])
    {
        if (!$this->request->isPost()) {
            $this->assign('poets_list', Db::table('poets')->field('id, name')->cache(true, 30)->where(['is_del' => 0])->select());
        }
    }

}