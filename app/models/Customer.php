<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;
use Throwable;

class Customer extends stdClass{
    
    private $id;
    private $firstName;
    private $lastName;
    private $emailAddress;
    private $mobileNumber;
    private $alternateNumber;
    private $isActive;
    private $registeredOn;
    private $accountPassword;
    private $lastLoginOn = null;
    private $accountVerificationCode;
    private $accountVerified;

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

    public function getMobileNumber(): string {
        return $this->mobileNumber;
    }

    public function getAlternateNumber(): string {
        return $this->alternateNumber;
    }

    public function getIsActive(): bool {
        return $this->isActive;
    }

    public function getRegisteredOn(string $format="d/m/Y H:i:s"): string {
        $timestamp = strtotime($this->registeredOn);
        return date($format, $timestamp);
    }

    public function getAccountPassword(): string {
        return $this->accountPassword;
    }

    public function getLastLoginOn(string $format="d/m/Y H:i:s"): string {
        $timestamp = strtotime($this->lastLoginOn);
        return date($format, $this->lastLoginOn);
    }

    public function getAccountVerificationCode(): string {
        return $this->accountVerificationCode;
    }

    public function getAccountVerified(): bool {
        return $this->accountVerified;
    }

    /**
     * @throws Throwable
     */
    public function getBillingAddress(): CustomerBillingAddress{
        return (new CustomerBillingAddressQuery($this->conn))->getByCustomerId($this->id);
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getAllShippingAddress(): array{
        return (new CustomerShippingAddressQuery($this->conn))->getAllByCustomerId($this->getId());
    }

    /**
     * @param string $type
     * @return array
     * @throws Throwable
     */
    public function getShippingAddressByType(string $type): array{
        return (new CustomerShippingAddressQuery($this->conn))->getByAddressType($this->id, $type);
    }


    /**
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasBillingAddress(): bool{
        return (bool)$this->conn->createQueryBuilder()
            ->select('count(*)')
            ->from('customer_billing_address')
            ->where('customer_id = ?')
            ->setParameter(0, $this->id)
            ->execute()
            ->fetchOne();
    }

    public function setId(int $id): Customer {
        $this->id = $id;
        return $this;
    }

    public function setFirstName(string $firstName): Customer {
        if(empty(trim($firstName))) {
            throw new Exception ('First Name field must not be empty.');
        }
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName): Customer {
        if(empty(trim($lastName))) {
            throw new Exception('Last Name field must not be empty.');
        }
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmailAddress(string $emailAddress): Customer {
        if(empty(trim($emailAddress))) {
            throw new Exception('Email Address field must not be empty.');
        }
        if(!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address.');
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function setMobileNumber(string $mobileNumber): Customer {
        if(empty(trim($mobileNumber))) {
            throw new Exception('Mobile Number field must not be emtpy.');
        }
        if(!preg_match('#^\d{10}$#', $mobileNumber)) {
            throw new Exception('Invalid mobile number, must be 10-digits number.');
        }
        $this->mobileNumber = $mobileNumber;
        return $this;
    }

    public function setAlternateNumber(string $alternateNumber): Customer {
        if(!empty($alternateNumber) and !preg_match('#^\d{10}$#', $alternateNumber)) {
            throw new Exception('Invalid alternate number, must be 10-digits number.');
        }
        $this->alternateNumber = $alternateNumber;
        return $this;
    }

    public function setIsActive(bool $isActive): Customer {
        $this->isActive = $isActive;
        return $this;
    }

    public function setRegisteredOn(int $registeredOn): Customer {
        $this->registeredOn = date('Y-m-d G-i-s', $registeredOn);
        return $this;
    }

    public function setAccountPassword($accountPassword): Customer {
        $this->accountPassword = $accountPassword;
        return $this;
    }

    public function setLastLoginOn($lastLoginOn = null): Customer {
        if(!is_null($lastLoginOn)) {
            $this->lastLoginOn = date('Y-m-d G-i-s', $lastLoginOn);
        }
        return $this;
    }

    public function setAccountVerificationCode($accountVerificationCode): Customer {
        $this->accountVerificationCode = $accountVerificationCode;
        return $this;
    }

    public function setAccountVerified(bool $accountVerified): Customer {
        $this->accountVerified = $accountVerified;
        return $this;
    }
    
    public function insert(): Customer{
        $this->conn->createQueryBuilder()
            ->insert('customer')
            ->values([
                'first_name' => '?',
                'last_name' => '?',
                'email_address' => '?',
                'mobile_number' => '?',
                'alternate_number' => '?',
                'account_password' => '?',
                'account_verification_code' => '?'
            ])->setParameters([
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->mobileNumber,
                $this->alternateNumber,
                $this->accountPassword,
                $this->accountVerificationCode
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update('customer')
            ->set('first_name', '?')
            ->set('last_name', '?')
            ->set('email_address', '?')
            ->set('mobile_number', '?')
            ->set('alternate_number', '?')
            ->set('is_active', '?')
            ->set('registered_on', '?')
            ->set('account_password', '?')
            ->set('last_login_on', '?')
            ->set('account_verified', '?')
            ->where('id = ?')
            ->setParameters([
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->mobileNumber,
                $this->alternateNumber,
                intval($this->isActive),
                $this->registeredOn,
                $this->accountPassword,
                $this->lastLoginOn,
                intval($this->accountVerified),
                $this->id
            ])->execute();
    }
    
    public function delete(){
        //deleting customer orders
        
        //deleting customer billing address
        
        //deleting customer shipping address
        
        //deleting customer
        $this->conn->createQueryBuilder()
            ->delete('customer')
            ->where('id = ?')
            ->setParameter(0, $this->id)
            ->execute();
    }
}