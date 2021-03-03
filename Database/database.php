<?php

class database
{

    protected $dbConnect;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $nameTable;
    private $option;
    public function __construct($dbname, $user, $pass,$nameTable)
    {
        $this->dbName = $dbname;
        $this->dbUser = $user;
        $this->dbPass = $pass;
        $this->nameTable = $nameTable;
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

    public function AddUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$status,$count_request,$premium,$is_admin)
    {

        if($this->ShowUser($chatID) == false){
            $result = $this->dbConnect->prepare("INSERT INTO users SET chat_id=?,username=?,firstname=?,lang=?,accountUser=?,accountPass=?,status=?,count_request=?,premium=?,is_admin=?");
            $result->bindValue(1,$chatID);
            $result->bindValue(2,$username);
            $result->bindValue(3,$firstname);
            $result->bindValue(4,$lang);
            $result->bindValue(5,$accountUser);
            $result->bindValue(6,$accountPass);
            $result->bindValue(7,$status);
            $result->bindValue(8,$count_request);
            $result->bindValue(9,$premium);
            $result->bindValue(10,$is_admin);
            $result->execute();
            return true;
        }else{
            $this->UpdateUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$status,$count_request,$premium,$is_admin);
        }
    }

    public function UpdateUser($chatID,$username,$firstname,$lang,$accountUser,$accountPass,$status,$count_request,$premium,$is_admin)
    {
        $result = $this->dbConnect->prepare("UPDATE users SET username=?,firstname=?,lang=?,accountUser=?,accountPass=?,status=?,count_request=?,premium=?,is_admin=? WHERE chat_id=?");
        $result->bindValue(1,$username);
        $result->bindValue(2,$firstname);
        $result->bindValue(3,$lang);
        $result->bindValue(4,$accountUser);
        $result->bindValue(5,$accountPass);
        $result->bindValue(6,$status);
        $result->bindValue(7,$count_request);
        $result->bindValue(8,$premium);
        $result->bindValue(9,$is_admin);
        $result->bindValue(10,$chatID);
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




}
$dbUser = new database('master-instagram','root','','users');