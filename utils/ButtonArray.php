<?php
class ButtonArray{
    private $language ;
    private $user ;
    private $data;
    private $text;
    public function __construct($data,$user,$text)
    {
        $this->user = $user;
        $this->data = $data;
        $this->text = $text;
        if ($data == '!lang_fa' || $user['lang'] == 'fa') {
            $jsonFile = file_get_contents('language/fa.json');
            $this->language = json_decode($jsonFile, true);

        } elseif ($data == '!lang_en' || $user['lang'] == 'en') {
            $jsonFile = file_get_contents('language/en.json');
            $this->language = json_decode($jsonFile, true);
        }
    }

    public function getlanguage()
    {
        return $this->language;
    }

    public function buttonHome()
    {
        return ['inline_keyboard' => [
            [
                ['text' => $this->language['information'], 'callback_data' => "!information"],
                ['text' => $this->language['account'], 'callback_data' => "!account"]
            ],
            [
                ['text' => $this->language['createBio'], 'callback_data' => "!createBio"],
                ['text' => $this->language['downloadMedia'], 'callback_data' => "!downloadMedia"]
            ],
            [
                ['text' => $this->language['settings'], 'callback_data' => "!settings"],
                ['text' => $this->language['downloadMedia'], 'callback_data' => "!downloadMedia"]
            ]
        ]];
    }

    public function buttonLanguage()
    {
        return ['inline_keyboard' => [
            [
                ['text' => '🇮🇷فارسی🇮🇷', 'callback_data' => "!lang_fa"]
            ],
            [
                ['text' => '🇺🇸English🇺🇸', 'callback_data' => "!lang_en"]
            ]
        ]];
    }

    public function buttonInformation()
    {
       return ['inline_keyboard' => [
            [
                ['text' => $this->language['back'],'callback_data' => "!lang_".$this->user['lang']]
            ]]];
    }

    public function buttonAbout()
    {
        return['inline_keyboard' => [
            [
                ['text' => $this->language['tellUs'], 'url' => "https://t.me/afsh7n"]
            ]]];
    }

    public function buttonInformationMore()
    {
        return ['inline_keyboard' => [
            [
                ['text' => $this->language['listFollwer'], 'callback_data' => "ListFollwer-".$this->text],
                ['text' => $this->language['listFollwing'], 'callback_data' => "ListFollwing-".$this->text]
            ]
        ]];
    }

    public function buttonBack()
    {
        return ['inline_keyboard' => [
            [
                ['text' => $this->language['back'], 'callback_data' => "!lang_" . $this->user['lang']]
            ]]];
    }

    public function buttonManagementAccount()
    {
        return ['inline_keyboard' => [
            [
                ['text' => $this->language['Follow'], 'callback_data' => "!Follow"],
                ['text' => $this->language['Like'], 'callback_data' => "!Like"]
            ],
            [
                ['text' => $this->language['UnFollow'], 'callback_data' => "!UnFollow"],
                ['text' => $this->language['Unlike'], 'callback_data' => "!Unlike"]
            ],
            [
                ['text' => $this->language['back'], 'callback_data' => "!lang_" . $this->user['lang']]
            ]
        ]];
    }
}