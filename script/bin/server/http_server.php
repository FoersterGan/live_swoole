<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/1/26
 * Time: 10:37
 */
/**
 * 可以写0.0.0.0 代表监听所有地址
 */
$http = new swoole_http_server("0.0.0.0",8811);
/**
 * 第一个参数代表请求的内容,$request 获取浏览器的请求信息
 * 第二个参数代表响应的内容,$response 获取响应信息
 */
$http->set(
    [
        'worker_num'=>6, //worker 进程数 cpu 核数的1-4 倍
        'enable_static_handler'=>true,
        'document_root'=>"/home/swoole/thinkphp/public/static", //设置视图页面
    ]
);
//当有worker 或者task 进程执行时会自动加载该方法 WorkerStart
$http->on('WorkerStart',function(swoole_server $server,$worker_id){
    // 定义应用目录
    define('APP_PATH', __DIR__ . '/../application/');
    //加载框架中的引导文件
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';
//    require __DIR__ . '/../thinkphp/start.php';//如果开启此处则直接执行，会执行6次，因为worker进程数为6
});
$http->on('request',function($request,$response)use($http){
    //如果将上一个请求放置在这个位置那么则是每次一个request请求就加载一次，相应会影响性能
//    define('APP_PATH', __DIR__ . '/../application/');
//    //加载框架中的引导文件
//    // 加载基础文件
//    require __DIR__ . '/../thinkphp/base.php';
    $_SERVER=[];
    if(isset($request->server)){
        foreach($request->server as $k=>$v){
            $_SERVER[strtoupper($k)]=$v;
        }
    }
    if(isset($request->header)){
        foreach($request->header as $k=>$v){
            $_SERVER[strtoupper($k)]=$v;
        }
    }

    //swoole 在还有静态资源时是不会释放超全局变量的
    $_GET=[];//如果不提前释放为空，则该变量一直存在于内存之中，不会消失
    if(isset($request->get)){
        foreach($request->get as $k=>$v){
            $_GET[strtolower($k)]=$v;
        }
    }
    $_POST=[];
    if(isset($request->post)){
        foreach($request->post as $k=>$v){
            $_POST[strtolower($k)]=$v;
        }
    }
    ob_start(); //开启ob缓存区:把输出的所有内容保存到缓存区
    // 执行应用并响应    //这是最终执行并响应的地方，所有在swoole中只加载base.php则可以
    //如果没有namespace think则 在类前面加一个think
    try{
        think\Container::get('app', [APP_PATH])
            ->run()
            ->send();
    }catch (\Exception $e){
        //todo
        var_dump('you are error');
    }
                              //这里的action指的是方法名称
//    echo "-action-".request()->action().'--action--'.PHP_EOL;
//    var_dump($_GET);
    $res= ob_get_contents();//获取缓存区的所有内容
    ob_end_clean();//清除缓存区内容，并关闭缓存区间
//    $response->cookie("wangqian","woain",5200);
    $response->end($res);
//    $http->close();//清空http服务
    // 在swoole 中慎重写 die exit
});

$http->start();

// thinkphp swoole 的类库  topthink/think-swoole