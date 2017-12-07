<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/6
 * Time: 下午9:22
 */

namespace app\api\controller;


use think\Cache;
use think\Db;
use think\Exception;

class Index extends Base
{

    //Cache::store('redis')->set('system_config', $system_config_info)
    public function getQuestionType()
    {
        try{
            $this->data = Db::table('question_level')->where(['status' => 0])->select();

        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }

    public function getQuestionList()
    {
        try{
            $level_id = input('get.level_id/d', 0);
            if(!$level_id) {
                throw new Exception('level_id 参数错误');
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
            $examed_list = Db::table('user_poeties_result')->where(['uid' => $this->user_id])->column('qe_id');
            if($examed_list) {
                $examed_ids = array_column($examed_list, 'qe_id');
                $exam_id = array_diff($exam_id, $examed_ids);
            }
            if(!$exam_id) {
                throw new Exception('对不起, 获取失败');
            }

            //随机获取一个id,
            $id = array_rand($exam_id, 1);
            $exam_info = Db::table('question_info qi')
                ->field('qm.choice, ps.content, ps.title, p.name')
                ->join('question_model qm', 'qm.id = qi.qm_id')
                ->join('poetries ps', 'ps.id = qm.ps_id')
                ->join('poets p', 'p.id = ps.poet_id')
                ->where(['qi.qe_id' => 1])
                ->select();
            if(!$exam_info) {
                throw new Exception('获取列表信息失败');
            }

            $this->data = $exam_info;
        } catch (Exception $e) {
            $this->code = -1;
            $this->msg = $e->getMessage();
        }
    }
}