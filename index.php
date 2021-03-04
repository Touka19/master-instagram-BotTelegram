<?php
require_once './core/core.php';
require_once './Database/database.php';
require_once './utils/ButtonArray.php';
require './vendor/autoload.php';

//-------------dep
use Instagram\Model\Media;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Phpfastcache\Helper\Psr16Adapter;

$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);

//----------------------
$db = new database('master-instagram', 'root', '');
$instadl = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());

//------------------------------------------Get Information Account ----------------------------------------------------
$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$api->login('alireza98moham', 'alireza123456'); // mandatory
$media = new Media();
//-----------------------------------------Download media -------------------------------------------------------------
$instadl = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'alireza98moham', 'alireza123456', new Psr16Adapter('Files'));
$instadl->login();

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
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------About--------------------------------------------------------
        case (preg_match('~\!about~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => $button->buttonAbout()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------List Follower-------------------------------------------------------
        case (preg_match('~!ListFollwer-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data, 14));
            $Followers = $api->getFollowers($instagram->getId());
            $flow = $Followers->getUsers();
            $filename= substr($data, 14) . ".txt";
            $myfile = fopen($filename, "w");
            for ($i = 0; $i <= 23; $i++) {
                fwrite($myfile, $flow[$i]->getUserName() . "\n");
            }
            fclose($myfile);
            $FollowerTxt = file_get_contents($filename);
            MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $FollowerTxt]);
            unlink($filename);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;

        //-------------------------------------------List Following--------------------------------------------------------
        case (preg_match('~!ListFollwing-@.*~', $data) ? true : false):
            $instagram = $api->getProfile(substr($data, 15));
            $Followers = $api->getFollowings($instagram->getId());
            $flow = $Followers->getUsers();
            $filename= substr($data, 15) . ".txt";
            $myfile = fopen($filename, "w");
            for ($i = 0; $i <= 23; $i++) {
                fwrite($myfile, $flow[$i]->getUserName() . "\n");
            }
            fclose($myfile);
            $FollowingTxt = file_get_contents($filename);
            MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $FollowingTxt]);
            unlink($filename);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------Management Account--------------------------------------------------------
        case (preg_match('~\!account~', $data) ? true : false):
            if (($user['accountUser'])?true:false){
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['managementAccount'], 'reply_markup' => $button->buttonManagementAccount()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
        //-------------------------------------------Follow--------------------------------------------------------
        case (preg_match('~\!Follow~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserFollow'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------Like--------------------------------------------------------
        case (preg_match('~\!Like~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserLike'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------UnFollow--------------------------------------------------------
        case (preg_match('~\!UnFollow~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnFollow'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //-------------------------------------------Unlike--------------------------------------------------------
        case (preg_match('~\!Unlike~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        case (preg_match('~\!downloadMedia~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
    }
}

//----------------- If IS Set Text
if (isset($text)) {
    switch ($text) {
        //----------------------------------------------------Information---------------------------------------------------
        case (preg_match('~^@.*~', $text) ? true : false):
            $instagram = $api->getProfile(substr($text, 1));
            MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => $instagram->getProfilePicture(), 'parse_mode' => 'html', 'caption' =>
                "<code>" . $instagram->getFullName() . "</code>" . "\r\n \r\n" .
                "<code>" . "👥Followers: " . $instagram->getFollowers() . "</code>" . "\r\n \r\n" .
                "<code>" . "🕵️‍♀Following: " . $instagram->getFollowing() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getBiography() . "</code>" . "\r\n \r\n" .
                "<code>" . $instagram->getExternalUrl() . "</code>" . "\r\n"
                , 'reply_markup' => $button->buttonInformationMore()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //----------------------------------------------------Account-------------------------------------------------------
        case (preg_match('~^account:.*:.*~', $text) ? true : false):
            $exp = explode(':',$text);
            $accountUser = $exp[1];
            $accountPass = $exp[2];
            $db->UpdateUser($chat_id, $username, $first_name, $lang,$accountUser,$accountPass,0,0,0);
            MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesAcoount'],'reply_markup' => $button->buttonBack()]);
            $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            break;
        //----------------------------------------------------UnFollow-------------------------------------------------------
        case (preg_match('~^unfollow@.*~', $text) ? true : false):
            $exp = explode(':',$text);
            if (($user['accountUser'])?true:false){
                $api->unfollow($api->getProfile($exp[1])->getId());
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesUnFollow'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
        //----------------------------------------------------Follow-------------------------------------------------------
        case (preg_match('~^follow@.*~', $text) ? true : false):
            $exp = explode(':',$text);
            if (($user['accountUser'])?true:false){
                $api->follow($api->getProfile($exp[1])->getId());
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesFollow'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
        //----------------------------------------------------UnLike-------------------------------------------------------
        case (preg_match('~^unlike#.*~', $text) ? true : false):
            $exp = explode('#',$text);
            if (($user['accountUser'])?true:false){
                $media->setLink(substr($exp[1],0,40));
                $api->unlike($api->getMediaDetailed($media)->getId());
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesUnlike'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
        //----------------------------------------------------Like-------------------------------------------------------
        case (preg_match('~^like#.*~', $text) ? true : false):
            $exp = explode('#',$text);
            if (($user['accountUser'])?true:false){
                $media->setLink(substr($exp[1],0,40));
                $mediaDetailed = $api->getMediaDetailed($media)->getId();
                $api->like($mediaDetailed);
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $jsonLanguage['sussesLike'],'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }else{
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
        //----------------------------------------------------Like-------------------------------------------------------
        case (preg_match('~^https:\/\/www\.instagram\.com.*~', $text) ? true : false):
            $url = explode("?",$text)[0];
            $mediadl = $instadl->getMediaByUrl($url);
            $type = $mediadl->getType();
            if ($type=="image"){
                $img = $mediadl->getImageHighResolutionUrl();
                $caption = $mediadl->getCaption();
                if (strlen($caption) > 1024){
                    MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' =>$img,'reply_markup'=>['inline_keyboard' => [
                        [
                            ['text' => "❤️Like:".$mediadl->getLikesCount() , "url"=>$mediadl->getLink()],
                            ['text' => "💬Comment:".$mediadl->getCommentsCount() , "url"=>$mediadl->getLink()]
                        ],
                        [
                            ['text' => "📌Location:".$mediadl->getLocationName() , "url"=>"https://t.me/afsh7n"]
                        ]
                    ]]]);
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $caption]);
                }else{
                    MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' =>$img ,'caption'=>$caption,'reply_markup'=>['inline_keyboard' => [
                        [
                            ['text' => "❤️Like:".$mediadl->getLikesCount() , "url"=>$mediadl->getLink()],
                            ['text' => "💬Comment:".$mediadl->getCommentsCount() , "url"=>$mediadl->getLink()]
                        ],
                        [
                            ['text' => "📌Location:".$mediadl->getLocationName() , "url"=>"https://t.me/afsh7n"]
                        ]
                    ]]]);
                }
            }elseif ($type=="video"){
                $video = $mediadl->getVideoStandardResolutionUrl();
                $caption = $mediadl->getCaption();
                if (strlen($caption) > 1024){
                    MassageRequestJson('sendVideo', ['chat_id' => $chat_id, 'video' =>$video,'reply_markup'=>['inline_keyboard' => [
                        [
                            ['text' => "❤️Like:".$mediadl->getLikesCount() , "url"=>$mediadl->getLink()],
                            ['text' => "💬Comment:".$mediadl->getCommentsCount() , "url"=>$mediadl->getLink()]
                        ],
                        [
                            ['text' => "📌Location:".$mediadl->getLocationName() , "url"=>"https://t.me/afsh7n"]
                        ]
                    ]]]);
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $caption]);
                }else{
                    MassageRequestJson('sendVideo', ['chat_id' => $chat_id, 'video' =>$video ,'caption'=>$caption,'reply_markup'=>['inline_keyboard' => [
                        [
                            ['text' => "❤️Like:".$mediadl->getLikesCount() , "url"=>$mediadl->getLink()],
                            ['text' => "💬Comment:".$mediadl->getCommentsCount() , "url"=>$mediadl->getLink()]
                        ],
                        [
                            ['text' => "📌Location:".$mediadl->getLocationName() , "url"=>"https://t.me/afsh7n"]
                        ]
                    ]]]);
                }
            }elseif ($type=="sidecar") {
                $sidecar = $mediadl->getSidecarMedias();
                $caption = $mediadl->getCaption();
                $arrayMedia=[];
                for ($i=0;$i<=sizeof($sidecar)-1;$i++){
                    if ($sidecar[$i]['type']=="image"){
                        if ($i ==0){
                            array_push($arrayMedia,['type' => 'photo', 'media' => $sidecar[$i]['imageHighResolutionUrl'],'caption'=>$caption]);
                        }else{
                            array_push($arrayMedia,['type' => 'photo', 'media' => $sidecar[$i]['imageHighResolutionUrl']]);
                        }

                    }else{
                        if ($i ==0) {
                            array_push($arrayMedia, ['type' => 'video', 'media' => $sidecar[$i]['videoStandardResolutionUrl'],'caption'=>$caption]);
                        }else{
                            array_push($arrayMedia, ['type' => 'video', 'media' => $sidecar[$i]['videoStandardResolutionUrl']]);
                        }
                    }
                }
                MassageRequestJson('sendMediaGroup', ['chat_id' => $chat_id, 'media' => json_encode($arrayMedia)]);
                //MassageRequestJson('sendMessage', ['chat_id' => $chat_id,'text' => $caption]);
            }
            break;


        default:
            if (!$user) {
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => "Please Select language 🇺🇸🇮🇷", 'reply_markup' => $button->buttonLanguage()]);
            } else {
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
                $count = $db->request($chat_id,(int)$db->countRequest($chat_id)['count_request']+1);
            }
            break;
    }
}