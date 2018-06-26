<?php
/**
 * Created by PhpStorm.
 * User: pql-pc
 * Date: 18-6-16
 * Time: 下午10:32
 */

/**
 * 用戶登錄
 * @param $frame
 * @param $msg
 */
function userLogin($wsId,$token)
{
    $uid = getUidByLoginToken($token);
    if ($uid){
        storeWsMsg($wsId,$uid);
        wsPush($wsId,resMsg('system','web socket connect successful!'));
    }else{
        wsPush($wsId,resMsg('system','invalid user or token!'));
    }
}
/**
 * 发送信息给用户
 * （1) redis获取发送放uid
 * （2）redis获取接收方socket_id
 * （3）接收方存在发送数据
 * （4）接收方不存在存储消息进入队列
 */
function sendUserMsg($wsId,$data)
{
    $msg = json_decode($data,true);
    $time = getNow();
    $toId = $msg['to_id'];
    $from_id = getUid($wsId);
    $resMsg = resMsg('msg',$msg['content'],$from_id,$toId,$time);

    if ($toWsId = getWsId($toId))
    {
        wsPush($toWsId,$resMsg);
    }else{
        chatRecord($resMsg);
    }
}
/**
 * 羣聊發送函數
 * @param $wsId wsID
 * @param $data 聊天數據
 */
function sendGroupMsg($wsId,$data)
{
    $msg = json_decode($data,true);
    $time = getNow();
    $groupId = $msg['to_id'];
    $from_id = getUid($wsId);
    $resMsg = resMsg('group',$msg['content'],$from_id,$groupId,$time);
    $wsIdArr = getGroupWsId($groupId);
    foreach ($wsIdArr as $wsId){
        wsPush($wsId,$resMsg);
    }
    chatRecord($resMsg);
}
/**
 * 獲取當前時間
 * @return false|string
 */
function getNow()
{
    return date("Y-m-d H:i:s");
}
