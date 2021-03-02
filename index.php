<?php
require_once './core/core.php';
require_once './Database/database.php';

$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram, true);
$language = 'fa';

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
            ['text' => '🇮🇷فارسی🇮🇷', 'callback_data' => "!fa"]
        ],
        [
            ['text' => '🇺🇸English🇺🇸', 'callback_data' => "!en"]
        ]
    ]]]);
}

if ($data == '!fa') {
    $jsonFile = file_get_contents('language/fa.json');
    $jsonLanguage = json_decode($jsonFile, true);

} elseif ($data == '!en') {
    $jsonFile = file_get_contents('language/en.json');
    $jsonLanguage = json_decode($jsonFile, true);
}

//-------------------
switch ($data) {
    case "!fa":
        MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['welcome'], 'reply_markup' => ['inline_keyboard' => [
            [
                ['text' => $jsonLanguage['information'], 'callback_data' => "!information"]
            ],
            [
                ['text' => $jsonLanguage['about'], 'callback_data' => "!about"]
            ]
        ]]]);
        $tr =$dbUser->AddUser($chat_id, $username, $first_name, 'fa');
        break;
    case '!information':
        MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['getInformation'], 'reply_markup' => ['inline_keyboard' => [
            [
                ['text' => $jsonLanguage['information'], 'callback_data' => "!information"]
            ],
            [
                ['text' => $jsonLanguage['about'], 'callback_data' => "!about"]
            ]
        ]]]);
        break;
    case '!about':
        MassageRequestJson('editMessageText', ['chat_id' => $chat_id, 'message_id' => $message_id, 'text' => $jsonLanguage['about'], 'reply_markup' => ['inline_keyboard' => [
            [
                ['text' => $jsonLanguage['information'], 'callback_data' => "!information"]
            ],
            [
                ['text' => $jsonLanguage['about'], 'callback_data' => "!about"]
            ]
        ]]]);
        break;

}


