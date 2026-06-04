<?php
class User{
    private $id;
    public $username;
    public $password;
    public $cu_id;
    public $cu_uname;
    public $last_joined;
    public $role;
}

enum UserRole{
    case ADMIN;
    case OPERATOR;
};
?>