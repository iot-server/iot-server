<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/lib/random_code.php';

$app = AppFactory::create();

require 'lib/Db.class.php';

    

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("hello world");
    return $response;
});


$app->get('/api', function (Request $request, Response $response, $args) {
    $templates = new League\Plates\Engine(__DIR__ . '/templates');
    echo $templates->render('api', ['name' => 'Jonathan']);
    return $response;
});

$app->post('/api/user/new', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $username = $allPostPutVars['name'];
    $userpasswd = $allPostPutVars['pwd'];
    $md5_passwd = md5($userpasswd);
    $db = new Db();
    $user_count = $db->query("Select count(*) as count from user where name='$username' and passwd='$md5_passwd'")[0]['count'];
    if($user_count==1){
        $data = array('err' => 'true', 'err_msg' => 'user name');
    }else{
        $insert   =  $db->query("INSERT INTO user(name,passwd) VALUES(:name,:passwd)", array("name"=>$username,"passwd"=>$md5_passwd));
        if($insert > 0 ) {
            $data = array('err' => 'false', 'insert_user' => 'true');
        }else{
            $data = array('err' => 'true', 'err_msg' => 'err');
        }
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/info', function (Request $request, Response $response, $args) {
    $db = new Db();
    $user_count = $db->query("Select count(*) as count from user")[0]['count'];
    $device_count = $db->query("Select count(*) as count from device")[0]['count'];
    $auth_count = $db->query("Select count(*) as count from auth")[0]['count'];
    $value_count = $db->query("Select count(*) as count from value")[0]['count'];
    $cmd_count = $db->query("Select count(*) as count from cmd")[0]['count'];
    $info=[];
    $info['user']=$user_count;
    $info['device']=$device_count;
    $info['auth']=$auth_count;
    $info['cmd']=$cmd_count;
    $info['value']=$value_count;
    $payload = json_encode($info);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/api/user/auth', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $username = $allPostPutVars['name'];
    $userpasswd = $allPostPutVars['pwd'];
    $md5_passwd = md5($userpasswd);
    $db = new Db();
    $user_count = $db->query("Select count(*) as count from user where name=:u and passwd=:p", array("u"=>$username,"p"=>$md5_passwd))[0]['count'];
    if($user_count==1){
        $auth_key=random_code(32,"t,n,s");
        $user_id = $db->query("Select id as user_id from user where name=:u and passwd=:p",array("u"=>$username,"p"=>$md5_passwd))[0]['user_id'];
        $update=$db->query("UPDATE auth SET auth_key = :key WHERE user_id = :id", array("key"=>$auth_key,"id"=>$user_id));
        if($update<=0){
            $insert   =  $db->query("INSERT INTO auth(user_id,auth_key) VALUES(:uid,:key)", array("uid"=>$user_id,"key"=>$auth_key));
        }
        $data = array('auth_key' => $auth_key);
    }else{
        $data = array('auth_key' => 'err');
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/device/all', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $db = new Db();
    $user = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key));
    if(count($user)>0){
        $user_id = $user[0]['user_id'];
        $device = $db->query("Select * from device where user_id=:u", array("u"=>$user_id));
        $data = array('err' => 'false', 'device' => $device); 
    }else{
       $data = array('err' => 'true', 'err_msg' => 'auth_key faile'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/device/info', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_key = $allPostPutVars['device_key'];
    
    $data = array('err' => 'false', 'auth_key' => 'xxxxxx');
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/device/new', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_name = $allPostPutVars['device_name'];
    $db = new Db();
    $count = $db->query("Select count(*) as count from auth where auth_key=:k", array("k"=>$auth_key))[0]['count'];
    if($count>0){
        $user_id = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key))[0]['user_id'];
        $device_key=random_code(16,"t,n,s");
        $db->query("INSERT INTO device(device_name,device_key,user_id) VALUES(:name,:key,:uid)", array("uid"=>$user_id,"key"=>$device_key,"name"=>$device_name));
        $data = array('err' => 'false', 'device_key' => $device_key); 
    }else{
        $data = array('err' => 'true', 'err_msg' => 'auth_key faile');
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/device/delete', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_key = $allPostPutVars['device_key'];
    $db = new Db();
    $user = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key));
    if(count($user)>0){
        $user_id = $user[0]['user_id'];
        $delete = $db->query("DELETE FROM device WHERE user_id = :uid and device_key=:device_key", array("uid"=>$user_id,"device_key"=>$device_key));
        if($delete>0){
            $data = array('err' => 'false', 'is_delete' => 'true'); 
        }else{
            $data = array('err' => 'true', 'err_msg' => 'no device_key found'); 
        }
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no auth_key found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/cmd/new', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_key = $allPostPutVars['device_key'];
    $cmd = $allPostPutVars['cmd'];
    $db = new Db();
    $user = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key));
    if(count($user)>0){
        $user_id = $user[0]['user_id'];
        $device = $db->query("Select device_key from device where user_id=:uid and device_key=:device_key", array("uid"=>$user_id,"device_key"=>$device_key));
        if(count($device)>0){
            $insert = $db->query("INSERT INTO cmd(cmd,is_execute,device_key) VALUES(:cmd,0,:device_key)", array("cmd"=>$cmd,"device_key"=>$device_key));
            $data = array('err' => 'false', 'cmd' => 'save'); 
        }else{
            $data = array('err' => 'true', 'err_msg' => 'device_key not yours'); 
        }
        
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no auth_key found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/cmd/last', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $device_key = $allPostPutVars['device_key'];
    $db = new Db();
    $cmd = $db->query("Select * from cmd where device_key=:device_key order by id desc limit 0,1", array("device_key"=>$device_key));
    if(count($cmd)>0){
        $cmd = $cmd[0]['cmd'];
        $data = array('err' => 'false', 'cmd' => $cmd); 
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no cmd found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/value/new', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_key = $allPostPutVars['device_key'];
    $name = $allPostPutVars['name'];
    $value = $allPostPutVars['value'];
    $value_type = $allPostPutVars['value_type'];
    $db = new Db();
    $user = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key));
    if(count($user)>0){
        $user_id = $user[0]['user_id'];
        $device = $db->query("Select device_key from device where user_id=:uid and device_key=:device_key", array("uid"=>$user_id,"device_key"=>$device_key));
        if(count($device)>0){
            $insert = $db->query("INSERT INTO value(name,value,value_type,device_key) VALUES(:name,:value,:value_type,:device_key)", array("name"=>$name,"value"=>$value,"value_type"=>$value_type,"device_key"=>$device_key));
            $data = array('err' => 'false', 'value' => 'save'); 
        }else{
            $data = array('err' => 'true', 'err_msg' => 'device_key not yours'); 
        }
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no auth_key found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/value/last', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $device_key = $allPostPutVars['device_key'];
    $name = $allPostPutVars['name'];
    $db = new Db();
    $value = $db->query("Select * from value where device_key=:device_key and name=:name order by id desc limit 0,1", array("device_key"=>$device_key,"name"=>$name));
    if(count($value)>0){
        $name = $value[0]['name'];
        $value = $value[0]['value'];
        $value_type = $value[0]['value_type'];
        $data = array('err' => 'false', 'name' => $name,'value' => $value,'value_type' => $value_type); 
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no value found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/value/clear', function (Request $request, Response $response, $args) {
    $allPostPutVars = $request->getParsedBody();
    $auth_key = $allPostPutVars['auth_key'];
    $device_key = $allPostPutVars['device_key'];
    $db = new Db();
    $user = $db->query("Select user_id from auth where auth_key=:k", array("k"=>$auth_key));
    if(count($user)>0){
        $user_id = $user[0]['user_id'];
        $device = $db->query("Select device_key from device where user_id=:uid and device_key=:device_key", array("uid"=>$user_id,"device_key"=>$device_key));
        if(count($device)>0){
            $delete = $db->query("delete from value where device_key=:device_key", array("device_key"=>$device_key));
            $data = array('err' => 'false', 'clear' => 'all'); 
        }else{
            $data = array('err' => 'true', 'err_msg' => 'device_key not yours'); 
        }
    }else{
        $data = array('err' => 'true', 'err_msg' => 'no auth_key found'); 
    }
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://mysite')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->run();
