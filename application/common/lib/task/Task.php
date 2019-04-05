<?php
/**
 * swoole 里面后续 所有的task异步 任务都放到这里面
 * Date: 2019/2/12
 * Time: 14:40
 */
namespace app\common\lib\task;
use app\common\lib\ali\Sms;
use app\common\lib\redis\Predis;
use app\common\lib\Redis;

class Task{
    /**
     * 异步验证码
     * @param $data
     * @param $serv swoole server对象
     */
    public function sendSms($data,$serv){
        try{
            $response=Sms::sendSms($data['phone'],$data['code']);
        }catch (\Exception $e){
            return false;
        }
        print_r($response);
        //如果发送成功把验证码记录到redis当中
        if($response->Code==='OK'){
            Predis::getInstance()->set(Redis::smsKey($data['phone']),$data['code'],config('redis.out_time'));
            print_r($response);
        }else{
            return false;
        }
        return true;
    }

    /**
     * 通过task机制发送赛况实时数据给客户端
     * @param $data
     * @param $serv swoole server对象
     */
    public function pushLive($data,$serv)
    {
        $clientId=Predis::getInstance()->sMembers(config('redis.live_game_key'));
        foreach($clientId as $k=>$fd)
        {
            //将信息推送到客户端上边
            $serv->push($fd,json_encode($data));
        }
    }
}