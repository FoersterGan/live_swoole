<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/12
 * Time: 10:07
 */

class Http
{
    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;
    public $http = null;
    public function __construct()
    {
        $this->http= new swoole_http_server(self::HOST,self::PORT);
        $this->http->set(
            [
                'worker_num'=>4,
                'task_worker_num'=>4,
                'enable_static_handler'=>true,
                'document_root'=>"/home/swoole/thinkphp/public/static", //设置视图页面

            ]
        );

        //开启worker进程
        $this->http->on("workerstart",[$this,'onWorkerstart']);
        //开启http请求
        $this->http->on("request",[$this,'onRequest']);
        $this->http->on("task",[$this,'onTask']);
        $this->http->on("finish",[$this,'onFinish']);
        $this->http->on("close",[$this,'onClose']);
        $this->http->start();
    }

    /**
     * worker 进程回调
     * @param $server
     * @param $worker_id
     */
    public function onWorkerstart($server,$worker_id)
    {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/');
        //加载框架中的引导文件
//        require __DIR__ . '/../thinkphp/base.php';
        require __DIR__ . '/../thinkphp/start.php';
    }

    /**
     * request回调(http 请求)
     * @param $request
     * @param $response
     */
    public function onRequest($request,$response){
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
        $_POST['http_server']=$this->http;
        ob_start(); //开启ob缓存区:把输出的所有内容保存到缓存区
        try{
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e){
            //todo
            var_dump('you are error');
        }
        $res= ob_get_contents();//获取缓存区的所有内容
        ob_end_clean();//清除缓存区内容，并关闭缓存区间
        $response->end($res);
//    $http->close();//清空http服务
    }

    /**
     * @param $ws
     * @param $taskId
     * @param $workerId
     * @param $data    此处data为onMessage 中传递的$data
     */
    public function onTask($serv,$taskId,$workerId,$data)
    {
        // 分发 task 任务机制 , 让不同的任务走不同的逻辑
        $obj=new app\common\lib\task\Task;
        $method=$data['method'];//任务的方法名称
        $flag=$obj->$method($data['data']);//去调用该方法
      /*  $obj=new app\common\lib\ali\Sms();
        try{
            $response=$obj::sendSms($data['phone'],$data['code']);
        }catch (\Exception $e){
            echo $e->getMessage();
        }*/
//        print_r($response);
        return $flag;// 告诉worker
    }

    /**
     * @param $serv
     * @param $taskId
     * @param $data  此处data为onTask 中传递return 返回的 字符串
     */
    public function onFinish($serv,$taskId,$data)
    {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }
    /**
     * 关闭
     * @param $ws
     * @param $fd
     */
    public function onClose($ws,$fd)
    {
        echo "client-{$fd} is closed\n";
    }
}

new Http();