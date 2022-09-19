<?php
namespace Mabdulamonem\Eloquent;

use Exception;
use Mabdulamonem\Eloquent\Database;
use Mabdulamonem\Eloquent\config;

abstract class Model
{
    /**
     * get Table name
     * @var string
     */
    protected  $table;

    /**
     * Table id
     * @var $id
     */
    protected  $id;
    /**
     * Table Columns
     * @var $column
     */
    protected  $column =[];

    /**
     * @var string id
     */
    protected  $key;
    /**
     * get dataBase Methods
     * @var Database
     */
    protected  $connection;

    /**
     * Mysql Statement Sorting
     * @var string
     */
    protected   $ASC = "ASC";

    /**
     * mysql Statement Sorting
     * @var string
     */
    protected   $DESC = "DESC";
    /**
     * @var
     */
    private  $error;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $config = new config;
        
        $this->connection = new Database();
    }


    public function __call($name, $arguments)
    {
        
        if (method_exists($this,$name)){
            return $this->$name($arguments);
        }
        throw new Exception('The ' . $name . ' is not supported.');
    }

    public static function __callStatic($name, $arguments)
    {
        if (method_exists(new self,$name)){
            return (new self)->$name($arguments);
        }
        throw new Exception('The ' . $name . ' is not supported.');
    }


    /**
     * @return array|null|\stdClass
     */
    public  function all()
    {
        return $this->connection()->fetchAll($this->table);
    }


    /**
     *
     * @return string
     */
    public  function desc(){
        return $this->DESC;
    }

    /**
     *
     * @return string
     */
    public  function asc(){
        return $this->ASC;
    }


    /**
     * @param $column
     * @param $value
     * @return null|\stdClass
     * @internal param $id
     */
    public  function where()
    {
        $args = func_get_args();

        return $this->connection()->select("*")->from($this->table)->where($args[0] . ' = ?',$args[1])->fetch($this->table);
    }

    /**
     * @param $column
     * @param $value
     * @return Database
     * @internal param $id
     */
    public  function delete($column, $value)
    {
        return $this->connection()->where($column . ' = ?', $value)->from($this->table)->delete();
    }

    /**
     * @param array $params
     * @return Exception|int|\SQLiteException
     */
    public  function insert($params = [])
    {
        try{
            return $this->connection()->data($params)->insert($this->table)->getLastID();
        }catch (\SQLiteException $e){
            return $this->error = $e;
        }
    }

    public  function update($params = [], $id = null)
    {
        return $this->connection()->data($params)->where($this->key . " = " ,$id)->update($this->table);
    }


    /**
     * @param $column
     * @param string $sort
     * @return array|null|\stdClass
     */
    public  function allOrdered($column = "id",$sort="ASC")
    {
        return $this->connection()->select("*")
            ->from($this->table)
            ->orderBy($column,$sort)
            ->fetchAll();
    }
    public  function getId(){
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * get Table Columns
     * @return mixed
     * @internal param null $id
     * @internal param bool $all
     */
    public  function columns()
    {
        return $this->column;
    }

    /**
     * Get Determined Table Column By Id
     * @param $id
     * @return mixed
     */
    public  function column($id)
    {
        return $this->columns()[$id];
    }

    /**
     * Get Database Methods
     *
     * @return Database
     */

    protected  function connection(){
        return $this->connection =  $this->db = new Database();
    }

    public  function error()
    {
        return $this->error;
    }

}