<?php
/**
 * Created by PhpStorm.
 * User: pql-pc
 * Date: 18-6-16
 * Time: 下午10:32
 */

/**
 *redis开启
 *以全局变量保存对象
 * @param:$redisConf redis配置信息
 * @param:$GLOBAL['redis'] redis全局对象
 */
function redisStart($redisConf){
    $redis = new Redis();
    $redis->connect($redisConf['host'],$redisConf['port']);
    $redis->auth("");
    $GLOBALS['redis'] = $redis;
}
function userLogin($ws,$frame,$msg)
{
    $uid = $GLOBALS['redis']->get('WsToken-'.$msg['token']);
    if ($uid){
        $GLOBALS['redis']->hset('UserWsId',$uid,$frame->fd);
        $GLOBALS['redis']->hset('WsIdUser',$frame->fd,$uid);
        $ws->push($frame->fd,json_encode(['type'=>'login','msg'=>$uid.'-'.$frame->fd,'status'=>'success']));
    }else{
        $ws->close($frame->fd);
    }
}
/**
 * @param $conf  mysql配置函数
 */
function mysqlStart($conf){
    $mysqli = mysqli_connect($conf['host'],$conf['username'],$conf['password'],$conf['database']);
    $GLOBALS['mysqli'] = $mysqli;
}
/**
 * redis存储socket主进程id
 * @parameter：
 */
function writeProcessPid($redisConf)
{
    $redis = new Redis();
    $redis->connect($redisConf['host'],$redisConf['port']);
    $redis->auth('');
    $redis->del('SOCKET_PROCESS');
    $redis->sAdd('SOCKET_PROCESS',getmypid());
    $redis->close();
}
/**
 * 发送信息给用户
 * （1) redis获取发送放uid
 * （2）redis获取接收方socket_id
 * （3）接收方存在发送数据
 * （4）接收方不存在存储消息进入队列
 */
function sendUserMsg($senderWsId,$msg,$jsMsg)
{
    $toUserId = $msg->toUid;
    $fromUserId = $GLOBALS['redis']->hget('WsIdUser',$senderWsId);
    if ($toWsId = getWsIdByUid($toUserId))
    {
        $GLOBALS['ws']->push($toWsId,$jsMsg);
    }else{
        $GLOBALS['redis']->sAdd('Records',$jsMsg);
    }
}
/**
 * 根据ws id获取用户id
 * @param $uid
 * @return mixed
 */
function getWsIdByUid($uid)
{
    return $GLOBALS['redis']->hGet('UserWsId',$uid);
}