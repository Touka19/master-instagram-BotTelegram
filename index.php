<?php
require_once './core/core.php';
require_once './Database/database.php';
require_once './utils/ButtonArray.php';
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
    }
}

//----------------- If IS Set DaTa
if (isset($data)) {
    switch ($data) {
        //-------------------------------------------Language--------------------------------------------------------
        case (preg_match('~\!lang_.+~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
            $dbUser->AddUser($chat_id, $username, $first_name, substr($data, 6),"","",0,1,false,false);
            break;
        //-------------------------------------------Information--------------------------------------------------------
        case (preg_match('~\!information~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation'], 'reply_markup' => $button->buttonInformation()]);
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
            break;
        //-------------------------------------------About--------------------------------------------------------
        case (preg_match('~\!about~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => $button->buttonAbout()]);
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
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
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
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
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
            break;
        //-------------------------------------------account--------------------------------------------------------
        case (preg_match('~\!account~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['account'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['getAccount'], 'callback_data' => "!getaccount"]]
                ]]]);
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
            break;
        //-------------------------------------------account--------------------------------------------------------
        case (preg_match('~\!getaccount~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getAccount'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['back'], 'callback_data' => "!lang_" . $user['lang']]
                ]]]]);
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
            break;

    }
}

//----------------- If IS Set Text
if (isset($text)) {
    switch ($text) {
        case (preg_match('~@.*~', $text) ? true : false):
            $instagram = $api->getProfile(substr($text, 1));
            MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => $instagram->getProfilePicture(), 'parse_mode' => 'html', 'caption' =>
                "<code>" . $instagram->getFullName() . "</code>" . "\r\n \r\n" .
                "<code>" . "👥Followers: " . $instagram->getFollowers() . "</code>" . "\r\n \r\n" .
                "<code>" . "🕵️‍♀Following: " . $instagram->getFollowing() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getBiography() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getExternalUrl() . "</code>" . "\r\n"
                , 'reply_markup' => $button->buttonInformationMore()]);
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,"","",0,1,false,false);
            break;
        case (preg_match('~.*:.*~', $text) ? true : false):
            $exp = explode(':',$text);
            $accountUser = $exp[0];
            $accountPass = $exp[1];
            $dbUser->UpdateUser($chat_id, $username, $first_name, $lang,$accountUser,$accountPass,0,1,false,false);
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['sussesAcoount'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['back'], 'callback_data' => "!lang_" . $user['lang']]
                ]]]]);
            break;
    }
}