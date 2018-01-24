<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/24
 * Time: 下午4:08
 */

namespace app\api\controller;


use GuzzleHttp\Client;
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
                $this->data = $data['result']['data'];
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

}