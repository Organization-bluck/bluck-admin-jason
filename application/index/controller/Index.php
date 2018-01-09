<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\index\controller;

use controller\WxLogin;
use think\Controller;
use think\Db;
use think\Exception;
use think\Session;

/**
 * 网站入口控制器
 * Class Index
 * @package app\index\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/04/05 10:38
 */
class Index extends Controller
{
    private $token='weixin';

    /**
     * 网站入口
     */
    public function index()
    {
        return view();
    }

    public function lettor()
    {
        try{
            $user_list = Db::table('lottery_user')->field('id, headimgurl')->select();

            if($user_list) {
                foreach ((array)$user_list as $val) {

                }
            }
            return view('', ['user_list' => $user_list]);
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    //https://www.yingshangyan.com/index/invites.html
    public function invite()
    {
        try{
            $config = array_merge(config('wechat_count'), ['redirect_uri' => $this->getCallBack()]);
            $wxlogin = new WxLogin($config);

            if(!$wxlogin->isWeixin()) {
                throw new Exception('请在微信环境中打开');
            }

            $wxlogin->getToken(md5(session_id()));

        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function CallBack()
    {
        try{
            $weixin = new WxLogin(config('wechat_count'));
            $state = input('get.state/s', '');
            if($state) {
                session_id($state);
            }
            if(!$weixin->isWeixin()) {

            }

            $param = input('param.');
            if(!isset($param['code']) || empty($param['code'])) {
                throw new Exception('非法请求');
            }

            //获取授权token
            $auth_info_arr = get_object_vars($weixin->get_access_token($param['code']));

            if(!isset($auth_info_arr['openid'])) {
                throw new Exception('openid获取失败');
            }

            //逻辑处理,判断用户是否存在
            $userinfo = Db::name('lottery_user')->where(['openid' => $auth_info_arr['openid']])->find();
            if(!$userinfo) {
                //获取用户信息
                $wx_user_info = $weixin->get_userinfo($auth_info_arr['access_token'], $auth_info_arr['openid']);
                if(!$wx_user_info) {
                    throw new Exception('获取用户信息失败');
                }
                $user_data = get_object_vars($wx_user_info);
                $userinfo = [
                    'nickname'      => $user_data['nickname'],
                    'sex'           => $user_data['sex'],
                    'headimgurl'    => $user_data['headimgurl'],
                    'openid'        => $user_data['openid'],
                    'unionid'       => $user_data['unionid'],
                ];

                if(!Db::table('lottery_user')->insert($userinfo)) {
                    throw new Exception('添加用户失败');
                }
            }

            Session::set('openid', $userinfo['openid']);
            Session::set('unionid', isset($userinfo['unionid'])?$userinfo['unionid']:1);
            Session::set('weixin_info', $userinfo);

            header('Location:'.url('index/user'));
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    public function user()
    {
        $user_list = Db::table('lottery_user')->field('id, nickname, headimgurl')->select();

        return view('', ['user_list' => $user_list]);
    }

    public function ajaxGetUserList()
    {
        try{
            $user_list = Db::table('lottery_user')->field('id, nickname, headimgurl')->select();
            echo json_encode(['code' => 0, 'date' => ['user_list' => $user_list, 'user_count' => count($user_list)]]);
        } catch (Exception $e) {
            echo json_encode(['code' => -1, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function ajaxGetCount()
    {
        try{
            $people_count = Db::table('lottery_user')->count();

            echo json_encode(['code' => 0, 'date' => ['people_count' => $people_count]]);
        } catch (Exception $e) {
            echo json_encode(['code' => -1, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function realCallBack()
    {
        $echoStr = input('get.echostr');
        if ($this->check_signature()){
            echo $echoStr;
        }else{
            return false;
        }
        exit;
    }

    private function check_signature()
    {
        $signature = input('get.signature');
        $timestamp = input('get.timestamp');
        $nonce = input('get.nonce');

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    protected function getCallBack()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type.$_SERVER['HTTP_HOST'].'/index/index/CallBack';
    }

//    public function qrc()
//    {
//        $wechat = load_wechat('Extends');
//        for ($i = 10; $i < 90; $i++) {
//            $qrc = $wechat->getQRCode($i, 1);
//            print_r($qrc);
//        }
//
//    }

}
