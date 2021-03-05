<?php

class database
{

    protected $dbConnect;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $option;
    public function __construct($dbname, $user, $pass)
    {
        $this->dbName = $dbname;
        $this->dbUser = $user;
        $this->dbPass = $pass;
        $this->option = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"];
        $this->dbConnect = new PDO("mysql:host=localhost;dbname=".$this->dbName,$this->dbUser,$this->dbPass,$this->option);
    }


    public function ShowUser($chatID)
    {
        $result = $this->dbConnect->prepare("SELECT * FROM users WHERE chat_id=?");
        $result->bindValue(1,$chatID);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function AddUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$referral,$countFonts,$status,$premium,$is_admin,$date)
    {

        if($this->ShowUser($chatID) == false){
            $result = $this->dbConnect->prepare("INSERT INTO users SET chat_id=?,username=?,firstname=?,lang=?,accountUser=?,accountPass=?,referral=?,countFonts=?,status=?,premium=?,is_admin=?,data_join=?");
            $result->bindValue(1,$chatID);
            $result->bindValue(2,$username);
            $result->bindValue(3,$firstname);
            $result->bindValue(4,$lang);
            $result->bindValue(5,$accountUser);
            $result->bindValue(6,$accountPass);
            $result->bindValue(7,$referral);
            $result->bindValue(8,$countFonts);
            $result->bindValue(9,$status);
            $result->bindValue(10,$premium);
            $result->bindValue(11,$is_admin);
            $result->bindValue(12,$date);
            $result->execute();
            return true;
        }else{
            $this->UpdateUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$referral,$countFonts,$status,$premium,$is_admin,$date);
        }
    }

    public function UpdateUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$referral,$countFonts,$status,$premium,$is_admin,$date)
    {
        $result = $this->dbConnect->prepare("UPDATE users SET username=?,firstname=?,lang=?,accountUser=?,accountPass=?,referral=?,countFonts=?,status=?,premium=?,is_admin=?,data_join=? WHERE chat_id=?");
        $result->bindValue(1,$username);
        $result->bindValue(2,$firstname);
        $result->bindValue(3,$lang);
        $result->bindValue(4,$accountUser);
        $result->bindValue(5,$accountPass);
        $result->bindValue(6,$referral);
        $result->bindValue(7,$countFonts);
        $result->bindValue(8,$status);
        $result->bindValue(9,$premium);
        $result->bindValue(10,$is_admin);
        $result->bindValue(11,$date);
        $result->bindValue(12,$chatID);
        $result->execute();
        return true;
    }

    public function DeleteUser($chatID)
    {
        $result = $this->dbConnect->prepare("DELETE FROM users WHERE chat_id=?");
        $result->bindValue(1,$chatID);
        $result->execute();
        return true;
    }

    public function countRefrral($ref)
    {
        $result = $this->dbConnect->prepare("SELECT chat_id FROM users WHERE referral=?");
        $result->bindValue(1,$ref);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

    public function listUser($date = "")
    {
        if($date ==""){
            $result = $this->dbConnect->prepare("SELECT COUNT(chat_id) FROM users");
            $result->execute();
            $row = $result->fetchAll(PDO::FETCH_ASSOC);
            return $row;
        }else{
            $result = $this->dbConnect->prepare("SELECT COUNT(chat_id) FROM users WHERE data_join >= ?");
            $result->bindValue(1,$date);
            $result->execute();
            $row = $result->fetchAll(PDO::FETCH_ASSOC);
            return $row;
        }
    }
}

