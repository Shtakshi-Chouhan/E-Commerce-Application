<?php
namespace OS\models;

use Exception;
use Doctrine\DBAL\Connection;
use stdClass;

class ContactSubmission extends stdClass{
    
    private $id;
    private $firstName;
    private $lastName;
    private $emailAddress;
    private $phoneNumber = null;
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

    public function getFirstName(): string {
        return $this->firstName;
    }

    public function getLastName(): string {
        return $this->lastName;
    }

    public function getEmailAddress(): string {
        return $this->emailAddress;
    }

    public function getPhoneNumber(): string {
        return $this->phoneNumber;
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

    public function setId(int $id): ContactSubmission {
        $this->id = $id;
    }

    public function setFirstName(string $firstName): ContactSubmission {
        if(empty(trim($firstName))) {
            throw new Exception('First Name field cannot be empty.');
        }
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): ContactSubmission {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmailAddress(string $emailAddress): ContactSubmission {
        if(empty(trim($emailAddress))) {
            throw new Exception('Email Address field cannot be empty.');
        }
        if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function setPhoneNumber(string $phoneNumber): ContactSubmission {
        if(!preg_match('#^\d{10}$#', $phoneNumber)) {
            throw new Exception('Invalid phone number, must be of 10-digits.');
        }
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function setSubject($subject): ContactSubmission {
        if(empty(trim($subject))) {
            throw new Exception('Subject field cannot be empty.');
        }
        $this->subject = $subject;
        return $this;
    }

    public function setDescription($description): ContactSubmission {
        if(empty(trim($description))) {
            throw new Exception('Description field cannot be empty');
        }
        if(strlen($description) < 50) {
            throw new Exception('Description cannot be less than 50 characters.');
        }
        $this->description = $description;
        return $this;
    }

    public function setPostedOn(int $timestamp): ContactSubmission {
        $this->postedOn = date("%Y-%m-%d %G:%i:%s", $timestamp);
        return $this;
    }

    public function setIsNew(bool $isNew): ContactSubmission {
        $this->isNew = $isNew;
        return $this;
    }
    
    public function insert(): ContactSubmission{
        $this->conn->createQueryBuilder()
            ->insert('contact_submission')
            ->values([
                'first_name' => '?',
                'last_name' => '?',
                'email_address' => '?',
                'phone_number' => '?',
                'subject' => '?',
                'description' => '?',
                'posted_on' => '?',
                'is_new' => '?'
            ])->setParameters([
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->phoneNumber,
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
            ->update("contact_submission")
            ->set("first_name", "?")
            ->set("last_name", "?")
            ->set("email_address", "?")
            ->set("phone_number", "?")
            ->set("subject", "?")
            ->set("description", "?")
            ->set("posted_on", "?")
            ->set("is_new", "?")
            ->where("id = ?")
            ->setParameters([
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->phoneNumber,
                $this->subject,
                $this->description,
                $this->postedOn,
                intval($this->isNew),
                $this->id
            ])->execute();
    }
    
    public function delete(){
        $this->conn->createQueryBuilder()
            ->delete("contact_submission")
            ->where("id = ?")
            ->setParameter(0, $this->id)
            ->execute();
    }


}