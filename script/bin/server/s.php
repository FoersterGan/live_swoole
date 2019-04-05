<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/3/16
 * Time: 15:32
 */
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//加载框架中的引导文件
//        require __DIR__ . '/../thinkphp/base.php';
require __DIR__ . '/../thinkphp/start.php';

\app\common\lib\redis\Predis::getInstance()->sRem('aaa','cccc');