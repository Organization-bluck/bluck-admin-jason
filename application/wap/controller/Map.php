<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/11
 * Time: 下午3:13
 */
namespace app\wap\controller;



use think\Db;
use think\Exception;

class Map extends Base
{
    private $regex = '/(省|市|区|自治州|县|特别行政区)$/';

    public function index()
    {
        try{
            $uid = input('get.user_id/d', 1);
            if(!$uid) {
                throw new Exception('用户信息不存在');
            }

            //获取用户城市信息
            $user_city_list = Db::table('map_info')->where(['uid' => $uid])->column('mc_id');
            $data = [];
            if($user_city_list) {

                //获取城市信息
                $city_list = Db::table('map_citys')->field('id, name, is_hot, parent_id')->where(['level_type' => ['eq', 2], 'id' => ['in', $user_city_list]])->select();
                if($city_list) {

                    //获取id，并获取父级信息
                    $city_info = $hot_list = [];
                    foreach ((array)$city_list as $val) {
                        if($val['is_hot']) {
                            $hot_list[] = preg_replace($this->regex, '', $val['name']);
                        }
                        $city_info[$val['parent_id']][] = preg_replace($this->regex, '', $val['name']);
                    }

                    $parent_list = Db::table('map_citys')->field('id, name')->where(['level_type' => ['eq', 1], 'id' => ['in', array_keys($city_info)]])->select();
                    if(!$parent_list) {
                        throw new Exception('城市信息获取失败');
                    }
                    $provice_list = [];
                    foreach ((array)$parent_list as $vals) {
                        $provice_list[] = [
                            'id' => $vals['id'],
                            'name' => preg_replace($this->regex, '', $vals['name']),
                            'city_list' => !empty($city_info[$vals['id']])?$city_info[$vals['id']]:[],
                        ];
                    }
                    //计算用户超过人数
                    $city_count = count($city_list);
                    $provice_count = count(array_keys($city_info));
                    $rate = 100;
                    $count = Db::table('map_info')->field('count(id) pcount')->where(['uid' => ['<>', $uid]])->group('uid')->select();
                    if($count) {
                        $big_user = 0;
                        foreach ((array)$count as $value) {
                            if($value['pcount'] < $city_count) {
                                $big_user+=1;
                            }
                        }
                        $rate = (float)($big_user/count($count)) * 100;
                    }

                    $data = compact('hot_list', 'provice_list', 'rate', 'city_count', 'provice_count');

                }
            }

            return view('', $data);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function chart()
    {
        return view();
    }
}