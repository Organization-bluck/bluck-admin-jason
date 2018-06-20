<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/12
 * Time: 上午10:17
 */

namespace app\wxapi\controller;


use think\Controller;
use \QCloud_WeApp_SDK\Conf;
use think\Response;


class Base extends Controller
{
    protected $msg = '操作成功';
    protected $code = 200;
    protected $data = [];
    protected $error = '';
    protected $page_size = 10;
    protected $openid = 'aaa';

    protected function _initialize()
    {
        require_once  VENDOR_PATH.'/qcloud/weapp-sdk/AutoLoader.php';
        require_once  VENDOR_PATH.'/qcloud/cos-sdk-v5/cos-autoloader.php';

        Conf::setup(array(
            'appId'          => 'wx49ab514a4a6590da',
            'appSecret'      => 'b21f9507afa87a8de9246b53400c13be',
            'useQcloudLogin' => false,
            'mysql' => [
//                'host' => 'sh-cdb-4urfpzxp.sql.tencentcdb.com:63645',
//                'port' => 3306,
//                'user' => 'root',
//                'pass' => '916065xuheng!!',
//                'db'   => 'cAuth',
//                'char' => 'utf8mb4'106.14.2.34
                'host' => '127.0.0.1',
                'port' => 3306,
                'user' => 'root',
                'pass' => 'QWER123asd',
                'db'   => 'poetry',
                'char' => 'utf8mb4'
            ],
            'cos' => [
                'region'       => 'cn-south',
                'fileBucket'   => 'news',
                'uploadFolder' => ''
            ],
            'serverHost'         => 'www.yingshangyan.com',
            'tunnelServerUrl'    => 'wss://clhadr5w.ws.qcloud.la/qcloud/ws',
            'tunnelSignatureKey' => '916065xuheng!!',
            'qcloudAppId'        => 1253981917,
            'qcloudSecretId'     => 'AKIDVpvmNj9fEYcQxEE3arsd8NN7bq0x3DVb',
            'qcloudSecretKey'    => '1mHVTBdpmSs1uWsyoOjodCUWHm0yf6HC',
            'wxMessageToken'     => 'abcdefghijkl',
        ));
    }

    protected function response($code = 200, $msg = 'Success', $data = [])
    {
        $data = [
            'code'  => $code? :$this->code,
            'msg'   => $msg?  :$this->msg,
            'data'  => $data? :$this->data,
        ];
        $obj = Response::create($data, input('request.format', 'json'))->code(200);
        $obj->send();
        exit;
    }

}