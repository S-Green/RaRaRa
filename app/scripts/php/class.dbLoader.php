<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 9/8/16
 * Time: 11:44 AM
 */

class dbLoader{
    public $resultArray = array();

    public function __construct($host,$user,$pass,$db){
        $this->connection = new mysqli($host,$user,$pass,$db);

        if($this->connection->connect_errno){
            die("Connection Fail " . $this->connection->connect_error);
        }
        $this->connection->select_db($db);
    }

    public function createTable($sql){
        if($this->connection->query($sql) === TRUE){
            echo "Table has been created successfully";
        }

        else{
            echo "Error creating table: " . $this->connection->error;
        }
    }

    public function dbSelectTbls($tableName, array $columns){
        $columns = implode(",", $columns);
        $result = $this->connection->query("Select $columns FROM $tableName");

        if($this->connection->errno){
            die("Fail Select " . $this->connection->error);
        }
        $i = 0;

        foreach($result as $value){
            $resultArray[$i] = $value;
            $i++;
        }

        return $resultArray;
    }

    public function dbLogin($tableName){
        $result = $this->connection->query("Select * FROM $tableName");

        if($this->connection->errno){
            die("Fail Select " . $this->connection->error);
        }

        $i=0;

        foreach($result as $value){
            $resultArray[$i] = $value;
            $i++;
            //var_dump($value);
            //echo "<br/><br/>";
        }
        //var_dump($resultArray);
        return $resultArray;
    }

    public function custSelect($query){
        $result = $this->connection->query($query);
        if($this->connection->errno){
            die("Fail Select " . $this->connection->error);
        }


        else {
            if ($result) {
                $i = 0;
                foreach ($result as $value) {
                    $resultArray[$i] = $value;
                    $i++;
                }
                return $resultArray;
            }
            else{
                return false;
            }
        }
    }

    public function custSQL($query){
        $result = $this->connection->query($query);
        if($this->connection->errno){
            var_dump($query);
            die("Failed SQL Call " . $this->connection->error);
        }
        else
            return $result;
    }

    public function insertReturnId($query){
        $this->connection->query($query);

        if($this->connection->errno){
            die("Failed SQL Call: " . $this->connection->error);
        }

        else{
            $last_id = $this->connection->insert_id;
            return $last_id;
        }
    }

    public function dbInsert($tableName, array $val_cols){
        echo "VAL COLS: ";
        var_dump($val_cols);
        $keysStr = implode(", ", array_keys($val_cols));

        $i = 0;

        foreach($val_cols as $key=>$value){
            $strVal[$i] = "'" . $value . "'";
            echo "$key: $value";
            $i++;
        }

        $strVal = implode(", ", $strVal);


        if(mysqli_connect_errno()){
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        echo "INSERT INTO $tableName ($keysStr) VALUES ($strVal)";

        if(!$this->connection->query("INSERT INTO $tableName ($keysStr) VALUES ($strVal)") === TRUE){
            echo "Error " . $this->connection->error;
        }
        else{
            // Do something related for errors
            echo "Successfully Inserted: ";
            var_dump($keysStr);
            var_dump($strVal);
            $returnArray = array(
                'Keys'=>$keysStr,
                'Vals'=>$strVal
            );
            return $returnArray;
        }
    }

    public function dbDelete($tableName, array $val_cols){
        $i = 0;

        foreach($val_cols as $key=>$value){
            $exp[$i] = $key . " = '" . $value . "'";
            $i++;
        }

        $strExp = implode(" AND ", $exp);

        if($this->connection->query("DELETE FROM $tableName WHERE $strExp") === TRUE){
            if(mysqli_affected_rows($this->connection)){
                echo "Record has been deleted successfully<br/>";
            }

            else{
                echo "Error to delete " . $this->connection->error;
            }

            echo "<br/>";
        }
    }

    public function dbUpdate($tableName, array $set_val_cols, array $cod_val_cols){
        $i = 0;

        foreach($set_val_cols as $key => $value){
            $set[$i] = $key . " = '" . $value . "'";
            $i++;
        }

        $strSet = implode(", ", $set);

        $i = 0;

        foreach($cod_val_cols as $key => $value){
            $cod[$i] = $key . " = '" . $value . "'";
            $i++;
        }

        $strCod = implode(" AND ", $cod);

        if($this->connection->query("UPDATE $tableName SET $strSet WHERE $strCod") === TRUE){
            if(mysqli_affected_rows($this->connection)){
                echo "Record Updated Successfully<br>";
            }

            else{
                echo "The Record Failed To Update: " . $this->connection->error;
            }
        }

        else{
            echo "Error to update " . $this->connection->error;
        }
    }

    public function __destruct() {
        if($this->connection){
            $this->connection->close();
        }
    }
}

?>