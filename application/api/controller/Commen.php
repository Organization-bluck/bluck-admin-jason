<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/1/16
 * Time: 下午3:32
 */

namespace app\api\controller;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use think\Exception;

class Commen extends Base
{

    public function getUrlContent()
    {
        try{
            $params = $this->request->get();
            $vali = [
                'url'   => ['require'],
                'type'  => ['require'],
            ];
            $vali_msg = [
                'url.require'   => 'url不能为空',
                'type.require'  => '请求方式不能为空',
            ];

            $result_val = $this->validate($params, $vali, $vali_msg);
            if($result_val !== true) {
                throw new Exception($result_val);
            }
            $data = [];
            if(!empty($params['params'])) {
                $data = json_decode($params['params'], 1);
                if(!$data) {
                    throw new Exception();
                }
            }
            try{
                $client = new Client();
                switch (strtolower($params['type'])) {
                    case 'get':
                        $res = $client->request('GET', urldecode(base64_decode($params['url'],1)).($data?http_build_query($data):''));
                        break;
                    case 'post':
                        $res = $client->request('POST', urldecode(base64_decode($params['url'],1)), ['form_params' => $data]);
                        break;
                    default:
                        throw new Exception('请求方式不存在');
                }
                if(!$res) {
                    throw new RequestException();
                }
                $this->data = json_decode($res->getBody(), true);
            } catch (RequestException $e) {
                throw new Exception('请求错误:'.$e->getResponse()->getStatusCode());
            }
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }
}