<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/24
 * Time: 下午4:08
 */

namespace app\api\controller;


use Couchbase\ViewQuery;
use GuzzleHttp\Client;
use QL\QueryList;
use think\Db;
use think\Exception;

class News extends Base
{
    public function getList()
    {
        try{
            $options = [
                'headers' => [
                    'Authorization' => 'APPCODE d4ed0bffeefb4f6d9cdf4cc6111f7781'
                ],
            ];
            $host = "http://toutiao-ali.juheapi.com";
            $path = "/toutiao/index";
            $querys = "type=".input('get.type/s', 'type');

            $client = new Client();
            $res = $client->request('GET', $host . $path . "?" . $querys, $options);
            $data = json_decode($res->getBody(), true);
            if(!empty($data['result']['stat']) && ($data['result']['stat'] == 1)) {
                $list = [];
                foreach ((array)$data['result']['data'] as $val) {
                    $list[$val['uniquekey']] = [
                        'uniquekey'         => $val['uniquekey'],
                        'title'             => $val['title'],
                        'date'              => $val['date'],
                        'category'          => $val['category'],
                        'author_name'       => $val['author_name'],
                        'url'               => $val['url'],
                        'thumbnail_pic_s'   => $val['thumbnail_pic_s'],
                        'thumbnail_pic_s02' => isset($val['thumbnail_pic_s02'])?$val['thumbnail_pic_s02']:$val['thumbnail_pic_s'],
                        'thumbnail_pic_s03' => isset($val['thumbnail_pic_s03'])?$val['thumbnail_pic_s03']:$val['thumbnail_pic_s'],
                    ];
                }

                $unq_list = Db::table('news_list')->where(['uniquekey' => ['IN', array_keys($list)]])->column('uniquekey');
                if($unq_list) {
                    foreach ((array)$unq_list as $v) {
                        unset($list[$v]);
                    }
                }
                if($list) {
//                    $a = [
//                        'uniquekey' => 'f112fbe7d9032d4a1b6e72356d7bdf6c',
//                        'title' => '这个监狱里一群壮汉在织毛衣 织三条毛衣就能减1天刑还有工资领',
//                        'date' => '2018-01-24 16:22',
//                        'category' => '头条',
//                        'author_name' => '你若乘风',
//                        'url' => 'http://mini.eastday.com/mobile/180124162251708.html',
//                        'thumbnail_pic_s' => 'http://00.imgmini.eastday.com/mobile/20180124/20180124_ccef819a90f94a8de6b3f62fc9fb34f0_mwpm_03200403.jpg',
//                        'thumbnail_pic_s02' => 'f112fbe7d9032d4a1b6e72356d7bdf6c',
//                        'thumbnail_pic_s03' => 'f112fbe7d9032d4a1b6e72356d7bdf6c',
//                    ];
                    $a =
                    Db::table('news_list')->insertAll(array_values($list));
                }

                $this->data = $data['result']['data'];
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    public function getInfo()
    {
        try{
            $url = input('get.url/s');
            if(!$url) {
                throw new Exception('请传入链接');
            }

            if(!($data = Db::table('news_info')->where(['uniq_id' => md5($url)])->find())) {
                $data['l_id'] = Db::table('news_list')->where(['url' => $url])->value('id');

                $hj = QueryList::Query($url,array(
                    'title'=>array('h1','html'),
                    'src'=>array('span','html'),
                    'content'=>array('#content','html')
                ));
                if(!empty($hj->data)) {
                    $data['title'] = $hj->data[0]['title'];
                    $data['date'] = rtrim(explode('来源', $hj->data[0]['src'])[0], chr(0xc2).chr(0xa0));
                    $data['author'] = explode('：', $hj->data[0]['src'])[1];
                    $data['content'] = $hj->data[0]['content']. '<div>数据内容由阿里云新闻头条api提供</div>';
                }
                Db::table('news_info')->insert(array_merge($data, ['uniq_id' => md5($url)]));
            }
            $id = $data['l_id'];
            unset($data['l_id']);
            $this->data = [
                'info'      => $data,
                'prev_id'   => !$id ? 0 : Db::table('news_list')->where(['id' => ['lt', $id]])->value('url'),
                'next_id'   => !$id ? 0 : Db::table('news_list')->where(['id' => ['gt', $id]])->value('url'),
            ];

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

}