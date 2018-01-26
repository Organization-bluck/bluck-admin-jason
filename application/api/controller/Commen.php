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
    /**
     * @api {get} /api/Commen/getUrlContent 1.获取第三方资源
     * @apiGroup Commen Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {String} url=aHR0cHMlM0ElMkYlMkZyb3V0ZS5zaG93YXBpLmNvbSUyRjI1NS0xJTNGc2hvd2FwaV9hcHBpZCUzRDU0MzYwJTI2c2hvd2FwaV9zaWduJTNEMjhiZDk5ZGYwNWVmNDlkY2E5NGNmNTM4Mjc2ZmMyNzglMjZwYWdlJTNEMQ==    第三方url,先urlencode,然后base64
     * @apiParam {String} type=get   请求方式  get 或 post
     * @apiParam {String} params 请求参数(可选) 先json_encode,然后base64
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {}
     *       }
     */
    public function getUrlContent()
    {
        try{
            $vali = [
                'url'   => ['require'],
                'type'  => ['require'],
            ];
            $vali_msg = [
                'url.require'   => 'url不能为空',
                'type.require'  => '请求方式不能为空',
            ];

            $result_val = $this->validate($this->request_params, $vali, $vali_msg);
            if($result_val !== true) {
                throw new Exception($result_val);
            }
            $data = [];
            if(!empty($this->request_params['params'])) {
                $data = json_decode(base64_decode($this->request_params['params']), 1);
                if(!$data) {
                    throw new Exception();
                }
            }
            try{
                $client = new Client();
                switch (strtolower($this->request_params['type'])) {
                    case 'get':
                        $res = $client->request('GET', urldecode(base64_decode($this->request_params['url'],1)).'?'.($data?http_build_query($data):''));
                        break;
                    case 'post':
                        $res = $client->request('POST', urldecode(base64_decode($this->request_params['url'],1)), ['form_params' => $data]);
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
            $this->response(-1, $e->getMessage());
        }
    }
}