<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2018/6/17
 * Time: 上午10:00
 */

namespace app\wxapi\controller;


use think\Db;
use think\Exception;

class Health extends Base
{

    public function addInfo()
    {
        try{

            $params = $this->request->param();
            $rules = [
                'message_name'    => 'require',
                'message_phone'   => ['require', 'regex:/^1[345678]\d{9}$/'],
                'message_email'   => 'require|email',
                'message_content' => 'require',
            ];
            $vali_msg = [
                'message_name.require'   => '姓名不能为空',
                'message_phone.require'  => '手机号不能为空',
                'message_phone.regex'    => '手机号不合法',
                'message_email.require'  => '邮箱地址不能为空',
                'message_email.email'    => '邮箱地址不合法',
                'message_content.require'=> '留言内容不能为空',
            ];
            $result_val = $this->validate($params, $rules, $vali_msg);
            if($result_val !== true) {
                throw new Exception($result_val);
            }
            $params['openid'] = $this->openid;
            if(!Db::table('manager_message')->insert($params)) {
                throw new Exception('添加留言失败');
            }

            $this->response(1, '添加留言成功');
        } catch (Exception $e) {
            $this->response(-1, $e->getMessage());
        }
    }
}