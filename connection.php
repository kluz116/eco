<?php


class Connection {

    protected $host = "localhost";
    protected $dbname = "ecostove_eco-pay-go";
    protected $user = "root";
    protected $pass = "jesus";
    protected $dbh;

    function __construct() {

        try {

            $this->dbh = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e) {

            echo $e->getMessage();
        }
    }

    public function closeConnection() {

        $this->dbh = null;
    }
}

?>