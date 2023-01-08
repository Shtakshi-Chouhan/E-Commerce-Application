<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;

class AdminUser extends stdClass{
    
    private $id;
    private $name;
    private $emailAddress;
    private $password;
    private $isActive;
    private $isSuperAdmin;
    private $lastLoginOn = null;

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): AdminUser
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): AdminUser
    {
        if(empty(trim($name))) {
            throw new Exception("Name must not be empty.");
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * 
     * @param string $emailAddress
     * @return AdminUser
     * @throws Exception
     */
    public function setEmailAddress(string $emailAddress): AdminUser
    {
        if(empty(trim($emailAddress))) {
            throw new Exception("Email address must not be empty");
        }
        if(filter_var($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
            throw new Exception("Invalid email address");
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @throws Exception
     */
    public function setPassword(string $password): AdminUser
    {
        if(empty($password)) {
            throw new Exception("Password cannot be empty.");
        }
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive(bool $isActive): AdminUser
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    /**
     * @param mixed $isSuperAdmin
     */
    public function setIsSuperAdmin(bool $isSuperAdmin): AdminUser
    {
        $this->isSuperAdmin = $isSuperAdmin;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastLoginOn(string $format)
    {
        if(is_null($this->lastLoginOn)){
            return ' --- ';
        }
        $timestamp = strtotime($this->lastLoginOn);
        return date($format, $timestamp);
    }

    /**
     * @param int $lastLoginOn
     */
    public function setLastLoginOn($lastLoginOn = null): AdminUser
    {
        $this->lastLoginOn = $lastLoginOn;
        if(is_null($lastLoginOn) === false) {
            $this->lastLoginOn = date('Y-m-d G:i:s', $lastLoginOn);
        }
        return $this;
    }

    public function insert(): AdminUser{
        $this->conn->createQueryBuilder()
            ->insert('user')
            ->values([
                'name' => '?',
                'email_address' => '?',
                'password' => '?',
                'is_active' => '?',
                'is_super_admin' => '?'
            ])->setParameters([
                $this->name,
                $this->emailAddress,
                $this->password,
                intval($this->isActive),
                intval($this->isSuperAdmin)
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update('user')
            ->set('name', '?')
            ->set('email_address', '?')
            ->set('password', '?')
            ->set('is_active', '?')
            ->set('is_super_admin', '?')
            ->set('last_login_on', '?')
            ->where('id = ?')
            ->setParameters([
                $this->name,
                $this->emailAddress,
                $this->password,
                intval($this->isActive),
                intval($this->isSuperAdmin),
                $this->lastLoginOn,
                $this->id
            ])->execute();
    }

    public function delete(){
        $this->conn->createQueryBuilder()
            ->delete('user')
            ->where('id = ?')
            ->setParameter(0, $this->id)
            ->execute();
    }
    
}