<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/6
 * Time: 上午11:05
 */
namespace app\api\controller;

use think\Controller;
use think\Response;

class Base extends Controller
{
    protected $msg = '操作成功';
    protected $code = 200;
    protected $data = [];

    protected $user_id='';


    public function __destruct()
    {
        $data = [
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->data,
        ];
        $obj = Response::create($data, input('request.format', 'json'))->code(200);
        $obj->send();
        exit;
    }
}