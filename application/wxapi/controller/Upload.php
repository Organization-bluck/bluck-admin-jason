<?php
/**
 * Created by PhpStorm.
 * User: xuheng
 * Date: 2017/12/13
 * Time: 下午4:44
 */

namespace app\wxapi\controller;

use \QCloud_WeApp_SDK\Conf as Conf;
use \QCloud_WeApp_SDK\Cos\CosAPI as Cos;
use \QCloud_WeApp_SDK\Constants as Constants;
use think\Exception;

class Upload extends Base
{
    /**
     * @api {get} /wxapi/Upload 微信小程序上传图片,单张上传
     * @apiGroup WXAPI Land API
     *
     * @apiVersion 1.0.0
     *
     * @apiParam {String} file   图片资源
     *
     * @apiSuccess {Number} code 状态码，值为200是正常
     * @apiSuccess {String} msg 提示信息
     *
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *           "code": 0,
     *           "data": {
     *               'imgUrl' : 'http:\/\/news-1253981917.cos.cn-south.myqcloud.com\/aee64fd04',
     *                'size' : 23730,
     *                'mimeType' : "image\/jpeg",
     *                'name' : 'aee64fd04fdd1047a68490ba24a3d4cc-9b44f83032_small.jpg',
     *           }
     *       }
     */
    public function index() {
        // 处理文件上传
        $file = $_FILES['file']; // 去除 field 值为 file 的文件

        ini_set('upload_max_filesize', '10M');
        ini_set('post_max_size', '10M');

        // 限制文件格式，支持图片上传
        if ($file['type'] !== 'image/jpeg' && $file['type'] !== 'image/png') {
            echo json_encode([
                'code' => 1,
                'data' => '不支持的上传图片类型：' . $file['type']
            ]);
            return;
        }

        // 限制文件大小：5M 以内
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode([
                'code' => 1,
                'data' => '上传图片过大，仅支持 5M 以内的图片上传'
            ]);
            return;
        }

        $cosClient = Cos::getInstance();
        $cosConfig = Conf::getCos();
        $bucketName = $cosConfig['fileBucket'];
        $folderName = $cosConfig['uploadFolder'];

        try {
            /**
             * 列出 bucket 列表
             * 检查要上传的 bucket 有没有创建
             * 若没有则创建
             */
            $bucketsDetail = $cosClient->listBuckets()->toArray()['Buckets'];
            $bucketNames = [];
            foreach ($bucketsDetail as $bucket) {
                array_push($bucketNames, explode('-', $bucket['Name'])[0]);
            }

            // 若不存在 bucket 就创建 bucket
            if (count($bucketNames) === 0 || !in_array($bucketName, $bucketNames)) {
                $cosClient->createBucket([
                    'Bucket' => $bucketName,
                    'ACL' => 'public-read'
                ])->toArray();
            }

            // 上传文件
            $fileFolder = $folderName ? $folderName . '/' : '';
            $fileKey = $fileFolder . md5(mt_rand()) . '-' . $file['name'];
            $uploadStatus = $cosClient->upload(
                $bucketName,
                $fileKey,
                file_get_contents($file['tmp_name'])
            )->toArray();

            echo json_encode([
                'code' => 0,
                'data' => [
                    'imgUrl' => $uploadStatus['ObjectURL'],
                    'size' => $file['size'],
                    'mimeType' => $file['type'],
                    'name' => $fileKey
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'code' => 1,
                'error' => $e->__toString()
            ]);
        }
    }
}