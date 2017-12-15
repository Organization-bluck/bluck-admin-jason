<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/13
 * Time: 下午5:53
 */

namespace app\wxapi\controller;

use controller\ChatTunnelHandler;
use \QCloud_WeApp_SDK\Tunnel\TunnelService;
use \QCloud_WeApp_SDK\Auth\LoginService;
use QCloud_WeApp_SDK\Constants as Constants;

class Tunnel extends Base
{
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $result = LoginService::check();

            if ($result['loginState'] === Constants::S_AUTH) {
                $handler = new ChatTunnelHandler($result['userinfo']);
                TunnelService::handle($handler, array('checkLogin' => TRUE));
            } else {
                echo json_encode([
                    'code' => -1,
                    'data' => []
                ]);
            }
        } else {
            $handler = new ChatTunnelHandler([]);
            TunnelService::handle($handler, array('checkLogin' => FALSE));
        }
    }
}