<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/8
 * Time: 下午5:34
 */

namespace app\api\controller;


use think\Cache;
use think\Db;
use think\Exception;
use think\Log;

class Userexam extends Base
{

    /**
     * @api {post} /Userexam/getUserIsEnd 判断用户是否存在上次未完成测试
     * @apiGroup Poetries Land API
     *
     * @apiSuccess {Number} code 状态码，值为0是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           code: 0,
     *           msg: "Success",
     *           data: {
     *               exam_id: 1702,
     *           }
     *       }
     **/
    public function getUserIsEnd()
    {
        try{
            if(!($exam_id = Cache::store('redis')->get($this->user_id.'exam'))) {
                throw new Exception('没有未完成的测试');
            }
            $this->data = ['exam_id', $exam_id];
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }


    /**
     * @api {post} /Userexam/addUserAnswer 添加用户答案
     * @apiGroup Poetries Land API
     *
     * @apiParam {Number} exam_id 考试id
     * @apiParam {Number} qm_id   题目id
     * @apiParam {Number} qi_id   测试号id
     * @apiParam {String} answer  答案
     *
     * @apiSuccess {Number} code 状态码，值为0是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           code: 0,
     *           msg: "Success",
     *           data: {
     *               code: 0回到错误; 1回答正确
     *               date: 为0的时候返回错误地方,
     *           }
     *       }
     **/
    public function addUserAnswer()
    {
        try{
            $params = $this->request->param();
            $vali = [
                'exam_id'   => ['require'],
                'qm_id'     => ['require'],
                'qi_id'     => ['require'],
                'answer'    => ['require'],
            ];
            $vali_msg = [
                'exam_id.require'=> '考试id不能为空',
                'qm_id.require'  => '考题id不能为空',
                'qi_id.require'  => '序号不能为空',
                'answer.require' => '答案不能为空',
            ];

            $result_val = $this->validate($params, $vali, $vali_msg);
            if($result_val !== true) {
                throw new Exception($result_val);
            }
            $score = Db::table('question_info')->where(['id' => $params['qi_id']])->value('score');
            if(!$score) {
                throw new Exception('考试题目信息不存在');
            }
            $true_answer = Db::table('question_model qm')
                ->field('qm.choice, ps.content')
                ->join('poetries ps', 'ps.id = qm.ps_id')
                ->where(['qm.id' => $params['qm_id']])
                ->find();
            if(!$true_answer) {
                throw new Exception('内容信息不存在');
            }
            $content = array_filter(explode('，', trim(str_replace('。', '，', $true_answer['content']))));
            $choice_arr = explode(',', $true_answer['choice']);

            $user_answer = array_filter(explode('|', $params['answer']));

            //判断答案
            if(count($choice_arr) > 1) {
                //判断是否完全作答
                if(count($user_answer) <= 1) {
                    throw new Exception('请补全答案');
                }

                //判断答案是否正确
                $error_wrong = [];
                foreach ((array)$user_answer as $k => $val) {
                    if($val !=  $content[$choice_arr[$k]]) {
                        $error_wrong[] = ['no' => $choice_arr[$k], 'sentance' => $content[$choice_arr[$k]]];
                    }
                }
                if(!empty($error_wrong)) {
                    $this->data = [
                        'code' => 0,
                        'data' => $error_wrong,
                    ];
                    $score = 0;
                }
            } else {
                if($content[$true_answer['choice']] != $user_answer) {
                    $this->data = [
                        'code' => 0,
                        'data' => [['no' => $true_answer['choice'], 'sentance' => $content[$true_answer['choice']]]],
                    ];
                    $score = 0;
                }
            }


            $h_key = config('cache.redis')['prefix']. $this->user_id .'_'. $params['exam_id'] .'_'. $params['qm_id'];
            $l_key = config('cache.redis')['prefix']. $this->user_id .'_'. $params['exam_id'];

            $data = [
                'qm_id'     => $params['qm_id'],
                'answer'    => $params['answer'],
                'score'     => $score,
            ];
            if(!($coureware_info = Cache::store('redis')->handler()->hGetAll($h_key))) {
                //如果不存在，则存入数据
                if(!Cache::store('redis')->handler()->hMset($h_key, $data)) {
                    throw new Exception('hash记录失败');
                }
                //组建该用户队列，用于接口请求
                if(!Cache::store('redis')->handler()->rpush($l_key, $h_key)) {
                    throw new Exception('list记录失败');
                }
            } else {
                //如果不存在，则存入数据
                if(!Cache::store('redis')->handler()->hMset($h_key, $data)) {
                    throw new Exception('hash记录失败');
                }
            }
            //回到成功
            $this->data = [
                'code' => 1,
                'data' => [],
                'msg'  => '回答正确'
            ];
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {post} /Userexam/addUserRecord 提交测试
     * @apiGroup Poetries Land API
     *
     * @apiParam {Number} exam_id 考试id
     *
     * @apiSuccess {Number} code 状态码，值为0是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           code: 0,
     *           msg: "Success",
     *           data: {}
     *       }
     **/
    public function addUserRecord()
    {
        try{
            $params = $this->request->param();
            $vali = [
                'exam_id'   => ['require'],
            ];
            $vali_msg = [
                'exam_id.require'=> '考试id不能为空',
            ];

            $result_val = $this->validate($params, $vali, $vali_msg);
            if($result_val !== true) {
                throw new Exception($result_val);
            }
            if(!Cache::store('redis')->get($this->user_id.'exam')) {
                throw new Exception('测试信息不存在');
            }

            $l_key = config('cache.redis')['prefix']. $this->user_id .'_'. $params['exam_id'];
            $list_info = Cache::store('redis')->handler()->lRange($l_key, 0, -1);//list所有值
            if(empty($list_info)) {
                throw new Exception('同步数据不存在');
            }

            //判断用户是否存在考试记录
            if(!($upi_id = Db::table('user_poeties_result')->where(['is_end' => 1, 'qe_id' => $params['exam_id'], 'uid' => $this->user_id])->column('upi_id'))) {
                throw new Exception('用户测试不存在');
            }

            $user_answer_list = $del_key = [];
            foreach ((array)$list_info as $val) {
                $answer_record = Cache::store('redis')->handler()->hGetAll($val);
                if(!$answer_record) {
                    $answer_record['upi_id'] = $upi_id;
                    $user_answer_list[] = $answer_record;
                    $del_key[] = $val;
                }
            }
            $del_key[] = $this->user_id.'exam';
            $del_key[] = $l_key;
            $amount_score = array_sum(array_column($user_answer_list, 'score'));

            try{
                Db::startTrans();
                //添加用户记录
                if(!Db::table('user_poeties_info')->insertAll($user_answer_list)) {
                    throw new Exception('添加用户记录失败');
                }
                //更新用户信息
                if(!Db::table('user_poeties_result')->where(['is_end' => 1, 'qe_id' => $params['exam_id'], 'uid' => $this->user_id])->update(['score_amount' => $amount_score, 'is_end' => 2])) {
                    throw new Exception('更新数据失败');
                }
                Db::commit();
            } catch (Exception $e) {
                Log::write(json_encode($user_answer_list), 'info');
                Db::rollback();
            }
            Cache::store('redis')->handler()->delete($del_key);
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    /**
     * @api {post} /Userexam/updateCancelExam 取消测试
     * @apiGroup Poetries Land API
     *
     * @apiSuccess {Number} code 状态码，值为0是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           code: 0,
     *           msg: "Success",
     *           data: {}
     *       }
     **/
    public function updateCancelExam()
    {
        try{
            $del_key = [];
            if(!($exam_id = Cache::store('redis')->get($this->user_id.'exam'))) {
                throw new Exception('没有测试');
            }

            $l_key = config('cache.redis')['prefix']. $this->user_id .'_'. $exam_id;
            $list_info = Cache::store('redis')->handler()->lRange($l_key, 0, -1);//list所有值
            if(empty($list_info)) {
                throw new Exception('同步数据不存在');
            }
            $del_key[] = $exam_id;
            $del_key[] = $l_key;
            $del_key = array_merge($del_key, $list_info);

            //判断用户是否存在考试记录
            if(!($upi_id = Db::table('user_poeties_result')->where(['is_end' => 1, 'qe_id' => $exam_id, 'uid' => $this->user_id])->column('upi_id'))) {
                throw new Exception('用户测试不存在');
            }

            if(!Db::table('user_poeties_result')->where(['is_end' => 1, 'qe_id' => $exam_id, 'uid' => $this->user_id])->update(['is_end' => 3])) {
                throw new Exception('更新用户考试记录失败');
            }
            Cache::store('redis')->handler()->delete($del_key);
            return true;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }
}