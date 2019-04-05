<?php
namespace app\index\controller;
use app\common\lib\ali\Sms;
use app\common\lib\Redis;
use app\common\lib\Util;
class Send
{
    /**
     * 发送验证码
     */
    public function index()
    {

//        $phoneNum=request()->get('phone_num',0);
        $phoneNum=intval($_GET['phone_num']);
        if(empty($phoneNum))
        {
           return Util::show(config('code.error'),'error');
        }
        //todo
        //生成一个随机数
        $code = rand(1000,9999);

        $taskData=[
            'method'=>'sendSms',
            'data'=>[
                'phone'=>$phoneNum,
                'code'=>$code
            ]
        ];
        $_POST['http_server']->task($taskData);
        return Util::show(config('code.success'),'ok');
        /*try{
            $request=Sms::sendSms($phoneNum,$code);
        }catch (\Exception $e){
            return Util::show(config('code.error'),'阿里短信内部异常');
        }*/
//        if($request->Code==='OK'){
//            //协程 redis 写法
//            $redis = new \Swoole\Coroutine\Redis();
//            //此处可以考虑了做一个redis的连接池
//            $redis->connect(config('redis.host'),config('redis.port'));
//            $redis->set(Redis::smsKey($phoneNum),$code,config('redis.out_time'));
//
//            return Util::show(config('code.success'),'success');
//        }else{
//            return Util::show(config('code.error'),'验证码发送失败');
//        }

//         记录到redis
    }
}