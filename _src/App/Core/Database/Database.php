<?php
namespace App\Core\Database;

use PDO;

class Database{
    private $host;
    private $user;
    private $pass;
    private $dbname;

    private $dbh;
    private $error;
    private $stmt;

    public function __construct(){

        $db = config('database');

        $this->host = $db['host'] . ':' . $db['port'];
        $this->user = $db['user'];
        $this->pass = $db['password'];
        $this->dbname = $db['database_name'];

        // Set DSN
        try {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8, NAMES utf8"
        );
        // Create a new PDO instanace
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        }
        // Catch any errors
        catch(PDOException $e){
            $this->error = $e->getMessage();
            $er = $e->getMessage();
            error_reporting(0);
            $this->errorReporting("<b>Chyba při navazování spojení s databází.</b> <br>
                                    ");
        }
        } catch (Exception $e) {
            error_reporting(0);
            $this->errorReporting("<b>Chyba při navazování spojení s databází.</b> <br>
                                    ");
        }
    }
    function errorReporting($text) {
        echo '<!DOCTYPE html>
                <html>
                    <head>
                        <title>Chyba databáze</title>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width">
                        <style>
                        body {background: #E0E0E0;}
                        .error {
                        width: 400px;
                        height: 100px;
                        position: fixed;
                        left: 40%;
                        }
                        </style>
                    </head>
                    <body>
                        <div class="error">'.$text.'</div>
                    </body>
                </html>';
    }
    public function query($query){
        $this->stmt = $this->dbh->prepare($query);
    }
    public function bind($param, $value, $type = null){
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(){
        return $this->stmt->execute();
    }

    public function resultset(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount(){
        return $this->stmt->rowCount();
    }

    public function lastInsertId(){
        return $this->dbh->lastInsertId();
    }

    public function beginTransaction(){
        return $this->dbh->beginTransaction();
    }

    public function endTransaction(){
        return $this->dbh->commit();
    }

    public function cancelTransaction(){
        return $this->dbh->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->dbh->inTransaction();
    }

    public function debugDumpParams(){
        return $this->stmt->debugDumpParams();
    }
}
