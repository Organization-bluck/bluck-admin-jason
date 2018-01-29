<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/14
 * Time: 下午10:17
 */

namespace app\manager\controller;

use controller\BasicAdmin;
use GuzzleHttp\Client;
use think\Db;
use think\Exception;

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
        $db = Db::name($this->table)->order('create_at desc');
        foreach (['title'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "{$get[$key]}%");
        }

        return parent::_list($db);
    }

    /**
     * 添加音乐
     */
    public function search()
    {
        $this->title = '添加音乐信息';

        $url = config('api_url') . '/api/Music/getList';

        if(($result = $this->_getSearchMusicList($url)) != false) {
            $this->assign('list', $result);
        }
        return view();
    }

    public function ajaxSearchMusic()
    {
        try{
            $songname = $this->request->get('songname', '');

            if($songname) {
                $url = config('api_url') . '/api/Music/getSearch' . "?" . http_build_query(['keyword' => $songname]);
            } else {
                $url = config('api_url') . '/api/Music/getList';
            }
            if(($result = $this->_getSearchMusicList($url)) == false) {
                $result = [];
            }

            echo json_encode(['code' => 0, 'data' => $result]);
        } catch (Exception $e) {
            echo json_encode(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    public function ajaxAddMusic()
    {
        try{
            $id = $this->request->get('id', '');

            if(!Db::table($this->table)->where(['songid' => $id])->value('id')) {
                $url = config('api_url') . '/api/Music/getPlay' . "?" . http_build_query(['songid' => $id]);

                if(($result = $this->_getSearchMusicList($url)) == false) {
                    throw new Exception('添加音乐失败');
                }
                $msg = '添加音乐成功';
            } else {
                $msg = '音乐已添加';
            }
            echo json_encode(['code' => 0, 'msg' => $msg]);
        } catch (Exception $e) {
            echo json_encode(['code' => -1, 'msg' => $e->getMessage()]);
        }
    }

    private function _getSearchMusicList($url)
    {
        $client = new Client();
        $res = $client->request('GET', $url);
        $data = json_decode($res->getBody(), true);
        if(isset($data['code']) && ($data['code'] == 200)) {
            return $data['data'];
        } else {
            return false;
        }
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