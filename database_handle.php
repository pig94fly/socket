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
/**
 * @param $wsId
 * @return mixed
 */
function getUid($wsId)
{
    return $GLOBALS['redis']->hGet('WsIdUser',$wsId);
}
/**
 * 獲取羣聊裏登錄用戶ws
 * @param $groupId 羣聊id
 * @return mixed
 */
function getGroupWsId($groupId)
{
    $uidArr = $GLOBALS['redis']->sMembers("GROUP-{$groupId}");
    return $GLOBALS['redis']->hmGet('UserWsId',$uidArr);
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
 * @param $token 根據token查找uid
 */
function getUidByLoginToken($token)
{
    return $GLOBALS['redis']->get('WsToken'.$token);
}
/**
 * 保存用戶id與ws，ws與id對應信息
 * @param $wsId wsid
 * @param $uid 用戶id
 */
function storeWsMsg($wsId,$uid)
{
    $GLOBALS['redis']->hset('UserWsId',$uid,$wsId);
    $GLOBALS['redis']->hset('WsIdUser',$wsId,$uid);
}
/**
 * 保存數據到redis，再由一個進程保存到MySQL
 * @param $msg 需要保存的信息json格式
 */
function chatRecord($msg)
{
    $GLOBALS['redis']->sAdd('Records',$msg);
}

/**
 * 讀取羣組信息到redis
 * @param $redisConf
 * @param $mysqlConf
 */
function storeGroupChatRelate($redisConf,$mysqlConf)
{
    redisStart($redisConf);
    mysqlStart($mysqlConf);
    $sql = "select * from group_relate";
    $res = $GLOBALS['mysqli']->query($sql);
    while ($row = $res->fetch_assoc()){
        $groupId = $row['group_id'];
        $uid = $row['user_id'];
        $GLOBALS['redis']->sAdd("GROUP-{$groupId}",$uid);
    }
    unset($GLOBALS['redis']);
    unset($GLOBALS['mysqli']);
}