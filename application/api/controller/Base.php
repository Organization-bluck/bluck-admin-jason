<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/6
 * Time: 上午11:05
 */
namespace app\api\controller;

use think\Config;
use think\Controller;
use think\Exception;
use think\Request;
use think\Response;

class Base extends Controller
{
    protected $msg = '操作成功';
    protected $code = 200;
    protected $data = [];

    protected $user_id='';
    protected $request_params = [];

    public function _initialize()
    {
        try{
            $this->setHeader();
            $this->request_params = Request::instance()->param();

        } catch (Exception $e) {
            $this->response(-3, $e->getMessage());
        }
    }

    protected function setHeader()
    {
        header('Access-Control-Allow-Origin: ' . Config::get('allow_origin'));
        header('Content-type:application/json;charset=utf-8');
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


    public function __destruct()
    {
        $this->response();
    }
}