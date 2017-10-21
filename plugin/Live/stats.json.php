<?php
$time_start = microtime(true);
ini_set('max_execution_time', 1);
set_time_limit(1);
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "OFFLINE";
$obj->nclients = 0;
if(empty($_POST['name']) && !empty($_GET['name'])){
    $_POST['name'] = $_GET['name'];
}else if(empty($_POST['name'])){
    $_POST['name'] = "undefined";
}
$obj->name = $_POST['name'];
$obj->applications = array();
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__LINE__.'Execution time : '.$time.' seconds');
}

$p = YouPHPTubePlugin::loadPlugin("Live");

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

$xml = $p->getStatsObject();

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

$xml = json_encode($xml);

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

$xml = json_decode($xml);
$stream = false;
$lifeStream = array();
//$obj->server = $xml->server;
if(!empty($xml->server->application) && !is_array($xml->server->application)){
    $application = $xml->server->application;
    $xml->server->application = array();
    $xml->server->application[] = $application;
}

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

if(!empty($xml->server->application[0]->live->stream)){
    $lifeStream = $xml->server->application[0]->live->stream;
    if(!is_array($xml->server->application[0]->live->stream)){
        $lifeStream = array();
        $lifeStream[0] = $xml->server->application[0]->live->stream;
    }
}

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}


require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
// the live users plugin
$liveUsersEnabled = YouPHPTubePlugin::isEnabled("cf145581-7d5e-4bb6-8c12-48fc37c0630d");

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

$obj->countLifeStream = count($lifeStream);
foreach ($lifeStream as $value){
    if(!empty($value->name)){
        $row = LiveTransmition::keyExists($value->name);
        if(empty($row) || empty($row['public'])){
            continue;
        }
        
        $users = false;
        if($liveUsersEnabled){
            require_once $global['systemRootPath'] . 'plugin/LiveUsers/Objects/LiveOnlineUsers.php';
            $liveUsers = new LiveOnlineUsers(0);
            $users = $liveUsers->getUsersFromTransmitionKey($value->name);
        }
        
        $u = new User($row['users_id']);
        $userName = $u->getNameIdentificationBd();
        $user = $u->getUser();
        $photo = $u->getPhotoURL();
        $obj->applications[] = array("key"=>$value->name, "users"=>$users, "name"=>$userName, "user"=>$user, "photo"=>$photo, "title"=>$row['title']);
        if($value->name === $_POST['name']){
            $obj->error = (!empty($value->publishing))?false:true;
            $obj->msg = (!$obj->error)?"ONLINE":"Waiting for Streamer";
            $obj->stream = $value;
            $obj->nclients = intval($value->nclients);
            break;
        }
    }
}

$time_end = microtime(true);  
$time = $time_end - $time_start; 
if($time>1){
    error_log(__FILE__." ".__LINE__.'Execution time : '.$time.' seconds');
}

echo json_encode($obj);