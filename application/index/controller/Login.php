<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/11
 * Time: 9:53
 */

namespace app\index\controller;
use app\common\lib\Redis;
use app\common\lib\redis\Predis;
use app\common\lib\Util;
class Login
{
    public function index()
    {
        // get   phone code
        $phoneNum=$_GET['phone_num'];
        // redis code
        $code=$_GET['code'];
        if(empty($phoneNum) || empty($code))
        {
            return Util::show(config('code.error'),'phone or code is null');
        }
        //redis code

        try{
            //获取验证码值
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum));
            echo $redisCode;
        }catch (\Exception $e){
            echo $e->getMessage();
        }
        if($redisCode==$code){
            // 写入redis
            $data=[
              'user'=>$phoneNum,
              'srcKey'=>md5(Redis::userKey($phoneNum)),
              'time'=>time(),
              'isLogin'=>true
            ];
            Predis::getInstance()->set(Redis::userKey($phoneNum),$data);

            return Util::show(config('code.success'),'ok',$data);
        }else{
            return Util::show(config('code.error'),'login error');
        }
        // redis.so

    }
}