<?php
namespace OS\models;

use Exception;
use Doctrine\DBAL\Connection;
use stdClass;

class CustomerSupport extends stdClass{
    
    private $id;
    private $customerId;
    private $orderId;
    private $subject;
    private $description;
    private $postedOn;
    private $isNew = true;
    
    /** @var Connection */
    private $conn;
    
    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getCustomerId(): int {
        return $this->customerId;
    }
    
    public function getCustomer(): Customer{
        return (new CustomerQuery($this->conn))->findById($this->customerId);
    }

    public function getOrderId(): int {
        return $this->orderId;
    }
    
    public function getSubject(): string {
        return $this->subject;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPostedOn($format="%d/%m/%Y %G:%i:%s"): string {
        $timestamp = strtotime($this->postedOn);
        return date($format, $timestamp);
    }

    public function getIsNew(): bool {
        return $this->isNew;
    }

    public function setId(int $id): CustomerSupport {
        $this->id = $id;
        return $this;
    }

    public function setCustomerId(int $customerId): CustomerSupport {
        $this->customerId = $customerId;
        return $this;
    }

    public function setOrderId(int $orderId): CustomerSupport {
        $this->orderId = $orderId;
        return $this;
    }

    public function setSubject(string $subject): CustomerSupport {
        if(empty(trim($subject))) {
            throw new Exception ('Subject field cannot be empty.');
        }
        $this->subject = $subject;
        return $this;
    }

    public function setDescription(string $description): CustomerSupport {
        if(empty(trim($description))) {
            throw new Exception('Description field cannot be empty');
        }
        if(strlen($description) < 50) {
            throw new Exception('Description cannot be less than 50 characters.');
        }
        $this->description = $description;
        return $this;
    }

    public function setPostedOn(int $timestamp): CustomerSupport {
        $this->postedOn = date("%Y-%m-%d %G:%i:%s", $timestamp);
        return $this;
    }

    public function setIsNew(bool $isNew): CustomerSupport {
        $this->isNew = $isNew;
        return $this;
    }
    
    public function insert(): CustomerSupport{
        $this->conn->createQueryBuilder()
            ->insert('customer_support')
            ->values([
                'customer_id' => '?',
                'order_id' => '?',
                'subject' => '?',
                'description' => '?',
                'posted_on' => '?',
                'is_new' => '?'
            ])->setParameters([
                $this->customerId,
                $this->orderId,
                $this->subject,
                $this->description,
                $this->postedOn,
                intval($this->isNew)
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update("customer_support")
            ->set("customer_id", "?")
            ->set("order_id", "?")
            ->set("subject", "?")
            ->set("description", "?")
            ->set("posted_on", "?")
            ->set("is_new", "?")
            ->where("id = ?")
            ->setParameters([
                $this->customerId,
                $this->orderId,
                $this->subject,
                $this->description,
                $this->postedOn,
                intval($this->isNew),
                $this->id
            ])->execute();
    }
    
    public function delete(){
        $this->conn->createQueryBuilder()
            ->delete("customer_support_reply")
            ->where("support_id = ?")
            ->setParameter(0, $this->id)
            ->execute();
        $this->conn->createQueryBuilder()
            ->delete("customer_support")
            ->where("id = ?")
            ->setParameter(0, $this->id)
            ->execute();
    }

}