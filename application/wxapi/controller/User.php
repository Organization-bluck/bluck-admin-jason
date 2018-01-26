<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/13
 * Time: 下午1:57
 */

namespace app\wxapi\controller;


use QCloud_WeApp_SDK\Auth\LoginService;
use QCloud_WeApp_SDK\Constants;

class User extends Base
{
    /**
     * @api {get} /wxapi/user 3.获取微信小程序个人信息
     * @apiGroup WXAPI Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 0,
     *           "data": {
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
     *           }
     *       }
     */
    public function index() {
        $result = LoginService::check();

        if ($result['loginState'] === Constants::S_AUTH) {
            echo json_encode([
                'code' => 0,
                'data' => $result['userinfo']
            ]);
        } else {
            echo json_encode([
                'code' => -1,
                'data' => []
            ]);
        }
        exit;
    }
}