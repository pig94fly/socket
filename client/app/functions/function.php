<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-15
 * Time: 下午5:33
 */
function socket_conf()
{
    if (empty($GLOBALS['socketConf'])){
        return $GLOBALS['socketConf'] = config('socket');
    }
    return $GLOBALS['socketConf'];
}