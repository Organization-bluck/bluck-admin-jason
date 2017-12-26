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
use think\Db;
use think\Exception;

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
     *                      'user_id' : 2,
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
        try{
            header('Content-type:application/json;charset=utf-8');
            $result = LoginService::login();

            if ($result['loginState'] !== Constants::S_AUTH) {
                throw new Exception($result['error']);
            }
            $user_info = $this->object2array($result['userinfo']);

            if(!($user_id = Db::table('map_user')->where(['open_id' => $user_info['userinfo']['openId']])->value('id'))) {
                //注册
                $data = [
                    'name'      => $user_info['userinfo']['nickName'],
                    'open_id'   => $user_info['userinfo']['openId'],
                ];
                if(!($user_id = Db::table('map_user')->insert($data, false, true))) {
                    throw new Exception('添加用户信息失败');
                }

            }
            $user_info['userinfo']['user_id'] = $user_id;

            echo json_encode([
                'code' => 0,
                'data' => $user_info
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'code' => -1,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function object2array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

}