<?php
require_once './core/core.php';
require_once './Database/database.php';
require_once './utils/ButtonArray.php';
require_once './utils/PersianCalendar.php';
require './vendor/autoload.php';

//-------------dep
use Instagram\Model\Media;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Phpfastcache\Helper\Psr16Adapter;

$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);
$db = new database('master-instagram', 'root', '');


if (isset($content["message"])) {
    $chat_id = $content["message"]['chat']['id'];
    $text = $content["message"]['text'];
    $message_id = $content["message"]["message_id"];
    if ($content["message"]["chat"]['username']) {
        $username = $content["message"]["chat"]['username'];
    } else {
        $username = "";
    }
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
$is_admin = $user['is_admin'];
//--------- ChannelSponsor
$channelSponsor = '@afsh7nDev';
//----------
$lang = $user['lang'];
if ($db->countRefrral($chat_id)) {
    $countRef = $db->countRefrral($chat_id);
    $countRef = sizeof($countRef);
    echo $countRef;
} else {
    $countRef = 0;
}

$button = new ButtonArray($data, $user, $text);
$jsonLanguage = $button->getlanguage();
$join = json_decode(MassageRequestJson('getChatMember', ['chat_id' => $channelSponsor, 'user_id' => $chat_id]), true);

//-----------------------Config Account ----------------------------------
$UserDefultAccount = "";
$PassDefultAccount = "";
$userDefult = 'ahmadreza90817';
$passDefult = 'alireza123456';
if (!$user['accountUser'] == "") {
    $UserDefultAccount = $user['accountUser'];
    $PassDefultAccount = $user['accountPass'];
} else {
    $UserDefultAccount = $userDefult;
    $PassDefultAccount = $passDefult;
}
$instadl = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());
$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$api->login($UserDefultAccount, $PassDefultAccount); // mandatory
$media = new Media();
$instadl = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), $UserDefultAccount, $PassDefultAccount, new Psr16Adapter('Files'));
$instadl->login();

//--------------------------------------------------------------------------------------------------------------------
if (!$join['ok']) {
    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['joinChannel'], 'reply_markup' => ['inline_keyboard' => [
        [
            ['text' => $jsonLanguage['join'], 'url' => "https://t.me/afsh7nDev"]
        ],
        [
            ['text' => $jsonLanguage['sussesJoin'], 'url' => "https://t.me/afsh7n_developbot?start"]
        ]
    ]]]);
} else {
    if (isset($data)) {
        switch ($data) {
            //-------------------------------------------Language--------------------------------------------------------
            case (preg_match('~\!lang_.+~', $data) ? true : false):
                $exp = explode("_", $data)[1];
                $db->UpdateUser($chat_id, $username, $first_name, $exp, "", "", $user['referral'], 50, 0, 0, 0, $user['data_join']);
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
                break;
            //-------------------------------------------Information--------------------------------------------------------
            case (preg_match('~\!information~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation'], 'reply_markup' => $button->buttonInformation()]);

                break;
            //-------------------------------------------Home --------------------------------------------------------
            case (preg_match('~\!Home~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
                break;

            //-------------------------------------------List Follower-------------------------------------------------------
            case (preg_match('~!ListFollwer-@.*~', $data) ? true : false):
                $instagram = $api->getProfile(substr($data, 14));
                $Followers = $api->getFollowers($instagram->getId());
                $flow = $Followers->getUsers();
                $filename = substr($data, 14) . ".txt";
                $myfile = fopen($filename, "w");
                for ($i = 0; $i <= 23; $i++) {
                    fwrite($myfile, $flow[$i]->getUserName() . "\n");
                }
                fclose($myfile);
                $FollowerTxt = file_get_contents($filename);
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $FollowerTxt]);
                unlink($filename);

                break;

            //-------------------------------------------List Following--------------------------------------------------------
            case (preg_match('~!ListFollwing-@.*~', $data) ? true : false):
                $instagram = $api->getProfile(substr($data, 15));
                $Followers = $api->getFollowings($instagram->getId());
                $flow = $Followers->getUsers();
                $filename = substr($data, 15) . ".txt";
                $myfile = fopen($filename, "w");
                for ($i = 0; $i <= 23; $i++) {
                    fwrite($myfile, $flow[$i]->getUserName() . "\n");
                }
                fclose($myfile);
                $FollowingTxt = file_get_contents($filename);
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $FollowingTxt]);
                unlink($filename);

                break;
            //-------------------------------------------Management Account--------------------------------------------------------
            case (preg_match('~\!account~', $data) ? true : false):
                if (($user['accountUser']) ? true : false) {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['managementAccount'], 'reply_markup' => $button->buttonManagementAccount()]);

                } else {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getAccount'], 'reply_markup' => $button->buttonBack()]);
                }
                break;
            //-------------------------------------------Follow--------------------------------------------------------
            case (preg_match('~\!Follow~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserFollow'], 'reply_markup' => $button->buttonBack()]);

                break;
            //-------------------------------------------Like--------------------------------------------------------
            case (preg_match('~\!Like~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserLike'], 'reply_markup' => $button->buttonBack()]);

                break;
            //-------------------------------------------UnFollow--------------------------------------------------------
            case (preg_match('~\!UnFollow~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnFollow'], 'reply_markup' => $button->buttonBack()]);
                break;
            //-------------------------------------------Unlike--------------------------------------------------------
            case (preg_match('~\!Unlike~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->buttonBack()]);
                break;
            //-------------------------------------------download Media--------------------------------------------------------
            case (preg_match('~\!downloadMedia~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getDownloadMedia'], 'reply_markup' => $button->buttonBack()]);
                break;
            //-------------------------------------------Create Bio--------------------------------------------------------
            case (preg_match('~\!createBio~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getHelpBio'], 'reply_markup' => $button->ButtonCreateBio()]);
                break;
            //-------------------------------------------CountBio_add--------------------------------------------------------
            case (preg_match('~\!countBio_add~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->ButtonCreateBio()]);
                $db->UpdateUser($chat_id, $username, $first_name, $user['lang'], $user['accountUser'], $user['accountPass'], $user['referral'], $user['countFonts'] + 1, 0, 0, 0);
                break;
            //-------------------------------------------countFonts_remove--------------------------------------------------------
            case (preg_match('~\!countFonts_remove~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['GetUserUnlike'], 'reply_markup' => $button->ButtonCreateBio()]);
                $db->UpdateUser($chat_id, $username, $first_name, $user['lang'], $user['accountUser'], $user['accountPass'], $user['referral'], $user['countFonts'] - 1, 0, 0, 0, $user['data_join']);
                break;
            //-------------------------------------------Help--------------------------------------------------------
            case (preg_match('~\!help~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getHelp'], 'reply_markup' => $button->buttonHelp()]);
                break;
            //-------------------------------------------Help Post--------------------------------------------------------
            case (preg_match('~\!HelpPost~', $data) ? true : false):
                MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => "AgACAgQAAxkBAAOGYEFDOGBpIy0F2B_o6vGxYbRggKoAAm61MRsRAhBSHhqLj3WMSbqW-x4nXQADAQADAgADeQADztkFAAEeBA", 'caption' => $jsonLanguage['captionPost']]);
                break;
            //-------------------------------------------Help Account--------------------------------------------------------
            case (preg_match('~\!HelpAccount~', $data) ? true : false):
                MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => "AgACAgQAAxkBAAOFYEFDEAeUQSZgLLXhkuc2tcDUS1MAAm21MRsRAhBS3Fz5UOQ-HV9blPwoXQADAQADAgADeQADHVsEAAEeBA", 'caption' => $jsonLanguage['captionAccount']]);
                break;
            //-------------------------------------------Setting-----------------------------------------------------------
            case (preg_match('~\!settings~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getSetting'], 'reply_markup' => $button->buttonSetting()]);
                break;
            //-------------------------------------------referral-----------------------------------------------------------
            case (preg_match('~\!referral~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getReferral'] . $countRef . "", 'reply_markup' => $button->buttonReferral()]);
                break;
            //-------------------------------------------link Referral-----------------------------------------------------------
            case (preg_match('~\!linkReferral~', $data) ? true : false):
                MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => "AgACAgQAAxkBAAOHYEFDUPG5vFGoz7uxO3llWYj4DUcAAr-6MRvkERBSIMTz-X2w_CFgyqYnXQADAQADAgADeQADQuUFAAEeBA", 'caption' => $jsonLanguage['textRefrral'] . "\n\r https://t.me/afsh7n_developbot?start=" . $chat_id]);
                break;
            //-------------------------------------------language-----------------------------------------------------------
            case (preg_match('~\!language~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getLanguage'], 'reply_markup' => $button->buttonLanguage()]);
                break;
            //-------------------------------------------changeChannel Sponsor----------------------------------------------------------
            case (preg_match('~\!changeChannel~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['changeChannelTxt'], 'reply_markup' => $button->buttonBackAdmin()]);
                break;
            //-------------------------------------------listUsers ---------------------------------------------------------------------
            case (preg_match('~\!listUsers~', $data) ? true : false):
                $y = mds_date('Y');
                $m = mds_date('m');
                $d = mds_date('d');
                $mon = $m - 1;
                $da = $d - 1;
                $month = $db->listUser($y . $mon . $d)[0]['COUNT(chat_id)'];
                $day = $db->listUser($y . $m . $da)[0]['COUNT(chat_id)'];
                $all = $db->listUser()[0]['COUNT(chat_id)'];
                if ($lang == "fa") {
                    $listUsersTxt = "📊کاربران اضافه شده در ۲۴ساعت اخیر: $day
📊کاربران اضافه شده در یک ماه اخیر: $month
📊کل کاربران ربات : $all";
                } else {
                    $listUsersTxt = "تعداد کاربران اضافه شده در ماه: 📊Users added in the last Day: $day
📊Users added in the last Month: $month
📊All Users Bot : $all";
                }
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $listUsersTxt, 'reply_markup' => $button->buttonBackAdmin()]);
                break;
            //-------------------------------------------changeChannel Sponsor----------------------------------------------------------
            case (preg_match('~\!back_admin~', $data) ? true : false):
                MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['panelAdmin'], 'reply_markup' => $button->buttonAdmin()]);
                break;
        }
    }

//----------------- If IS Set Text
    if (isset($text)) {
        switch ($text) {
            //---------------------------------------------------------------------------------------------------------------
            case (preg_match('~^/s~', $text) ? true : false):
                if (!$user) {
                    if (stripos($text, '/start ') === 0) {
                        $payload = str_replace('/start ', '', $text);
                        $db->AddUser($chat_id, "", "", 'en', '', '', $payload, 50, 0, 0, 0, mds_date("Ymd"));
                        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => "Please Select language 🇺🇸🇮🇷", 'reply_markup' => $button->buttonLanguage()]);
                        break;
                    } else {
                        $db->AddUser($chat_id, "", "", 'en', '', '', "", 50, 0, 0, 0, mds_date("Ymd"));
                        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => "Please Select language 🇺🇸🇮🇷", 'reply_markup' => $button->buttonLanguage()]);
                        break;
                    }
                } else {
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => $button->buttonHome()]);
                    break;
                }
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

                break;
            //----------------------------------------------------Account-------------------------------------------------------
            case (preg_match('~^account:.*:.*~', $text) ? true : false):
                $exp = explode(':', $text);
                $accountUser = $exp[1];
                $accountPass = $exp[2];
                $db->UpdateUser($chat_id, $username, $first_name, $user['lang'], $accountUser, $accountPass, $user['referral'], 50, 0, 0, 0, $user['data_join']);
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['sussesAcoount'], 'reply_markup' => $button->buttonBack()]);

                break;
            //----------------------------------------------------UnFollow-------------------------------------------------------
            case (preg_match('~^unfollow@.*~', $text) ? true : false):
                $exp = explode(':', $text);
                if (($user['accountUser']) ? true : false) {
                    $api->unfollow($api->getProfile($exp[1])->getId());
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['sussesUnFollow'], 'reply_markup' => $button->buttonBack()]);

                } else {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);

                }
                break;
            //----------------------------------------------------Follow-------------------------------------------------------
            case (preg_match('~^follow@.*~', $text) ? true : false):
                $exp = explode(':', $text);
                if (($user['accountUser']) ? true : false) {
                    $api->follow($api->getProfile($exp[1])->getId());
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['sussesFollow'], 'reply_markup' => $button->buttonBack()]);

                } else {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);

                }
                break;
            //----------------------------------------------------UnLike-------------------------------------------------------
            case (preg_match('~^unlike#.*~', $text) ? true : false):
                $exp = explode('#', $text);
                if (($user['accountUser']) ? true : false) {
                    $media->setLink(substr($exp[1], 0, 40));
                    $api->unlike($api->getMediaDetailed($media)->getId());
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['sussesUnlike'], 'reply_markup' => $button->buttonBack()]);

                } else {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);

                }
                break;
            //----------------------------------------------------Like-------------------------------------------------------
            case (preg_match('~^like#.*~', $text) ? true : false):
                $exp = explode('#', $text);
                if (($user['accountUser']) ? true : false) {
                    $media->setLink(substr($exp[1], 0, 40));
                    $mediaDetailed = $api->getMediaDetailed($media)->getId();
                    $api->like($mediaDetailed);
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['sussesLike'], 'reply_markup' => $button->buttonBack()]);

                } else {
                    MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['ErrorAccount'], 'reply_markup' => $button->buttonBack()]);

                }
                break;
            //----------------------------------------------------Media Download-------------------------------------------------------
            case (preg_match('~^https:\/\/www\.instagram\.com.*~', $text) ? true : false):
                $url = explode("?", $text)[0];
                $mediadl = $instadl->getMediaByUrl($url);
                $type = $mediadl->getType();
                if ($type == "image") {
                    $img = $mediadl->getImageHighResolutionUrl();
                    $caption = $mediadl->getCaption();
                    if (strlen($caption) > 1024) {
                        MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => $img, 'reply_markup' => ['inline_keyboard' => [
                            [
                                ['text' => "❤️Like:" . $mediadl->getLikesCount(), "url" => $mediadl->getLink()],
                                ['text' => "💬Comment:" . $mediadl->getCommentsCount(), "url" => $mediadl->getLink()]
                            ],
                            [
                                ['text' => "📌Location:" . $mediadl->getLocationName(), "url" => "https://t.me/afsh7n"]
                            ]
                        ]]]);
                        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $caption]);
                    } else {
                        MassageRequestJson('sendPhoto', ['chat_id' => $chat_id, 'photo' => $img, 'caption' => $caption, 'reply_markup' => ['inline_keyboard' => [
                            [
                                ['text' => "❤️Like:" . $mediadl->getLikesCount(), "url" => $mediadl->getLink()],
                                ['text' => "💬Comment:" . $mediadl->getCommentsCount(), "url" => $mediadl->getLink()]
                            ],
                            [
                                ['text' => "📌Location:" . $mediadl->getLocationName(), "url" => "https://t.me/afsh7n"]
                            ]
                        ]]]);
                    }
                } elseif ($type == "video") {
                    $video = $mediadl->getVideoStandardResolutionUrl();
                    $caption = $mediadl->getCaption();
                    if (strlen($caption) > 1024) {
                        MassageRequestJson('sendVideo', ['chat_id' => $chat_id, 'video' => $video, 'reply_markup' => ['inline_keyboard' => [
                            [
                                ['text' => "❤️Like:" . $mediadl->getLikesCount(), "url" => $mediadl->getLink()],
                                ['text' => "💬Comment:" . $mediadl->getCommentsCount(), "url" => $mediadl->getLink()]
                            ],
                            [
                                ['text' => "📌Location:" . $mediadl->getLocationName(), "url" => "https://t.me/afsh7n"]
                            ]
                        ]]]);
                        MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $caption]);
                    } else {
                        MassageRequestJson('sendVideo', ['chat_id' => $chat_id, 'video' => $video, 'caption' => $caption, 'reply_markup' => ['inline_keyboard' => [
                            [
                                ['text' => "❤️Like:" . $mediadl->getLikesCount(), "url" => $mediadl->getLink()],
                                ['text' => "💬Comment:" . $mediadl->getCommentsCount(), "url" => $mediadl->getLink()]
                            ],
                            [
                                ['text' => "📌Location:" . $mediadl->getLocationName(), "url" => "https://t.me/afsh7n"]
                            ]
                        ]]]);
                    }
                } elseif ($type == "sidecar") {
                    $sidecar = $mediadl->getSidecarMedias();
                    $caption = $mediadl->getCaption();
                    $arrayMedia = [];
                    for ($i = 0; $i <= sizeof($sidecar) - 1; $i++) {
                        if ($sidecar[$i]['type'] == "image") {
                            if ($i == 0) {
                                array_push($arrayMedia, ['type' => 'photo', 'media' => $sidecar[$i]['imageHighResolutionUrl'], 'caption' => $caption]);
                            } else {
                                array_push($arrayMedia, ['type' => 'photo', 'media' => $sidecar[$i]['imageHighResolutionUrl']]);
                            }

                        } else {
                            if ($i == 0) {
                                array_push($arrayMedia, ['type' => 'video', 'media' => $sidecar[$i]['videoStandardResolutionUrl'], 'caption' => $caption]);
                            } else {
                                array_push($arrayMedia, ['type' => 'video', 'media' => $sidecar[$i]['videoStandardResolutionUrl']]);
                            }
                        }
                    }
                    MassageRequestJson('sendMediaGroup', ['chat_id' => $chat_id, 'media' => json_encode($arrayMedia)]);
                }
                break;
            //----------------------------------------------------CreateBio(Font)-------------------------------------------------------
            case (preg_match('~^&[ا-ی]~', $text) ? true : false):
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['notSupportFonts'], 'reply_markup' => $button->buttonBack()]);
                break;

            //----------------------------------------------------CreateBio(Font)-------------------------------------------------------
            case (preg_match('~^\&.*~', $text) ? true : false):
                $countFont = $user['countFonts'];
                $txt = explode('&', $text)[1];
                $fontUrl = json_decode(file_get_contents("http://api.codebazan.ir/font/?text=" . $txt), true)['result'];
                $font = $fontUrl;
                $textFonts = "";
                for ($i = 1; $i <= $countFont; $i++) {
                    $textFonts = $textFonts . $font[$i] . "\n\r";
                }
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $textFonts, 'parse_mode' => 'HTML']);
                break;
            //----------------------------------------------------Admin Managment-------------------------------------------------------
            case (preg_match('~^admin$~', $text) ? true : false):
                if ($is_admin == 1) {
                    MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['panelAdmin'], 'reply_markup' => $button->buttonAdmin()]);
                    break;
                }
            //----------------------------------------------------Admin Managment-------------------------------------------------------
            case (preg_match('~^\(\@.*\)$~', $text) ? true : false):
                $exp = explode("@", $text)[1];
                $channelSponsor = $exp;
                MassageRequestJson('sendMessage', ['chat_id' => $chat_id, 'text' => $jsonLanguage['changeChannelSusses'], 'reply_markup' => $button->buttonBackAdmin()]);
                break;
        }
    }
}