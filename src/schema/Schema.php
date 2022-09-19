<?php


class Schema{

    private $sql = "CREATE TABLE IF NOT EXISTS ";



    static public function create($table_name,$callback)
    {
        
        self::$sql += "$tabel_name ( ";
        $callback();
        self::$sql += ");";
    }


}