<?php
require_once './core/core.php';
require_once './Database/database.php';
require 'vendor/autoload.php';
//-------------dep
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);

//----------------------
$dbUser = new database('master-instagram', 'root', '', 'users');

//-------------------------------

$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$api->login('alireza98moham', 'alireza123456'); // mandatory


//------------------

if (isset($content["message"])) {
    $chat_id = $content["message"]['chat']['id'];
    $text = $content["message"]['text'];
    $message_id = $content["message"]["message_id"];
    $username = $content["message"]["chat"]['username'];
    $first_name = $content["message"]["chat"]['first_name'];
} elseif (isset($content["callback_query"])) {
    $callback_id = $content["callback_query"]['id'];
    $chat_id = $content["callback_query"]["message"]['chat']['id'];
    $pv_id = $content["callback_query"]['from']['id'];
    $data = $content["callback_query"]["data"];
    $message_id = $content["callback_query"]["message"]['message_id'];
    $text = $content["callback_query"]["message"]["text"];
    $username = $content["callback_query"]["message"]["chat"]['username'];
    $first_name = $content["callback_query"]["message"]["chat"]['first_name'];
}

//----------User
$user = $dbUser->ShowUser($chat_id);
if ($text == "/start") {
    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => "Please Select language 🇺🇸🇮🇷", 'reply_markup' => ['inline_keyboard' => [
        [
            ['text' => '🇮🇷فارسی🇮🇷', 'callback_data' => "!lang_fa"]
        ],
        [
            ['text' => '🇺🇸English🇺🇸', 'callback_data' => "!lang_en"]
        ]
    ]]]);
}

if ($data == '!lang_fa' || $user['lang'] == 'fa') {
    $jsonFile = file_get_contents('language/fa.json');
    $jsonLanguage = json_decode($jsonFile, true);

} elseif ($data == '!lang_en' || $user['lang'] == 'en') {
    $jsonFile = file_get_contents('language/en.json');
    $jsonLanguage = json_decode($jsonFile, true);
}


if (isset($data)){
    switch ($data) {
        case (preg_match('~\!lang_.+~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['information'], 'callback_data' => "!information"]
                ],
                [
                    ['text' => $jsonLanguage['about'], 'callback_data' => "!about"]
                ]
            ]]]);
            $dbUser->AddUser($chat_id, $username, $first_name, substr($data, 6));
            break;
        //------------information -------------
        case (preg_match('~\!information~', $data) ? true : false):
             MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation'], 'reply_markup' => ['inline_keyboard' => [
                 [
                     ['text' => $jsonLanguage['home'],'callback_data' => "!information"]
                 ]]]]);
            break;
        //------------About -------------
        case (preg_match('~\!about~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['tellUs'], 'url' => "https://t.me/afsh7n"]
                ]]]]);
            break;
        //------------ListFollwer -------------
        case (preg_match('~ListFollwer-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data,13));
            $Followers = $api->getFollowers($instagram->getId());
            $flow = $Followers->getUsers();
            $myfile = fopen(substr($data,13).".txt", "w");
            for ($i= 0;$i <=23; $i++){
                fwrite($myfile, $flow[$i]->getUserName()."\n");
            }
            fclose($myfile);
            MassageRequestJson('sendDocument',['chat_id'=>$chat_id,'document'=>substr($data,13).".txt"]);
            break;
        case (preg_match('~ListFollwing-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data,14));
            $Followeing = $api->getFollowings($instagram->getId());
            $flow = $Followeing->getUsers();
            $myfile = fopen(substr($data,14).".txt", "w");
            for ($i= 0;$i <=23; $i++){
                fwrite($myfile, $flow[$i]->getUserName()."\n");
            }
            fclose($myfile);
            MassageRequestJson('sendDocument',['chat_id'=>$chat_id,'document'=>substr($data,14).".txt"]);
            break;
    }

}
if (isset($text)){
 switch ($text){
        case (preg_match('~@.*~', $text) ? true : false):
            $instagram = $api->getProfile(substr($text,1));
            MassageRequestJson('sendPhoto',['chat_id'=>$chat_id,'photo'=>$instagram->getProfilePicture(),'parse_mode' => 'html','caption'=>
                "<code>".$instagram->getFullName()."</code>"."\r\n \r\n".
                "<code>"."👥Followers: ".$instagram->getFollowers()."</code>"."\r\n \r\n".
                "<code>"."🕵️‍♀Following: ".$instagram->getFollowing()."</code>"."\r\n \r\n".
                "<code>".$instagram->getBiography()."</code>"."\r\n \r\n".
                "<code>".$instagram->getExternalUrl()."</code>"."\r\n"
                , 'reply_markup' => ['inline_keyboard' => [
                    [
                        ['text' => $jsonLanguage['listFollwer'], 'callback_data' => "ListFollwer-".$text],
                        ['text' => $jsonLanguage['listFollwing'], 'callback_data' => "ListFollwing-".$text]
                    ]]]]);
            break;
    }
}