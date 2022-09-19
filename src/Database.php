<?php

namespace Mabdulamonem\Eloquent;

//use AlMOSTAQBAL\Vendor\Helper\filter;
use PDO;
use PDOException;
use stdClass;

/**
 * Class Database
 * @package AlMOSTAQBAL\Vendor\Database
 */
class Database
{

    /**
     * PDO
     * @var \PDO
     */
    private static $connection;
    /**
     * Table Name
     *
     * @var string
     */
    private $table;
    /**
     * data Container
     * @var array
     */
    private $data = [];
    /**
     * Bindings Container
     *
     * @var array
     */
    private $bindings = [];
    /**
     * Where Container
     *
     * @var array
     */
    private $wheres = [];

    /**
     * get last data Inserted id
     * @var int
     */
    private $lastID;
    /**
     * Selects
     * @var array
     */
    private $selects = [];
    /**
     * Joins
     * @var array
     */
    private $joins = [];
    /**
     * Offset
     * @var int
     */
    private $offset;
    /**
     * Limit
     *
     * @var int
     */
    private $limit;
    /**
     * Total Rows
     *
     * @var int
     */
    private $rows = 0;
    /**
     * Order By
     *
     * @var array
     */
    private $orderBy = [];

    private  $host_name;

    private  $db_name;

    private $username;
    private  $password;

    /**
     * Database constructor.
     *
     * @internal param Application $app
     * @param $host_name
     * @param $db_name
     * @param $username
     * @param $password
     */
    public function __construct($host_name, $db_name, $username, $password)
    {

        $this->host_name = $host_name;
        $this->db_name= $db_name;
        $this->username = $username;
        $this->password = $password;

        if (! $this->isConnected()){
           $this->connect();
        }
    }

    /**
     *
     * @return bool
     */
    private function isConnected(): bool
    {
        return static::$connection instanceof PDO;
    }

    /**
     * static::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_OBJ);
     * @return void
     */
    private function connect()
    {
       $dsn = "mysql:host=".$this->host_name.";dbname=".$this->db_name;
       $user = $this->username;
       $pass = $this->password;
       try{
           static::$connection = new PDO($dsn,$user,$pass);
           static::$connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
           static::$connection->exec("SET NAMES utf8");
       } catch (PDOException $e){
           echo "Error : " . $e->getMessage();
       }
    }

    /**
     * @return PDO
     */
    public function connection(): PDO
    {
        return static::$connection;
    }


    /**
     * set The Table Name
     *
     * @param string $table
     * @return $this
     */
    public function table(string $table): Database
    {
        $this->table = $table;
        return$this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function from($table): Database
    {
        return $this->table($table);
    }

    /**
     * The Data Will Be Stored In Database
     * @param $key
     * @param $value
     * @return $this
     */
    public function data($key, $value = null): Database
    {
        if (is_array($key)){
            $this->data = array_merge($this->data,$key);
            $this->addToBindings($key);
        }else {
            $this->data[$key] = $value;
            $this->addToBindings($value);
        }
        return$this;
    }


    /**
     * Insert data Method Into database
     *
     * @param string|null $table
     * @return $this
     */
    public function insert(string $table = null): Database
    {
        if ($table){
            $this->table($table);
        }

        $sql = "INSERT INTO " .$this->table . " SET ";
        $sql .= $this->setFields();
        $sql = rtrim($sql, ', ');
        $this->query($sql,$this->bindings);
        $this->lastID = $this->connection()->lastInsertId();
        $this->rest();
        return $this;
    }

    /**
     * update data In database
     *
     * @param string|null $table
     * @return $this
     */
    public function update(string $table = null): Database
    {
        if ($table){
            $this->table($table);
        }

        $sql = "UPDATE " .$this->table . " SET ";

        $sql .= $this->setFields();

        if ($this->wheres){
            $sql .= " WHERE " . implode(' ',$this->wheres);
        }
        $query = $this->query($sql,$this->bindings);
        $this->rows = $query->rowCount();
        $this->rest();
        return $this;
    }
    /**
     * delete data From database
     *
     * @param string|null $table
     * @return $this
     */
    public function delete(string $table = null): Database
    {
        if ($table){
            $this->table($table);
        }

        $sql = "DELETE FROM " .$this->table . "  ";

        $sql .= $this->setFields();

        if ($this->wheres){
            $sql .= " WHERE " . implode(' ',$this->wheres);
        }
        $query = $this->query($sql,$this->bindings);
        $this->rows = $query->rowCount();
        $this->rest();
        return $this;
    }


    /**
     * @param null $table
     * @return stdClass | null
     */
    public function fetch($table = null)
    {
        if ($table) {
            $this->table($table);
        }
        $sql = $this->fetchStatement();
        $result = $this->query($sql,$this->bindings)->fetch();
        $this->rest();
        return $result;
    }

    /**
     * @param null $table
     * @return array|false
     */
    public function fetchAll($table = null)
    {
        if ($table) {
            $this->table($table);
        }
        $sql = $this->fetchStatement();
        $query = $this->query($sql,$this->bindings);
        $results = $query->fetchAll();
        $this->rows = $query->rowCount();
        $this->rest();
        return $results;
    }

    /**
     * Sum Total Rows
     *
     * @return int
     */
    public function count(): int
    {
        return $this->rows;
    }

    /**
     * Set Select Clause
     *
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        $this->selects[] = $select;
        return $this;
    }

    /**
     * set Join Clause
     *
     * @param $join
     * @return $this
     */
    public function join($join): Database
    {
        $this->joins[] = $join;
        return $this;
    }

    public function orderBy($orderBy,$sort = "ASC"): Database
    {
        $this->orderBy = [$orderBy,$sort];
        return $this;
    }

    /**
     * Set Limit And Offset
     *
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit(int $limit, int $offset): Database
    {
        $this->limit = $limit;
        $this->offset =$offset;
        return $this;
    }
    /**
     * @param $value
     */
    private function addToBindings($value)
    {
        if (is_array($value))
            $this->bindings = array_merge($this->bindings,array_values($value));
        else
            $this->bindings[] = $value;
    }

    /**
     * Execute Sql Statement
     *
     * @return \PDOStatement
     */
    public function query(): \PDOStatement
    {
        $bindings = func_get_args();
        $sql = array_shift($bindings);
        if (count($bindings) && is_array($bindings[0])){
            $bindings = $bindings[0];
        }
       try{
           $query = $this->connection()->prepare($sql);
           foreach ($bindings as $key  => $value){
               $query->bindValue($key + 1, $value);
           }
           $query->execute();
           return $query;
       } catch (PDOException $e){
            die($e->getMessage());
       }
    }

    /**
     * get Last Data inserted ID
     * @return int
     */
    public function getLastID(): int
    {
        return $this->lastID;
    }

    /**
     *
     * @return $this
     */
    public function where(): Database
    {
        $bindings = func_get_args();
        $sql = array_shift($bindings);
        $this->addToBindings($bindings);
        $this->wheres[] = $sql;
        return $this;
    }

    private function setFields(): string
    {
        $sql = '';
        foreach (array_keys($this->data) as $key ){
            $sql .= '`'.$key.'` = ? , ';
        }
        $sql = rtrim($sql, ', ');
        return $sql;
    }

    /**
     * @return string
     */
    private function fetchStatement(): string
    {
        $sql ="SELECT ";
        if ($this->selects){
            $sql .= implode(',',$this->selects);
        }else{
            $sql .="*";
        }
        $sql .= " FROM " . $this->table . ' ';

        if ($this->joins){
            $sql .= implode(' ', $this->joins);
        }

        if ($this->wheres){
            $sql .= ' WHERE ' . implode(' ',$this->wheres);
        }
        if ($this->limit){
            $sql .= " LIMIT " .$this->limit;
        }
        if ($this->offset){
            $sql .= " OFFSET " .$this->limit;
        }
        if ($this->orderBy){
            $sql .= " ORDER BY " . implode(' ',$this->orderBy);
        }
        return $sql;
    }

    public function general($statement,$query,$params = array())
    {
        $sql = $this->connection()->prepare($statement);

        if ($query == "all")
        {
            $sql->execute();
            $stat = $sql->fetchAll();
        }
        elseif ($query == "one") {
            $sql->execute(array($params));
            $stat = $sql->fetch();
        }
        return $stat;
    }

    /**
     *
     *
     *
     */
    private function rest(){
        $this->bindings = [];
        $this->wheres = [];
        $this->joins = [];
        $this->selects = [];
        $this->orderBy = [];
        $this->data = [];
        $this->table = null;
        $this->offset = null;
        $this->limit = null;
    }
    
    

}