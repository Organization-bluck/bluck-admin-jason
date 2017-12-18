<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/13
 * Time: 上午9:23
 */

namespace app\wxapi\controller;


use QCloud_WeApp_SDK\Auth\LoginService;
use QCloud_WeApp_SDK\Constants;

class Login extends Base
{

    /**
     * @api {get} /wxapi/login 微信小程序登录
     * @apiGroup WXAPI Land API
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 0,
     *           "data": {
     *               "userinfo": {
     *                      'openId' : 'ouUj_0CDuDewz-EtfLKMbQphLEqk',
     *                      'nickName' : '\u8bb8\u6052',
     *                      'gender' : 1,
     *                      'language' : 'zh_CN',
     *                      'city' : 'Huanggang',
     *                      'province' : 'Hubei',
     *                      'country' : 'China',
     *                      'avatarUrl' : 'https:\/\/wx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTLFdUicXyh',
     *                      'watermark' : {
     *                          'timestamp':1513157238,
     *                          'appid':'wx5d2a9650d1ab4747',
     *                      },
     *                  },
     *               "skey": "6f61af0cf974b09f4be1e0148079c2e98bda08b8",
     *           }
     *       }
     */
    public function index()
    {
        header('Content-type:application/json;charset=utf-8');
        $result = LoginService::login();

        if ($result['loginState'] === Constants::S_AUTH) {
            echo json_encode([
                'code' => 0,
                'data' => $result['userinfo']
            ]);
        } else {
            echo json_encode([
                'code' => -1,
                'error' => $result['error']
            ]);
        }
        exit;
    }

}