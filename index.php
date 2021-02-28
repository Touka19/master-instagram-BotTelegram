<?php
require_once 'core.php';
//------- require Dependency 
require 'vendor/autoload.php';


use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


$cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
$api = new Api($cachePool);
$api->login('alireza98moham', 'alireza123456'); // mandatory
$data = $api->getProfile('afsh7n_');


$content = file_get_contents("php://input");
$update = json_decode($content,true);
$chat_id = $update["message"]['chat']['id'];
$text= $update["message"]['text'];

MassageRequestJson('sendMessage',['chat_id'=>$chat_id,'text'=>$data->getId()]);