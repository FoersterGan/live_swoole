<?php
/**
 * Created by PhpStorm.
 * User: loveLinux
 * Date: 2019/2/24
 * Time: 15:41
 */
namespace app\admin\controller;
use app\common\lib\redis\Predis;
use app\common\lib\Util;
class Live{
    public function push(){
//        print_r($_GET);
        // 赛况的基本信息入库
        //2、数据组装好 push 到直播页面

//        $_POST['http_server']->push(7,'hello-singwa-push-data');
        if(empty($_GET))
        {
            return Util::show(config('code.error'),'error');
        }

        //admin 后端验证
        //token 通过token进行验证
        //mysql  通过传递的球队id去查询球队相应的信息

        $teams=[
            1=>[
                'name'=>'马刺',
                'logo'=>'/live/imgs/team1.png'
            ],
            4=>[
                'name'=>'火箭',
                'logo'=>'/live/imgs/team2.png'
            ]
        ];

        $data=[
            'type'=>intval($_GET['type']),
            'title'=>!empty($teams[$_GET['team_id']]['name'])?$teams[$_GET['team_id']]['name']:'直播员',
            'logo'=>!empty($teams[$_GET['team_id']]['logo'])?$teams[$_GET['team_id']]['logo']:'',
            'content'=>!empty($_GET['content'])?$_GET['content']:'',
            'image'=>!empty($_GET['image'])?$_GET['image']:''
        ];

        $taskData=[
            'method'=>'pushLive',
            'data'=>$data
        ];
        $_POST['http_server']->task($taskData);
        return Util::show(config('code.success'),'ok');
//        //获取已连接的客户端
//        $clientId=Predis::getInstance()->sMembers(config('redis.live_game_key'));
//        foreach($clientId as $k=>$fd)
//        {
//            //将信息推送到客户端上边
//            $_POST['http_server']->push($fd,json_encode($data));
//        }

    }
}