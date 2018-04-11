<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------
use think\Route;

/*  测试环境禁止操作路由绑定 */
//think\Route::post([
//    'admin/index/pass'    => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁修改用户密码！']);
//    },
//    'admin/user/pass'     => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁修改用户密码！']);
//    },
//    'admin/config/index'  => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁修改系统配置操作！']);
//    },
//    'admin/config/file'   => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁修改文件配置操作！']);
//    },
//	'admin/menu/index'      => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁排序菜单操作！']);
//    },
//    'admin/menu/add'      => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁添加菜单操作！']);
//    },
//    'admin/menu/edit'     => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁编辑菜单操作！']);
//    },
//    'admin/menu/forbid'   => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止禁用菜单操作！']);
//    },
//    'admin/menu/del'      => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止删除菜单操作！']);
//    },
//    'wechat/config/index' => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止修改微信配置操作！']);
//    },
//    'wechat/config/pay'   => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止修改微信支付操作！']);
//    },
//    'admin/node/save'     => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止修改节点数据操作！']);
//    },
//    'wechat/menu/edit'    => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止修改微信菜单操作！']);
//    },
//]);
//
//Route::get([
//    'wechat/menu/cancel' => function () {
//        return json(['code' => 0, 'msg' => '测试环境禁止删除微信菜单操作！']);
//    },
//]);
Route::group('api', function () {
    Route::rule('question/getQuestionType', 'api/Question/getQuestionType');
    Route::rule('question/getRandQuestionList', 'api/Question/getRandQuestionList');
    Route::rule('question/getQuestionList', 'api/Question/getQuestionList');
    Route::rule('userexam/getUserIsEnd', 'api/Userexam/getUserIsEnd');
    Route::rule('userexam/addUserAnswer', 'api/Userexam/addUserAnswer');
    Route::rule('userexam/addUserRecord', 'api/Userexam/addUserRecord');
    Route::rule('userexam/updateCancelExam', 'api/Userexam/updateCancelExam');
    Route::rule('map/getAllCity', 'api/Map/getAllCity');
    Route::rule('map/updateUserCity', 'api/Map/updateUserCity');
    Route::rule('map/getUserAllCity', 'api/Map/getUserAllCity');
    Route::rule('music/getList', 'api/Music/getList');
    Route::rule('music/getSearch', 'api/Music/getSearch');
    Route::rule('music/getAuthorInfo', 'api/Music/getAuthorInfo');
    Route::rule('music/getPlay', 'api/Music/getPlay');
    Route::rule('music/getSongWord', 'api/Music/getSongWord');
    Route::rule('music/getAuthorSongList', 'api/Music/getAuthorSongList');
    Route::rule('music/getCommentList', 'api/Music/getCommentList');
    Route::rule('commen/getUrlContent', 'api/Commen/getUrlContent');
    Route::rule('news/getlist', 'api/News/getlist');
    Route::rule('news/getlists', 'api/News/getlists');
    Route::rule('news/getinfo', 'api/News/getinfo');
    Route::rule('blog/gethomepage', 'api/blog/gethomepage');
    Route::rule('blog/getList', 'api/blog/getList');
    Route::rule('blog/getDetail', 'api/blog/getDetail');
    Route::miss('api/Info/nofound');
});
