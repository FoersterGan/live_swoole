<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/12
 * Time: 10:07
 */

class Ws
{
    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;//直播员直播的数据端口
    CONST CHART_PORT = 8812;//用户评论端口
    public $ws = null;
    public function __construct()
    {
        //todo：获取redis 中的key值，如果有值将key中的值del

        $this->ws= new swoole_websocket_server(self::HOST,self::PORT);
        $this->ws->listen(self::HOST,self::CHART_PORT,SWOOLE_SOCK_TCP);//监听一个新的端口
        $this->ws->set(
            [
                'worker_num'=>4,
                'task_worker_num'=>4,
                'enable_static_handler'=>true,//通过该参数设置底层http会判断document_root设置静态文件是否存在，如果存在则发送文件内容给客户端，不再触发onRequest回调
                'document_root'=>"/home/swoole/thinkphp/public/static", //设置视图页面
            ]
        );
        $this->ws->on("start",[$this,'onStart']);
        $this->ws->on("open",[$this,'onOpen']);
        $this->ws->on("message",[$this,'onMessage']);
        //开启worker进程
        $this->ws->on("workerstart",[$this,'onWorkerstart']);
        //开启http请求
        $this->ws->on("request",[$this,'onRequest']);
        $this->ws->on("task",[$this,'onTask']);
        $this->ws->on("finish",[$this,'onFinish']);
        $this->ws->on("close",[$this,'onClose']);
        $this->ws->start();
    }

    /**
     * 给当前swoole 服务主进程起别名
     * @param $server
     */
    public function onStart($server){
        swoole_set_process_name("live_master");
    }
    /**
     * worker 进程回调
     * @param $server
     * @param $worker_id
     */
    public function onWorkerstart($server,$worker_id)
    {
        // 定义应用目录
        define('APP_PATH', __DIR__ . '/../../../application/');
        //加载框架中的引导文件
//        require __DIR__ . '/../thinkphp/base.php';
        require __DIR__ . '/../../../thinkphp/start.php';
    }

    /**
     * request回调(http 请求)
     * @param $request
     * @param $response
     */
    public function onRequest($request,$response){
        $_SERVER=[];
        if($request->server['request_uri']=='/favicon.ico'){
            $response->status(404);
            $response->end();
            return;
        }
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
        $_FILES=[];//如果不提前释放为空，则该变量一直存在于内存之中，不会消失
        if(isset($request->files)){
            foreach($request->files as $k=>$v){
                $_FILES[strtolower($k)]=$v;
            }
        }
        $_POST=[];
        if(isset($request->post)){
            foreach($request->post as $k=>$v){
                $_POST[strtolower($k)]=$v;
            }
        }
        $this->writeLog();//将各种请求记录到日志当中
        $_POST['http_server']=$this->ws;
        ob_start(); //开启ob缓存区:把输出的所有内容保存到缓存区
        try{
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e){
            var_dump($e);
            //todo
            var_dump('you are error');
        }
        $res= ob_get_contents();//获取缓存区的所有内容
        ob_end_clean();//清除缓存区内容，并关闭缓存区间
        $response->end($res);
//    $http->close();//清空http服务
    }

    /**
     * @param $ws      swoole service 对象
     * @param $taskId
     * @param $workerId
     * @param $data    此处data为onMessage 中传递的$data
     */
    public function onTask($serv,$taskId,$workerId,$data)
    {
        // 分发 task 任务机制 , 让不同的任务走不同的逻辑
        $obj=new app\common\lib\task\Task; //所有的异步task任务都到此处当中去执行
        $method=$data['method'];//任务的方法名称
        $flag=$obj->$method($data['data'],$serv);//去调用该方法
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
     * 监听ws链接事件
     * @param $ws
     * @param $request  $request->fd为客户端id
     */
    public function onOpen($ws,$request)
    {
        //当有一个websocket客户端连接时就产生一条连接id $request->fd 获取，存储到redis当中
        \app\common\lib\redis\Predis::getInstance()->sAdd(config('redis.live_game_key'),$request->fd);
        var_dump($request->fd.'哈哈');
    }

    /**
     * 监听wa消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws,$frame)
    {
        echo "Message: {$frame->data}\n";
        $ws->push($frame->fd,"server:".date("Y-m-d H:i:s"));
    }

    /**
     * 关闭
     * @param $ws
     * @param $fd
     */
    public function onClose($ws,$fd)
    {
        \app\common\lib\redis\Predis::getInstance()->sRem(config('redis.live_game_key'),$fd);
        //当退出一个websocket连接时则该$fd (客户端id)也会显示，则从redis中进行删除
        echo "client-{$fd} is closed\n";
    }

    /**
     * 记录日志
     */
    public function writeLog(){
        $data=array_merge(['date'=>date('Ymd H:i:s')],$_GET,$_POST,$_SERVER);
        $logs="";
        foreach ($data as $k=>$v)
        {
            $logs.=$k.":".$v."";
        }
        swoole_async_writefile(APP_PATH.'../runtime/log/'.date("Ym").'/'.date(
            "d")."_access.log",$logs.PHP_EOL,function
        ($filename) {
            //todo
        },FILE_APPEND);
    }
}

new Ws();

//大型公司处理日志数据方案
//20台服务器 agent -> spark (计算) ->数据库 elasticsearch hadoop

//sigterm(用户重启服务器用的) sigusr1(用于重启swoole的work进程) usr2(用于重启task进程)