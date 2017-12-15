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