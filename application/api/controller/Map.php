<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/13
 * Time: 下午10:18
 */

namespace app\api\controller;


use function PHPSTORM_META\type;
use think\Db;
use think\Exception;

class Map extends Base
{
    private $regex = '/(省|市|区|自治州|县|特别行政区)$/';
    /**
     * @api {get} /Map/getAllCity 获取测试类型
     * @apiGroup Map Land API
     *
     * @apiParam {Number} user_id   用户id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {
     *               "hot_list": [{
     *                      id: "110000",
     *                      name: "北京",
     *                      choice: "0未选中  1选中",
     *              }],
     *                 "list" :[{
     *                      id: "110000",
     *                      name: "北京",
     *                      choice: "0未选中  1选中",
     *              }]
     *
     *           }
     *       }
     */
    public function getAllCity()
    {
        try{
            $uid = input('get.user_id', 1);
            if(!$uid) {
                throw new Exception('用户信息不存在');
            }
            $city_list = Db::table('map_citys')->field('id, name, level_type, is_hot, parent_id')->where('level_type = 1 OR level_type = 2')->order('level_type asc')->select();
            if(!$city_list) {
                throw new Exception('信息不存在');
            }
            $user_city = Db::table('map_info')->where(['uid' => $uid])->column('mc_id');
            $hot_list = $list = [];

            array_walk($city_list, function ($v) use(&$list, &$hot_list, $user_city) {
                if($v['is_hot']) {
                    $hot_list[] = [
                        'id'        => $v['id'],
                        'name'      => preg_replace($this->regex, '', $v['name'])
                    ];
                }

                switch ((int)$v['level_type']) {
                    case 1:
                        $list[$v['id']] = [
                            'id'        => $v['id'],
                            'name'      => preg_replace($this->regex, '', $v['name']),
                            'choice'    => in_array($v['id'], $user_city)? 1 : 0
                        ];
                        break;
                    case 2:
                        $list[$v['parent_id']]['city'][] = [
                            'id'    => $v['id'],
                            'name'  => preg_replace($this->regex, '', $v['name']),
                            'choice'=> in_array($v['id'], $user_city)? 1 : 0
                        ];
                        break;
                }

            });
            $list = array_values($list);

            $this->data = compact('hot_list', 'list');
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Map/updateUserCity 更新用户选择地址
     * @apiGroup Map Land API
     *
     * @apiParam {Number} user_id   用户id
     * @apiParam {String[]} city_id   城市id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {}
     *       }
     */
    public function updateUserCity()
    {
        try{
            $uid = input('post.user_id/d');
            if(!$uid) {
                throw new Exception('用户id不存在');
            }

            $city_id = input('post.city_id/a');
            if(!$city_id) {
                throw new Exception('请选择城市');
            }
            $user_city = Db::table('map_info')->where(['uid' => $uid])->column('mc_id');
            if(!$user_city) {

                if(!$this->_add_user_city($city_id, $uid)) {
                    throw new Exception('添加城市信息失败');
                }
            } else {

                try{
                    Db::startTrans();
                    $identical = array_intersect($user_city, $city_id);

                    //存在相同的，获取需要删除的
                    if($identical) {
                        $delete_ids = array_diff($user_city, $identical); //删除的
                        $add_ids = array_diff($city_id, $identical);
                    } else {
                        $delete_ids = $user_city;
                        $add_ids = $city_id;
                    }
                    if($delete_ids) {
                        if(!Db::table('map_info')->where(['mc_id' => ['in', $delete_ids], 'uid' => ['eq', $uid]])->delete()) {
                            throw new Exception('删除失败');
                        }
                    }
                    if($add_ids) {
                        if(!$this->_add_user_city($add_ids, $uid)) {
                            throw new Exception('添加城市信息失败');
                        }
                    }
                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    throw new Exception($e->getMessage());
                }
            }

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Map/getUserAllCity 获取用户城市
     * @apiGroup Map Land API
     *
     * @apiParam {Number} user_id   用户id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": {
     *               "hot_list": [{
     *                      id: "110000",
     *                      name: "北京",
     *              }],
     *                "provice_list" :[{
     *                      id: "110000",
     *                      name: "北京",
     *                      city_list: [{
     *                          id: "110000",
     *                          name: "北京",
     *                      }],
     *              }],
     *              "rate" : '超过百分比',
     *              "city_count" : '城市数量',
     *              "provice_count" : '省级数量',
     *              "color_josn":{'beijing':12}
     *           }
     *       }
     */
    public function getUserAllCity()
    {
        try{
            $uid = input('get.user_id/d');
            if(!$uid) {
                throw new Exception('用户信息不存在');
            }

            //获取用户城市信息
            $user_city_list = Db::table('map_info')->where(['uid' => $uid])->column('mc_id');
            if(!$user_city_list) {
                return true;
            }

            //获取城市信息
            $city_list = Db::table('map_citys')->field('id, name, is_hot, parent_id')->where(['level_type' => ['eq', 2], 'id' => ['in', $user_city_list]])->select();
            if(!$city_list) {
                return true;
            }

            //获取id，并获取父级信息
            $city_info = $hot_list = [];
            foreach ((array)$city_list as $val) {
                if($val['is_hot']) {
                    $hot_list[] = ['id' => $val['id'], 'name' => preg_replace($this->regex, '', $val['name'])];
                }
                $city_info[$val['parent_id']][] = ['id' => $val['id'], 'name' => preg_replace($this->regex, '', $val['name'])];
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

            $this->data = compact('hot_list', 'provice_list', 'rate', 'city_count', 'provice_count');
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
        try{
            $uid = input('get.user_id/d', 1);
            if(!$uid) {
                throw new Exception('用户信息不存在');
            }

            //获取用户城市信息
            $user_city_list = Db::table('map_info')->where(['uid' => $uid])->column('mc_id');
            if($user_city_list) {

                //获取城市信息
                $city_list = Db::table('map_citys')->field('id, name, is_hot, parent_id, pinyin')->where(['level_type' => ['eq', 2], 'id' => ['in', $user_city_list]])->select();
                if($city_list) {

                    //获取id，并获取父级信息
                    $city_info = $hot_list = $color_list = [];
                    foreach ((array)$city_list as $val) {
                        if($val['is_hot']) {
                            $hot_list[] = preg_replace($this->regex, '', $val['name']);
                        }
                        $city_info[$val['parent_id']][] = preg_replace($this->regex, '', $val['name']);

                        $color_list[$val['pinyin']] = ['stateInitColor:' => rand(0, 1052)];
                    }

                    if(!empty($color_list)) {
                        $color_josn = json_encode($color_list);
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

                }
            }

            $this->data = compact('hot_list', 'provice_list', 'rate', 'city_count', 'provice_count', 'color_josn');
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    private function _add_user_city($add_city, $uid)
    {
        $data = [];
        foreach ((array)$add_city as $vs) {
            $data[] = [
                'uid'   => $uid,
                'mc_id' => $vs,
            ];
        }

        if(!Db::table('map_info')->insertAll($data)) {
            return false;
        }

        return true;
    }
}