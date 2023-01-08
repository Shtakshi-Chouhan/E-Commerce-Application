<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;

class CustomerBillingAddress extends stdClass{
    
    private $id;
    private $customerId;
    private $billingName;
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

    public function getBillingName(): string {
        return $this->billingName;
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

    public function setId(int $id): CustomerBillingAddress {
        $this->id = $id;
        return $this;
    }

    public function setCustomerId(int $customerId): CustomerBillingAddress {
        $this->customerId = $customerId;
        return $this;
    }

    public function setBillingName(string $billingName): CustomerBillingAddress {
        if(empty(trim($billingName))) {
            throw new Exception('Billing Name field cannot be empty.');
        }
        $this->billingName = $billingName;
        return $this;
    }

    public function setAddressLine1(string $addressLine1): CustomerBillingAddress {
        if(empty(trim($addressLine1))) {
            throw new Exception('Address line #1 field cannot be empty.');
        }
        $this->addressLine1 = $addressLine1;
        return $this;
    }

    public function setAddressLine2(string $addressLine2): CustomerBillingAddress {
        $this->addressLine2 = $addressLine2;
        return $this;
    }

    public function setLandmark(string $landmark): CustomerBillingAddress {
        $this->landmark = $landmark;
        return $this;
    }

    public function setCity(string $city): CustomerBillingAddress {
        if(empty(trim($city))) {
            throw new Exception('City field cannot be empty.');
        }
        $this->city = $city;
        return $this;
    }

    public function setState(string $state): CustomerBillingAddress {
        if(empty(trim($state))) {
            throw new Exception('State field cannot be empty.');
        }
        $this->state = $state;
        return $this;
    }

    public function setCountry(string $country): CustomerBillingAddress {
        if(empty(trim($country))) {
            throw new Exception('Country field cannot be empty.');
        }
        $this->country = $country;
        return $this;
    }

    public function setPincode(string $pincode): CustomerBillingAddress {
        if(empty(trim($pincode))) {
            throw new Exception('Pincode field cannot be empty.');
        }
        $this->pincode = $pincode;
        return $this;
    }
    
    public function insert(): CustomerBillingAddress{
        $this->conn->createQueryBuilder()
            ->insert('customer_billing_address')
            ->values([
                'customer_id' => '?',
                'billing_name' => '?',
                'address_line_1' => '?',
                'address_line_2' => '?',
                'landmark' => '?',
                'city' => '?',
                'state' => '?',
                'country' => '?',
                'pincode' => '?'
            ])->setParameters([
                $this->customerId,
                $this->billingName,
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
            ->update('customer_billing_address')
            ->set('customer_id', '?')
            ->set('billing_name', '?')
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
                $this->billingName,
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
            ->delete('customer_billing_address')
            ->where('id = ?')
            ->setParameter(0, $this->id)
            ->execute();
    }

}