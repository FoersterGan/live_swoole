<?php
/**
 * 监控服务 ws http 8811
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/3/31
 * Time: 15:22
 */
class Server{
    const PORT=8811;
    public function port()
    {
        $shell="netstat -nap 2>/dev/null | grep ".self::PORT." | grep LISTEN | wc -l";
        $result=shell_exec($shell);
        if($result!=1)
        {
            //发送报警服务 邮件 短信
            echo date("Ymd H:i:s")."error".PHP_EOL;
            die();
        }else{
            echo date("Ymd H:i:s")."success".PHP_EOL;
        }
    }
}
//nohub 不间断执行命令
swoole_timer_tick(2000,function(){
    (new Server())->port();
    echo "time-start".PHP_EOL;
});

