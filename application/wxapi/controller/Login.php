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