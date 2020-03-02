<?php $this->layout('head', ['title' => 'api']) ?>
<?php
$host="localhost:3000/api";
$Parsedown = new Parsedown();
echo $Parsedown->text('# 全部API');
echo $Parsedown->text('## 概况');
echo $Parsedown->text('* 统计信息');
echo $Parsedown->text('```curl '.$host.'/info -X POST```');
echo $Parsedown->text('返回具体的数量');
echo $Parsedown->text('```{"user":5,"device":1,"auth":1,"cmd":0,"value":0}```');

echo $Parsedown->text('## 用户相关');
echo $Parsedown->text('* 创建用户');
echo $Parsedown->text('提交明文密码，数据库只保存经过MD5计算后的加密密码');
echo $Parsedown->text('```curl '.$host.'/user/new -X POST -d "name=tom&pwd=123456"```');
echo $Parsedown->text('* 获得令牌');
echo $Parsedown->text('目前并没有对令牌期限做限制，因此在新令牌生成之前，旧令牌一直可用。');
echo $Parsedown->text('```curl '.$host.'/user/auth -X POST -d "name=tom&pwd=123456"```');
echo $Parsedown->text('如果成功，返回 auth_key（32位）');
echo $Parsedown->text('```{"auth_key":"wo7SpMgVj#yqnQ1-Dc6U9RaFs8H%LYE*"}```');
echo $Parsedown->text('如果失败，返回 err');
echo $Parsedown->text('```{"auth_key":"err"}```');
echo $Parsedown->text('## 设备管理');
echo $Parsedown->text('* 添加设备');
echo $Parsedown->text('```curl '.$host.'/device/new -X POST -d "auth_key=xxx&device_name=esp8266"```');
echo $Parsedown->text('如果成功，返回设备的 device_key（16位）');
echo $Parsedown->text('```{"err":"false","device_key":"5BuG4AZFIqLyJRXi"}```');

echo $Parsedown->text('* 查看设备');
echo $Parsedown->text('POST auth_key，以查看自己的全部设备：');
echo $Parsedown->text('```curl '.$host.'/device/all -X POST -d "auth_key=xxx"```');
echo $Parsedown->text('如果成功，返回全部设备');
echo $Parsedown->text('```{"err":"false","device":[{"id":1,"device_key":"5BuG4AZFIqLyJRXi","first_online":null,"last_online":null,"user_id":4,"device_name":"esp8266"}]}```');
echo $Parsedown->text('* 删除设备');
echo $Parsedown->text('POST auth_key，及device_key：');
echo $Parsedown->text('```curl '.$host.'/device/all -X POST -d "auth_key=xxx&device_key=xxx"```');
echo $Parsedown->text('如果成功，is_delete：true');
echo $Parsedown->text('```{"err":"false","is_delete":"true"}```');

echo $Parsedown->text('## 远程控制');
echo $Parsedown->text('* 向设备发送指令');
echo $Parsedown->text('指令并未设置有效期，未来会设置有效期');
echo $Parsedown->text('```curl '.$host.'/cmd/new -X POST -d "auth_key=xxx&device_key=xxx&cmd=xxx"```');
echo $Parsedown->text('* 读取设备的最后指令');
echo $Parsedown->text('```curl '.$host.'/cmd/last -X POST -d "device_key=xxx"```');
echo $Parsedown->text('如果成功，则返回该设备的最后指令');
echo $Parsedown->text('```{"err":"false","cmd":"on"}```');

echo $Parsedown->text('* 查询设备的历史指令（未完成）');
echo $Parsedown->text('```curl '.$host.'/cmd/all -X POST -d "auth_key=xxx&device_key=xxx"```');
echo $Parsedown->text('* 设置指令为已读（未完成）');
echo $Parsedown->text('```curl '.$host.'/cmd/read -X POST -d "auth_key=xxx&cmd_id=xxx"```');
echo $Parsedown->text('## 设备数据');
echo $Parsedown->text('* 保存传感器数据');
echo $Parsedown->text('```curl '.$host.'/value/new -X POST -d "auth_key=xxx&device_key=xxx&name=xxx&value=xxx&value_type=xxx"```');
echo $Parsedown->text('如果成功，则返回value:save');
echo $Parsedown->text('```{"err":"false","value":"save"}```');
echo $Parsedown->text('* 读取传感器最后的数据');
echo $Parsedown->text('```curl '.$host.'/value/last -X POST -d "device_key=xxx&name=xxx"```');
echo $Parsedown->text('如果成功，则返回具体数值，如：');
echo $Parsedown->text('```{"err":"false","name":"tem","value":"23","value_type":"2"}```');

echo $Parsedown->text('* 为设备删除全部的传感数据');
echo $Parsedown->text('```curl '.$host.'/value/clear -X POST -d "auth_key=xxx&device_key=xxx"```');
echo $Parsedown->text('如果成功，则返回clear:all');
echo $Parsedown->text('```{"err":"false","clear":"all"}```');

?>
