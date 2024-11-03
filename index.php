<?php
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\RunningMode\Webhook;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

$config = require_once "config.php";
require_once "vendor/autoload.php";
require_once "init.php";
require_once "inc/new_account__command.php";
require_once "inc/db.php";
// require_once "inc/Logger.php";



// --------------------------------------------------------------------------------------
// Init Bot

$bot = new Nutgram($config['api_key']); //, new Configuration(logger: TinderSYLogger::class)
if(is_cli()){
    $bot->setRunningMode(Polling::class);
}
else{
    $bot->setRunningMode(Webhook::class);
}


// --------------------------------------------------------------------------------------
// User Commands

// Start
$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage(M('CS_hi'));
});

// // Start with Ref
// $bot->onCommand('start {refed_by}', function (Nutgram $bot, $refed_by) {
//     $bot->sendMessage(M('CS_hi'));
//     $user_id = $bot->userId();

//     if(!is_numeric($refed_by))return;
//     if(db_user_exist($refed_by)){
//         $bot->sendMessage(M('CS_ref_link_broken'));
//         return;
//     }
//     if(db_user_exist($user_id)){
//         $bot->sendMessage(M('CS_you_are_already_a_user'));
//         return;
//     }

//     $ref_user_display_name =  db()->select("users", 'display_name', [
//         "user_id" => $refed_by
//     ]);

//     $bot->sendMessage(M('CS_you_refed_by') . $ref_user_display_name);


// });

// Reset Account
// $bot->onCommand('reset_account', function (Nutgram $bot) {
//     db()->update('users',
//         [
//             'status' => '0',
//             'full_ar_name' => null,
//             'display_name' => null,
//             'bio' => null,
//             'profile_pic' => null,
//             'photo_id' => null,
//             'gender' => null,
//             'gender_pref' => null,
//             'birth_year' => null,
//             'swipes_left' => null,
//             'swipes_right' => null,
//             'refed_by' => null,
//             'ref' => null,
//         ],
//         ['user_id' => $bot->userId()]
//     );
//     // db_update_user($bot->userId(), '0', null, null, null, null, null, null, null, null);
//     $bot->sendMessage(M('Account Reset Successfull !'));
// });

// New Account
$bot->onCommand('new_account', NewAccountConversation::class);


// --------------------------------------------------------------------------------------
// Admin Commands

// Ping
$bot->onCommand('ping', function (Nutgram $bot) {
    if(! user_is_admin($bot->userId())){
        $bot->sendMessage("no_permission");
        return;
    }
    $bot->sendMessage(
        print_r(
            json_decode(
                json_encode($bot->update()->getMessage(), true, JSON_PRETTY_PRINT)
            )
        , true)    
    );
});

// Talk to User 
// $bot->onCommand('talk_to_user {user}', function (Nutgram $bot, $user) {

//     if(! user_is_admin($bot->userId())){
//         $bot->sendMessage("no_permission");
//         return;
//     }

//     if($user[0] =='@'){
//         $user_id = db()->select("users", 'user_id', [
//             "tg_username" => substr($user,1)
//         ])[0];
//         echo "USERID=". $user_id;
//     }
//     else{
//         $user_id = (int)$user;
//     }
    
//     $bot->sendMessage(
//         text:"The parameter is {$user}",
//         chat_id: (int)$user_id
//     );
// });

// Admin-Review Response 
$bot->onCommand('admin_approve', function (Nutgram $bot) {

    if(! user_is_admin($bot->userId())){
        $bot->sendMessage("no_permission");
        return;
    }

    $text = $bot->message()->text;
    $message_parts = explode("\n", $text);
    $command = $message_parts[0];
    $admin_answer = $message_parts[1];
    $admin_note = $message_parts[2] ?? null;

    $replied_on_message = $bot->message()->reply_to_message->text ?? null;


    // echo "\ncommand: $command \n";
    // echo "admin_answer: $admin_answer \n";
    // echo "admin_note: $admin_note \n";
    // echo "replied_on_message: $replied_on_message \n";

    if($replied_on_message === null){
        send_to_admins_group('Please send you response as a reply to the target user info !');
        return;
    }

    if(! in_array($admin_answer, ['NO', 'YES'])){
        send_to_admins_group('Please Say YES or NO !');
        return;
    }

    $pendeing_user_id =  explode("\n", $replied_on_message)[1];
    echo "pendeing_user_id: $pendeing_user_id \n";

    if($admin_answer === 'YES'){
        db()->update('users',
            ['status' => '2'],
            ['user_id' => $pendeing_user_id]
        );

        // db_update_user_status($pendeing_user_id ,'2');
        send_to_admins_group('Admin ' . $bot->userId() . 'Approved user: '. PHP_EOL . $pendeing_user_id);

        $bot->sendMessage(M('you_have_been_accepted'), $pendeing_user_id);
        if(strlen($admin_note) >3)
            $bot->sendMessage(M('message_from_admin') .': '. $admin_note , $pendeing_user_id);
    }
    elseif($admin_answer === 'NO'){

        db()->update('users',
            ['status' => '2'],
            ['user_id' => $pendeing_user_id]
        );
        // db_update_user_status($pendeing_user_id ,'3');
        send_to_admins_group('Admin ' . $bot->userId() . 'Rejected user: '. PHP_EOL . $pendeing_user_id);

        $bot->sendMessage(M('you_have_been_rejected'), $pendeing_user_id);
        if(strlen($admin_note) >3)
            $bot->sendMessage(M('message_from_admin') .': '. $admin_note , $pendeing_user_id);
    }

    
});

// Misc for testing
// $bot->onCommand('test', function (Nutgram $bot) {
//     $user_info = db()->select("users", '*', [
//         "user_id" => '507130741'
//     ]);
//     $user_info['chat_log'] = '***';
//     $text = print_r($user_info, true);
//     echo "BEFORE:\n$text\n\n";
//     $text = str_replace('Array', "<b>New Account Pending:</b>\n" . $user_info['user_id'] . "\n Info:" , $text);
//     echo "AFTER:\n$text\n\n";
//     send_to_admins_group(
//         text: $text
//     );

// });

// Helper function
function send_to_admins_group(...$params){
    global $config, $bot;
    $params['chat_id'] = $config['admins_group_chat_id']; //'507130741';
    $params['parse_mode'] = ParseMode::HTML; //'507130741';
    $bot->sendMessage(...$params);
}


// --------------------------------------------------------------------------------------
// General Commands

$bot->onMessage(function (Nutgram $bot) {
    $bot->sendMessage('You sent a message!');
});


// --------------------------------------------------------------------------------------
// Global Middleware

// $bot->middleware(function (Nutgram $bot, $next) {

//     $user_id = $bot->userId();

//     // Handle New users
//     if(! db_user_exist($user_id)){
//         $user_tg_username = $bot->user()->username;
//         db()->insert("users", [
//             "user_id" => $user_id,
//             "tg_username" =>  $user_tg_username,
//             "status" => 0,
//             "chat_log" => '',
//         ]);
//     }

//     // Logging Incoming Message
//     $text = '';
//     $incoming = $bot->message();

//     $date = $incoming->date;
//     $text = $incoming->text ?? '';
//     $photo = $incoming->photo ?? null;

//     if($photo){
//         $photo_id = end($photo)->file_id;;
//         $bot->getFile($photo_id)->save(DOWNLOAD_FOLDER . $photo_id. '.png');
//         $caption = $incoming->caption ?? '';

//         $text .= "[img-$photo_id]$caption";
//     }

//     dblog($text, $user_id, (int)$date);

//     $next($bot);
    
// });


// --------------------------------------------------------------------------------------
// Start Bot

$bot->run();