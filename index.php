
<?php
use Mabdulamonem\Eloquent\Model;


require "vendor/autoload.php";


class User extends Model{

    protected  $table = "users";
}




$user = new User;

var_dump($user->all());