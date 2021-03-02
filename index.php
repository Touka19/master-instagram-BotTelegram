<?php
require_once './core/core.php';

$contentRequestTelegram = file_get_contents("php://input");
$content = json_decode($contentRequestTelegram,true);

if (isset($content["message"])){
    $chat_id = $content["message"]['chat']['id'];
    $text= $content["message"]['text'];
    $message_id = $content["message"]["message_id"];
}

MassageRequestJson('sendMessage',['chat_id'=>$chat_id,'text'=>$text]);