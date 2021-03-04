<?php
require_once './core/core.php';
require_once './Database/database.php';
require_once './utils/ButtonArray.php';
require 'vendor/autoload.php';

//-------------dep
use Instagram\Model\Media;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);

//----------------------
$db = new database('master-instagram', 'root', '');


//-------------------------------

$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$api->login('alireza98moham', 'alireza123456'); // mandatory
$media = new Media();




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
$user = $db->ShowUser($chat_id);
$lang = $user['lang'];
$button = new ButtonArray($data, $user,$text);
$jsonLanguage = $button->getlanguage();
if (!$user) {
    if ($text == "/start") {
        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => "Please Select language 🇺🇸🇮🇷", 'reply_markup' => $button->buttonLanguage()]);
    }
} else {
    if ($text == "/start") {
        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
        $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
    }
}

//----------------- If IS Set DaTa
if (isset($data)) {
    switch ($data) {
        //-------------------------------------------Language--------------------------------------------------------
        case (preg_match('~\!lang_.+~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
            if (!$user){
                $db->AddUser($chat_id, $username, $first_name, substr($data, 6),"","",0,false,false);
            }
            $db->request($chat_id,1);
            break;
        //-------------------------------------------Information--------------------------------------------------------
        case (preg_match('~\!information~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation'], 'reply_markup' => $button->buttonInformation()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------About--------------------------------------------------------
        case (preg_match('~\!about~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => $button->buttonAbout()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------List Follower-------------------------------------------------------
        case (preg_match('~ListFollwer-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data, 13));
            $Followers = $api->getFollowers($instagram->getId());
            $flow = $Followers->getUsers();
            $myfile = fopen(substr($data, 13) . ".txt", "w");
            for ($i = 0; $i <= 23; $i++) {
                fwrite($myfile, $flow[$i]->getUserName() . "\n");
            }
            fclose($myfile);
            MassageRequestJson('sendDocument', ['chat_id' => $chat_id, 'document' => substr($data, 13) . ".txt"]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;

        //-------------------------------------------List Following--------------------------------------------------------
        case (preg_match('~ListFollwing-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data, 14));
            $Followeing = $api->getFollowings($instagram->getId());
            $flow = $Followeing->getUsers();
            $myfile = fopen(substr($data, 14) . ".txt", "w");
            for ($i = 0; $i <= 23; $i++) {
                fwrite($myfile, $flow[$i]->getUserName() . "\n");
            }
            fclose($myfile);
            MassageRequestJson('sendDocument', ['chat_id' => $chat_id, 'document' => substr($data, 14) . ".txt"]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------Management Account--------------------------------------------------------
        case (preg_match('~\!account~', $data) ? true : false):
            if (($user['accountUser'])?true:false){
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['managementAccount'], 'reply_markup' => $button->buttonManagementAccount()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }
            break;
        //-------------------------------------------Follow--------------------------------------------------------
        case (preg_match('~\!Follow~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserFollow'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------Like--------------------------------------------------------
        case (preg_match('~\!Like~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserLike'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------UnFollow--------------------------------------------------------
        case (preg_match('~\!UnFollow~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnFollow'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
        //-------------------------------------------Unlike--------------------------------------------------------
        case (preg_match('~\!Unlike~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
    }
}

//----------------- If IS Set Text
if (isset($text)) {
    switch ($text) {
    //----------------------------------------------------Information---------------------------------------------------
        case (preg_match('~@.*~', $text) ? true : false):
            $instagram = $api->getProfile(substr($text, 1));
            MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => $instagram->getProfilePicture(), 'parse_mode' => 'html', 'caption' =>
                "<code>" . $instagram->getFullName() . "</code>" . "\r\n \r\n" .
                "<code>" . "👥Followers: " . $instagram->getFollowers() . "</code>" . "\r\n \r\n" .
                "<code>" . "🕵️‍♀Following: " . $instagram->getFollowing() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getBiography() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getExternalUrl() . "</code>" . "\r\n"
                , 'reply_markup' => $button->buttonInformationMore()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
    //----------------------------------------------------Account-------------------------------------------------------
        case (preg_match('~account:.*:.*~', $text) ? true : false):
            $exp = explode(':',$text);
            $accountUser = $exp[1];
            $accountPass = $exp[2];
            $db->UpdateUser($chat_id, $username, $first_name, $lang,$accountUser,$accountPass,0,0,0);
            MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesAcoount'],'reply_markup' => $button->buttonBack()]);
            $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            break;
    //----------------------------------------------------UnFollow-------------------------------------------------------
        case (preg_match('~unfollow:.*~', $text) ? true : false):
            $exp = explode(':',$text);
            if (($user['accountUser'])?true:false){
                $api->unfollow($api->getProfile($exp[1])->getId());
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesUnFollow'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }
            break;
    //----------------------------------------------------Follow-------------------------------------------------------
        case (preg_match('~follow:.*~', $text) ? true : false):
            $exp = explode(':',$text);
            if (($user['accountUser'])?true:false){
                $api->follow($api->getProfile($exp[1])->getId());
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesFollow'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request(865627951,(int)$db->countRequest(865627951)['count_request']+1);
            }
            break;


    }
}