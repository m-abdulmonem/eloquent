<?php


class Blueprint{


    private $columns = [];

    private $value = 255;


    public function id()
    {
        $this->columns[] = "id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, ";
        return $this;
    }

    public function String(string $column_name)
    {
        $this->columns[] = "$column_name VARCHAR(255) NOT NULL,";
        return $this;
    }
    public function integer(string $column_name)
    {
        $this->columns[] = "$column_name int NOT NULL,";
        return $this;
    }


}