<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-20
 * Time: 下午5:42
 */
/**
 * @param $type     消息類型
 * @param null $msg     消息內容
 * @param null $from_id     發送人uid
 * @param null $to_id       接收人uid
 * @param null $time        發送時間
 * @return string       返回
 */
function resMsg($type,$msg=null,$from_id=null,$to_id=null,$time=null)
{
    switch ($type)
    {
        case 'ping':
            return json_encode(['type'=>'pong','msg'=>'pong',]);
        case 'msg':
            return json_encode(['type'=>'msg','from_id'=>$from_id,'to_id'=>$to_id,'content'=>$msg,'time'=>$time]);
        case 'img':
            return json_encode(['type'=>'img','from_id'=>$from_id,'to_id'=>$to_id,'url'=>$msg,'time'=>$time]);
        case 'system':
            return json_encode(['type'=>'system','content'=>$msg]);
    }
}
/**
 * 推送消息
 * @param $wsId  socket id
 * @param $msg   傳輸的信息
 */
function wsPush($wsId,$msg)
{
    $GLOBALS['ws']->push($wsId,$msg);
}
/**
 * 關閉socket
 * @param $wsId     socket id
 */
function wsClose($wsId)
{
    $GLOBALS['ws']->close($wsId);
}