<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-20
 * Time: 下午5:52
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
/**
 * @param $conf  mysql配置函数
 */
function mysqlStart($conf){
    $mysqli = mysqli_connect($conf['host'],$conf['username'],$conf['password'],$conf['database']);
    $GLOBALS['mysqli'] = $mysqli;
}
/**
 * 根据ws id获取用户id
 * @param $uid
 * @return mixed
 */
function getWsId($uid)
{
    return $GLOBALS['redis']->hGet('UserWsId',$uid);
}
function getUid($wsId)
{
    return $GLOBALS['redis']->hGet('WsIdUser',$wsId);
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
function getUidByLoginToken($token)
{
    $uid = $GLOBALS['redis']->get('WsToken-'.$token);
}
function storeWsMsg($wsId,$uid)
{
    $GLOBALS['redis']->hset('UserWsId',$uid,$wsId);
    $GLOBALS['redis']->hset('WsIdUser',$wsId,$uid);
}
function chatRecord()
{
    $GLOBALS['redis']->sAdd('Records',$jsMsg);
}