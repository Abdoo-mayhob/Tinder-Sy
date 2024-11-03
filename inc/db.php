<?php
use Medoo\Medoo;


// --------------------------------------------------------------------------------------
// Setup MySql
$db = new Medoo($config['mysql']);
function db(){
    global $db;
    return $db;
}

// Note to Self: DEFAULT keyword is not supported everywhere properly, Set defaults in code on insertion instead.
$db->create("users", [
	"ID" => [
		"INT(6)",
		"UNSIGNED",
		"NOT NULL",
		"AUTO_INCREMENT",
		"PRIMARY KEY"
	],
	"status" => [
		"VARCHAR(10)"
	],
	"user_id" => [
		"VARCHAR(10)",
		"NOT NULL",
		"UNIQUE"
	],
	"tg_username" => [
		"VARCHAR(50)"
	],
	"full_ar_name" => [
		"VARCHAR(254)"
	],
	"display_name" => [
		"VARCHAR(254)"
	],
	"bio" => [
		"TEXT"
	],
	"profile_pic" => [
		"VARCHAR(254)"
	],
	"gender" => [
		"VARCHAR(30)"
	],
	"gender_pref" => [
		"VARCHAR(30)"
	],
	"birth_year" => [
		"VARCHAR(4)"
	]
]);


// --------------------------------------------------------------------------------------
// Helper Functions

function db_user_exist($user_id){
    return db()->has('users',['user_id' => $user_id]);
}

// function db_update_chat_log($user_id, $chat_log){
//     db()->query("UPDATE <users> SET <chat_log>=CONCAT(<chat_log>, :chat_log) WHERE <user_id>=:user_id",[
//         ":user_id" => $user_id,
//         ":chat_log" => $chat_log
//     ])->fetchAll();
// }