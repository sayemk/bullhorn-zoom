<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $username;
    private $password;
    private $dbName;
    private $port;
    /**
     * @var pdo
     */
    private PDO $conn;


    public function __construct() {


        $this->host = getenv("DATABASE_HOST");
        $this->port = getenv("DATABASE_PORT");
        $this->username = getenv("DATABASE_USERNAME");
        $this->password = getenv("DATABASE_PASSWORD");
        $this->dbName = getenv("DATABASE_NAME");

        var_dump($this->host);

        $this->connectDb();
    }

    private function connectDb() {
        try {
            $conn = new PDO("mysql:host=$this->host:$this->port;dbname=".$this->dbName, $this->username, $this->password);

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Database Connected successfully\r\n";
            $this->conn =  $conn;
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage()."\r\n";
            exit();
        }
    }


    public function saveContact($data = []) {

        $stmt = $this->conn->prepare("Insert Into contacts (bullhorn_id, phone, email, firstName, lastName, phone2, phone3, mobile, zoom_contact_id)
                                             VALUES (:bullhorn_id, :phone, :email, :firstName, :lastName, :phone2, :phone3, :mobile, :zoom_contact_id )");

        return $stmt->execute($data);


    }
    public function updateContact($data = []) {

//        print_r($data);
        $stmt = $this->conn->prepare("UPDATE contacts SET bullhorn_id = :bullhorn_id, phone = :phone, email = :email, firstName = :firstName, lastName = :lastName, 
                                                    phone2 = :phone2, phone3 = :phone3, mobile = :mobile, zoom_contact_id = :zoom_contact_id
                                        WHERE bullhorn_id = :id");
        $data["id"] = $data["bullhorn_id"];
        return $stmt->execute($data);


    }
    public function getContact($bullhornId) {
        $stmt = $this->conn->prepare("SELECT * FROM contacts where bullhorn_id = ?");
        $stmt->execute([$bullhornId]);

        $phone = $stmt->fetch(PDO::FETCH_ASSOC);

        return $phone;
    }
    public function searchNumber($number) {
        $stmt = $this->conn->prepare("SELECT * FROM contacts where `phone` = ? OR `mobile` = ?  OR `phone2` = ? OR `phone3` = ?");
        $stmt->execute([$number,$number,$number,$number]);

        $phone = $stmt->fetch(PDO::FETCH_ASSOC);

        return $phone;
    }
    public function getContacts() {

    }
}