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


}

$database = new database('master-instagram','root','');