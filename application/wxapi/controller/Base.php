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


class Base extends Controller
{
    protected function _initialize()
    {
        require_once  VENDOR_PATH.'/qcloud/weapp-sdk/AutoLoader.php';
        require_once  VENDOR_PATH.'/qcloud/cos-sdk-v5/cos-autoloader.php';

        Conf::setup(array(
            'appId'          => 'wx5d2a9650d1ab4747',
            'appSecret'      => 'a9ddfb47dd576af7fb51f274ee4dda59',
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

}