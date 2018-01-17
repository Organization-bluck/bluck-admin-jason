<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/26
 * Time: 上午10:45
 */

namespace app\api\controller;

use think\Db;
use think\Exception;

class Music extends Base
{
    private $baidu_url = 'http://tingapi.ting.baidu.com/v1/restserver/ting';

    /**
     * @api {get} /Music/getList 获取音乐列表
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} page   页数
     * @apiParam {Number} pagesize   每页多少条
     * @apiParam {Number} type   音乐类型id 1-新歌榜,2-热歌榜,11-摇滚榜,12-爵士,16-流行,21-欧美金曲榜,22-经典老歌榜,23-情歌对唱榜,24-影视金曲榜,25-网络歌曲榜
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {[
     *                  "song_id":"音乐资源id",
     *                  "ting_uid":"歌手id",
     *                  "title":"音乐名称",
     *                  "picture":"图片路径",
     *                  "hot":"热度",
     *                  "author":"演唱人",
     *              ]}
     *       }
     */
    public function getList()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.billboard.billList',
                'type'      => input('get.type/d', 1), //type = 1-新歌榜,2-热歌榜,11-摇滚榜,12-爵士,16-流行,21-欧美金曲榜,22-经典老歌榜,23-情歌对唱榜,24-影视金曲榜,25-网络歌曲榜
                'size'      => input('get.pagesize/d', 10),
                'offset'    => input('get.page/d', 0),
                'format'    =>'json'
            ];

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            if(isset($result['error_code']) && ($result['error_code'] == 22000)) {
                if(!empty($result['song_list']) && is_array($result['song_list'])) {
                    $this->data = array_map(function ($v) {
                        return [
                            'song_id'   => $v['song_id'],
                            'ting_uid'  => $v['ting_uid'],
                            'title'     => $v['title'],
                            'picture'   => $v['pic_s500'],
                            'hot'       => $v['hot'],
                            'author'    => $v['author'],
                        ];
                    }, $result['song_list']);
                }
            } else {
                throw new Exception($result['error_message']);
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getCommentList 获取每日推荐音乐列表
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} pagesize   推荐条数
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {[
     *                  "song_id":"音乐资源id",
     *                  "ting_uid":"歌手id",
     *                  "title":"音乐名称",
     *                  "picture":"图片路径",
     *                  "hot":"热度",
     *                  "author":"演唱人",
     *              ]}
     *       }
     */
    public function getCommentList()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.song.getRecommandSongList',
                'song_id'   => 877578,
                'num'       => input('get.pagesize/d', 10),
                'format'    =>'json'
            ];

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            if(isset($result['error_code']) && ($result['error_code'] == 22000)) {
                if(!empty($result['result']['list']) && is_array($result['result']['list'])) {
                    $this->data = array_map(function ($v) {
                        return [
                            'song_id'   => $v['song_id'],
                            'ting_uid'  => $v['ting_uid'],
                            'title'     => $v['title'],
                            'picture'   => $v['pic_big'],
                            'hot'       => $v['hot'],
                            'author'    => $v['author'],
                        ];
                    }, $result['result']['list']);
                }
            } else {
                throw new Exception($result['error_message']);
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getSearch 获取搜索音乐列表
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {String} keyword   音乐名称
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {[
     *                  "songid":"音乐资源id",
     *                  "songname":"音乐名称",
     *                  "artistname":"演唱人",
     *              ]}
     *       }
     */
    public function getSearch()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.search.catalogSug',
                'query'     => input('get.keyword/s', ''),
                'format'    =>'json'
            ];
            if(empty($params['query'])) {
                throw new Exception('请输入音乐名称');
            }

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            if(isset($result['error_code']) && ($result['error_code'] == 22000)) {
                if(!empty($result['song']) && is_array($result['song'])) {
                    $this->data = array_map(function ($v) {
                        return [
                            'songid' => $v['songid'],
                            'songname' => $v['songname'],
                            'artistname' => $v['artistname'],
                        ];
                    }, $result['song']);
                }
            } else {
                throw new Exception($result['error_message']);
            }

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getPlay 获取音乐播放信息
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} songid   音乐资源id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {
     *                  "songid":"音乐资源id",
     *                  "title":"音乐名称",
     *                  "author":"演唱人",
     *                  "all_rate":"码率，下载选择",
     *                  "picture":"图片",
     *                  "file_link":"音乐地址",
     *                  "file_size":"音乐大小",
     *              }
     *       }
     */
    public function getPlay()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.song.play',
                'songid'    => input('get.songid/d', 0),
                'format'    =>'json'
            ];
            if(empty($params['songid'])) {
                throw new Exception('音乐信息不存在');
            }
            if(!($this->data = Db::table('music_source')->field('songid,title,author,all_rate,picture,file_link,file_size')->where(['songid' => $params['songid']])->find())) {
                $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
                if(!$result) {
                    throw new Exception('数据不存在');
                }
                $result = json_decode($result, 1);

                if(isset($result['error_code']) && ($result['error_code'] == 22000)) {
                    if(!empty($result['bitrate']['file_link']) && !empty($result['songinfo']['title'])) {
                      $source = file_get_contents($result['bitrate']['file_link']);
                      $dirname = ROOT_PATH.'static/upload/music/';
                      if(!is_dir($dirname)) {
                          mkdir(ROOT_PATH.'static/upload/music/', 0777, true);
                      }

                      $is_ok = file_put_contents(iconv("UTF-8", "GBK", ROOT_PATH.'static/upload/music/'.$params['songid'].'.mp3'), $source);
                      if(!$is_ok) {
                            throw new Exception('下载资源失败');
                      }
                      $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
                      $file_like = $http_type.$_SERVER['HTTP_HOST'].'/static/upload/music/'.$params['songid'].'.mp3';
                    }

                    $this->data = [
                        'songid'    => isset($result['songinfo']['song_id']) ? $result['songinfo']['song_id'] : 0,
                        'title'     => isset($result['songinfo']['title']) ? $result['songinfo']['title'] : '',
                        'author'    => isset($result['songinfo']['author']) ? $result['songinfo']['author'] : '',
                        'all_rate'  => isset($result['songinfo']['all_rate']) ? $result['songinfo']['all_rate'] : '',
                        'picture'   => isset($result['songinfo']['pic_premium']) ? $result['songinfo']['pic_premium'] : '',
                        'file_link' => $file_like,
                        'file_size' => isset($result['bitrate']['file_size']) ? $result['bitrate']['file_size'] : '',
                    ];

                    Db::table('music_source')->insert($this->data);
                } else {
                    throw new Exception($result['error_message']);
                }
            }

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getSongWord 获取音乐歌词
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} songid   音乐资源id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {
     *                  "title":"音乐名称",
     *                  "content":"歌词内容",
     *              }
     *       }
     */
    public function getSongWord()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.song.lry',
                'songid'    => input('get.songid/d', 0),
                'format'    =>'json'
            ];
            if(empty($params['songid'])) {
                throw new Exception('音乐歌词信息不存在');
            }

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            $this->data = [
                'title'     => isset($result['title']) ? $result['title'] : '',
                'content' => isset($result['lrcContent']) ? $result['lrcContent'] : '',
                ];

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getAuthorSongList 获取歌手的音乐列表
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} tinguid   歌手id
     * @apiParam {Number} page   页数
     * @apiParam {Number} pagesize   每页多少条
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {[
     *                  "song_id":"音乐资源id",
     *                  "ting_uid":"歌手id",
     *                  "title":"音乐名称",
     *                  "picture":"图片路径",
     *                  "hot":"热度",
     *                  "all_rate":"码率",
     *                  "author":"演唱人",
     *              ]}
     *       }
     */
    public function getAuthorSongList()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.artist.getSongList',
                'tinguid'   => input('get.tinguid/d', 0),
                'limits'    => input('get.pagesize/d', 10),
                'offset'    => input('get.page/d', 0),
                'order'     => 0,//倒叙
                'format'    =>'json'
            ];

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            if(isset($result['error_code']) && ($result['error_code'] == 22000)) {
                if(!empty($result['songlist']) && is_array($result['songlist'])) {
                    $this->data = array_map(function ($v) {
                        return [
                            'song_id'   => $v['song_id'],
                            'ting_uid'  => $v['ting_uid'],
                            'title'     => $v['title'],
                            'picture'   => $v['pic_s500'],
                            'hot'       => $v['hot'],
                            'all_rate'  => $v['all_rate'],
                            'author'    => $v['author'],
                        ];
                    }, $result['songlist']);
                }
            } else {
                throw new Exception($result['error_message']);
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Music/getAuthorInfo 获取歌手信息
     * @apiGroup Music Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} tinguid   歌手id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {
     *                  "nickname":"昵称",
     *                  "ting_uid":"音乐人id",
     *                  "avatar_s500":"头像地址",
     *                  "intro":"简介",
     *                  "country":"籍贯",
     *                  "birth":"生日",
     *                  "songs_total":"音乐数量",
     *                  "name":"名称",
     *                  "listen_num":"收听人数",
     *              }
     *       }
     */
    public function getAuthorInfo()
    {
        try{
            $params = [
                'method'    => 'baidu.ting.artist.getInfo',
                'tinguid'   => input('get.tinguid/d', 0),
                'format'    =>'json'
            ];
            if(empty($params['songid'])) {
                throw new Exception('歌手信息不存在');
            }

            $result = file_get_contents($this->baidu_url.'?'.http_build_query($params));
            if(!$result) {
                throw new Exception('数据不存在');
            }
            $result = json_decode($result, 1);

            if(isset($result['error_code'])) {
                $this->data = [
                    'nickname'      => $result['nickname'],
                    'ting_uid'      => $result['ting_uid'],
                    'avatar_s500'   => $result['avatar_s500'],
                    'intro'         => $result['intro'],
                    'country'       => $result['country'],
                    'birth'         => $result['birth'],
                    'songs_total'   => $result['songs_total'],
                    'name'          => $result['name'],
                    'listen_num'    => $result['listen_num'],
                ];
            } else {
                throw new Exception('获取歌手信息失败');
            }

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }
}