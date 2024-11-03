<?php

define('DOWNLOAD_FOLDER' , getcwd() . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR);


ini_set('log_errors', 1);
ini_set('error_log', getcwd() . DIRECTORY_SEPARATOR .'error.log');

function user_is_admin($user_id){
    global $config;
    return in_array((int) $user_id, $config['admins']);
}
function is_cli() {
    return php_sapi_name() === "cli";
}
function is_localhost() {
    if(!isset($_SERVER['REMOTE_ADDR']))return true; // then is_cli === ture
    if(in_array($_SERVER['REMOTE_ADDR'], [ 
        'localhost',
        '127.0.0.1',
        '::1',
    ])){
        return true;
    }
    return false;
}

// Modify Config for Local Env
if (is_cli() || is_localhost()){
    echo "[[LocaEnv]]\n\n";
    $config['mysql'] = [
        'type' => 'mysql',
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'tinder_sy',
        'charset'  => 'utf8mb4'
    ];
}

// Texts
function M($handle){
    $all_texts = require  getcwd() . DIRECTORY_SEPARATOR .'messages.php'; 
    return isset($all_texts[$handle]) ? $all_texts[$handle] : $handle;
}

// Log
function dblog($text, $chat_id, $date = null){
    if($date === null){  // The date is only null when it's Bot->User Message
        $text = 'BOT: ' . $text;
    }
    $date = date('Y-m-d H:i:s', $date);
    $log = "~[$date]: $text \n";
    echo ">$chat_id>".$log;
    // db_update_chat_log($chat_id, $log);
}



?>

