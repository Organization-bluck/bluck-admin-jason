<?php
/**
 * 微信登录类
 */
namespace controller;

use think\Cache;

class WxLogin {
    
    //appId
    private $appId = '';
    //appSecret
    private $appSecret = '';
    //redirect_uri
    private $redirect_uri = '';
    
    public function __construct($config = array())
    {
        $this->appId = isset($config['appId'])?$config['appId']:'';
        $this->appSecret = isset($config['appSecret'])?$config['appSecret']:'';
        $this->redirect_uri = isset($config['redirect_uri'])?$config['redirect_uri']:'';
    }

    public function getToken($code_params)
    {
        if($this->isWeixin()) {
            header("Location:".$this->get_authorize_url($code_params));
            exit();
        }
    }

    //判断是否微信登录
    public function isWeixin()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_weixin = strpos($agent, 'micromessenger') ? true : false ;

        if($is_weixin){

            return true;
        }else{

            return false;
        }
    }
    
    /**
     * 获取微信授权网址
     *
     * @param  $state 参数  
     */
    public function get_authorize_url($code_params)
    {
        $authorize_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appId.'&redirect_uri='.$this->redirect_uri.'&response_type=code&scope=snsapi_userinfo&state='.$code_params.'#wechat_redirect';
        return $authorize_url;
    }
    
    /**
     * 根据code获取授权toke
     *
     * @param  $parameters
     */
    public function get_access_token($code)
    {
        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$code.'&grant_type=authorization_code';  
        $res = $this->https_request($token_url);
        return json_decode($res);
    }
    
    /**
     * 根据access_token以及oppenid获取用户信息
     *
     * @param  $access_token
     * @param  $oppenid
     */
    public function get_userinfo($access_token,$oppenid)
    {
        $info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$oppenid;  
        $res = $this->https_request($info_url);
        return json_decode($res);
    }

    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.(当前需求 ajax调用，url必须和当前页面一直)
        //$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = input('post.url');
        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(Cache::get('jsapi_ticket'), 1);
        if (empty($data) || $data['expire_time'] < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->https_request($url));
            $ticket = $res->ticket;
            if ($ticket) {
                Cache::set('jsapi_ticket', json_encode([
                    'expire_time' => time() + 7000,
                    'jsapi_ticket'=> $ticket,
                ]), 7000);
            }
        } else {
            $ticket = $data['jsapi_ticket'];
        }

        return $ticket;
    }

    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(Cache::get('access_token'), 1);
        if (empty($data) || $data['expire_time'] < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->https_request($url));
            $access_token = $res->access_token;
            if ($access_token) {
                Cache::set('access_token', json_encode([
                    'expire_time' => time() + 7000,
                    'access_token'=> $access_token,
                ]), 7000);
            }
        } else {
            $access_token = $data['access_token'];
        }
        return $access_token;
    }
    
    /**
     * https请求
     * @param  $url  请求网址
     */
    public function https_request($url , $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}
