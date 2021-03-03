<?php
require_once './core/core.php';
require_once './Database/database.php';

$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);

//----------------------
$dbUser = new database('master-instagram', 'root', '', 'users');


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

        case (preg_match('~\!information~', $data) ? true : false):
             MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation']]);
            break;

        case (preg_match('~\!about~', $data) ? true : false):
            MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => ['inline_keyboard' => [
                [
                    ['text' => $jsonLanguage['tellUs'], 'url' => "https://t.me/afsh7n"]
                ]]]]);
            break;
    }

}

