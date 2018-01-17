<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/8
 * Time: 下午5:28
 */

namespace app\api\controller;


use think\Cache;
use think\Db;
use think\Exception;

class Question extends Base
{
    /**
     * @api {get} /Question/getQuestionType 获取测试类型
     * @apiGroup Poetries Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": [{
     *               "id": 612,
     *               "t_name": "程度",
     *               "count": "题目数量",
     *               "color": 背景颜色,
     *           }]
     *       }
     */
    public function getQuestionType()
    {
        try{
            $this->data = Db::table('question_level')->field('id, t_name, count, color')->where(['status' => 0])->select();
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Question/getRandQuestionList 获取随机测试测试
     * @apiGroup Poetries Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {String} level_id 难度级别id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": [{
     *               "qe_id": '考试id',
     *               "qi_id": '第几个内容id',
     *               "qm_id": '题号id',
     *               "choice": "第几个text为空",
     *               "content": {
     *                  text0 : '内容1',
     *                  text1 : '内容2',
     *                  text2 : '内容3',
     *                  text3 : '内容4',
     *              },
     *               "title": 标题,
     *               "bg_img": 背景图片,
     *               "name": 名称,
     *           }]
     *       }
     */
    public function getRandQuestionList()
    {
        try{
            $level_id = input('get.level_id/d', 0);
            if(!$level_id) {
                throw new Exception('level_id 参数错误');
            }
            if(Cache::store('redis')->get($this->user_id.'exam')) {
                throw new Exception('请完成上次练习');
            }

            //获取难度级别的所有id,并缓存
            $exam_id = Cache::store('redis')->get('level_id_'.$level_id);
            if(!$exam_id) {
                $exam_id = Db::table('question_exam')->where(['status' => 1, 'ql_id' => $level_id])->column('id');
                if(!$exam_id) {
                    throw new Exception('试卷信息不存在');
                }
                Cache::store('redis')->set('level_id_'.$level_id, $exam_id);
            }
            //获取用户已做过试卷
            $examed_ids = Db::table('user_poeties_result')->where(['is_end' => 2, 'uid' => $this->user_id])->column('qe_id');
            if($examed_ids) {
                $exam_id = array_diff($exam_id, $examed_ids);
            }
            if(!$exam_id) {
                throw new Exception('对不起, 获取失败');
            }

            //随机获取一个id,
            $id = array_rand($exam_id, 1);
            $exam_info = Db::table('question_info qi')
                ->field('qi.qe_id, qi.id qi_id, qm.id qm_id, qm.choice, ps.content, ps.title, ps.bg_img, p.name')
                ->join('question_model qm', 'qm.id = qi.qm_id')
                ->join('poetries ps', 'ps.id = qm.ps_id')
                ->join('poets p', 'p.id = ps.poet_id')
                ->where(['qi.qe_id' => $id])
                ->select();
            if(!$exam_info) {
                throw new Exception('获取列表信息失败');
            }
            foreach ((array)$exam_info as $keys => $vals) {
                $content = array_filter(explode('，', trim(str_replace('。', '，', $vals['content']))));
                $s_list = [];
                array_walk($content, function ($v, $k) use (&$s_list) {
                    $s_list['text'.$k] = $v;
                });
                $exam_info[$keys]['content'] = $s_list;
            }
            Cache::store('redis')->set($this->user_id.'exam', $id);

            //添加用户考试信息
            if(!Db::table('user_poeties_result')->insert(['uid' => $this->user_id, 'qe_id' => $id])) {
                throw new Exception('添加用户考试信息失败');
            }
            $this->data = $exam_info;
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {get} /Question/getQuestionList 获取测试内容
     * @apiGroup Poetries Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {String} exam_id 考试id
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 200,
     *           "msg": "操作成功",
     *           "data": [{
     *               "qe_id": '考试id',
     *               "qi_id": '第几个内容id',
     *               "qm_id": '题号id',
     *               "choice": "第几个text为空",
     *               "content": {
     *                  text0 : '内容1',
     *                  text1 : '内容2',
     *                  text2 : '内容3',
     *                  text3 : '内容4',
     *              },
     *               "title": 标题,
     *               "bg_img": 背景图片,
     *               "name": 名称,
     *           }]
     *       }
     */
    public function getQuestionList()
    {
        try{
            $id = input('get.exam_id/d', 1);
            if(!$id) {
                throw new Exception('测试信息不存在');
            }
            $exam_info = Db::table('question_info qi')
                ->field('qi.qe_id, qi.id qi_id, qm.id qm_id, qm.choice, ps.content, ps.title, ps.bg_img, p.name')
                ->join('question_model qm', 'qm.id = qi.qm_id')
                ->join('poetries ps', 'ps.id = qm.ps_id')
                ->join('poets p', 'p.id = ps.poet_id')
                ->where(['qi.qe_id' => $id])
                ->select();
            if(!$exam_info) {
                throw new Exception('获取列表信息失败');
            }
            foreach ((array)$exam_info as $keys => $vals) {
                $content = array_filter(explode('，', trim(str_replace('。', '，', $vals['content']))));
                $s_list = [];
                array_walk($content, function ($v, $k) use (&$s_list) {
                    $s_list['text'.$k] = $v;
                });
                $exam_info[$keys]['content'] = $s_list;
            }
            $this->data = $exam_info;
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }
}