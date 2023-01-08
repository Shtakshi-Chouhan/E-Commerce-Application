<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;

class CustomerShippingAddress extends stdClass{
    
    const TYPE_HOME_ADDRESS = "home";
    const TYPE_OFFICE_ADDRESS = "office";
    const TYPE_OTHER_ADDRESS = "other";
    
    private $id;
    private $customerId;
    private $type;
    private $firstName;
    private $lastName;
    private $emailAddress;
    private $mobileNumber;
    private $addressLine1;
    private $addressLine2;
    private $landmark;
    private $city;
    private $state;
    private $country;
    private $pincode;

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

    public function getType(): string {
        return $this->type;
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
    
    public function getAddressLine1(): string {
        return $this->addressLine1;
    }

    public function getAddressLine2(): string {
        return $this->addressLine2;
    }

    public function getLandmark(): string {
        return $this->landmark;
    }

    public function getCity(): string {
        return $this->city;
    }

    public function getState(): string {
        return $this->state;
    }

    public function getCountry(): string {
        return $this->country;
    }

    public function getPincode(): string {
        return $this->pincode;
    }

    public function setId(int $id): CustomerShippingAddress {
        $this->id = $id;
        return $this;
    }

    public function setCustomerId(int $customerId): CustomerShippingAddress {
        $this->customerId = $customerId;
        return $this;
    }

    public function setType(string $type): CustomerShippingAddress {
        $this->type = $type;
        return $this;
    }

    public function setFirstName($firstName): CustomerShippingAddress {
        if(empty(trim($firstName))){
            throw new Exception("First Name field cannot be empty.");
        }
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName): CustomerShippingAddress {
        if(empty(trim($lastName))){
            throw new Exception("Last Name field cannot be empty.");
        }
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmailAddress($emailAddress): CustomerShippingAddress {
        if(!empty(trim($emailAddress)) and !filter_var($emailAddress, FILTER_VALIDATE_EMAIL)){
            throw new Exception("Invalid email address.");
        }
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function setMobileNumber($mobileNumber): CustomerShippingAddress {
        if(empty(trim($mobileNumber))){
            throw new Exception("Mobile Number field cannot be empty.");
        }
        if(!preg_match('#^\d{10}$#', $mobileNumber)){
            throw new Exception("Invalid mobile number, must be 10-digits number.");
        }
        $this->mobileNumber = $mobileNumber;
        return $this;
    }
    
    public function setAddressLine1($addressLine1): CustomerShippingAddress {
        if(empty(trim($addressLine1))){
            throw new Exception("Address Line #1 field cannot be empty.");
        }
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function setAddressLine2($addressLine2): CustomerShippingAddress {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function setLandmark($landmark): CustomerShippingAddress {
        if(empty(trim($landmark))){
            throw new Exception("Landmark field cannot be empty.");
        }
        $this->landmark = $landmark;
        return $this;
    }

    public function setCity($city): CustomerShippingAddress {
        if(empty(trim($city))){
            throw new Exception("City field cannot be empty.");
        }
        $this->city = $city;
        return $this;
    }

    public function setState($state): CustomerShippingAddress {
        if(empty(trim($state))){
            throw new Exception("State field cannot be empty.");
        }
        $this->state = $state;
        return $this;
    }

    public function setCountry($country): CustomerShippingAddress {
        if(empty(trim($country))){
            throw new Exception("Country field cannot be empty.");
        }
        $this->country = $country;
        return $this;
    }

    public function setPincode($pincode): CustomerShippingAddress {
        if(empty(trim($pincode))){
            throw new Exception("Pincode field cannot be empty.");
        }
        $this->pincode = $pincode;
        return $this;
    }
    
    public function insert(): CustomerShippingAddress{
        $this->conn->createQueryBuilder()
            ->insert('customer_shipping_address')
            ->values([
                'customer_id' => '?',
                'type' => '?',
                'first_name' => '?',
                'last_name' => '?',
                'email_address' => '?',
                'mobile_number' => '?',
                'address_line_1' => '?',
                'address_line_2' => '?',
                'landmark' => '?',
                'city' => '?',
                'state' => '?',
                'country' => '?',
                'pincode' => '?'
            ])->setParameters([
                $this->customerId,
                $this->type,
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->mobileNumber,
                $this->addressLine1,
                $this->addressLine2,
                $this->landmark,
                $this->city,
                $this->state,
                $this->country,
                $this->pincode
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update('customer_shipping_address')
            ->set('customer_id', '?')
            ->set('type', '?')
            ->set('first_name', '?')
            ->set('last_name', '?')
            ->set('email_address', '?')
            ->set('mobile_number', '?')
            ->set('address_line_1', '?')
            ->set('address_line_2', '?')
            ->set('landmark', '?')
            ->set('city', '?')
            ->set('state', '?')
            ->set('country', '?')
            ->set('pincode', '?')
            ->where('id = ?')
            ->setParameters([
                $this->customerId,
                $this->type,
                $this->firstName,
                $this->lastName,
                $this->emailAddress,
                $this->mobileNumber,
                $this->addressLine1,
                $this->addressLine2,
                $this->landmark,
                $this->city,
                $this->state,
                $this->country,
                $this->pincode,
                $this->id
            ])->execute();
    }
    
    public function delete(){
        $this->conn->createQueryBuilder()
            ->delete('customer_shipping_address')
            ->where('id = ?')
            ->setParameter(0, $this->id)
            ->expr();
    }


}

