<?php
namespace OS\models;

use stdClass;
use Exception;
use Doctrine\DBAL\Connection;

class CustomerOrder extends stdClass{
    
    const ORDER_STATUS_CREATED = "Order Created";
    const ORDER_STATUS_PLACED = "Order Placed";
    const ORDER_STATUS_PROCESSING = "Processing";
    const ORDER_STATUS_SHIPPED = "Shipped";
    const ORDER_STATUS_DELIVERED = "Delivered";
    const ORDER_STATUS_CANCELLED = "Cancelled";
    
    private $id;
    private $orderDate;
    private $customerId;
    private $shippingAddressId;
    private $orderStatus;
    private $totalAmount = 0;
    private $totalTax = 0;
    private $discountAmount = 0;
    private $shippingCharges = 0;
    private $grandTotal = 0;

    /** @var Connection */
    private $conn;

    public function __construct(Connection $conn) {
        $this->conn = $conn;
    }
    
    public function getId(): int {
        return $this->id;
    }

    public function getOrderDate(string $format="d/m/Y h:i:s A"): string {
        $timestamp = strtotime($this->orderDate);
        return date($format, $timestamp + (330 * 60));
    }

    public function getCustomerId(): int {
        return $this->customerId;
    }
    
    public function getCustomer(): Customer{
        return (new CustomerQuery($this->conn))->findById($this->customerId);
    }
    
    public function getShippingAddressId(): int{
        return $this->shippingAddressId;
    }
    
    public function getShippingAddress(): CustomerShippingAddress{
        return (new CustomerShippingAddressQuery($this->conn))->findById($this->shippingAddressId);
    }

    public function getOrderStatus(): string {
        return $this->orderStatus;
    }

    public function getTotalAmount(): float {
        return $this->totalAmount;
    }

    public function getTotalTax(): float {
        return $this->totalTax;
    }

    public function getDiscountAmount(): float {
        return $this->discountAmount;
    }

    public function getShippingCharges(): float {
        return $this->shippingCharges;
    }

    public function getGrandTotal(): float {
        return $this->grandTotal;
    }

    public function setId($id): CustomerOrder {
        $this->id = $id;
        return $this;
    }

    public function setOrderDate(int $orderDate): CustomerOrder {
        $this->orderDate = date('Y-m-d G:i:s');
        return $this;
    }

    public function setCustomerId(int $customerId): CustomerOrder {
        $this->customerId = $customerId;
        return $this;
    }
    
    public function setShippingAddressId(int $shippingAddressId): CustomerOrder{
        if(!$shippingAddressId)
            throw new Exception ("Shipping Address is required to create order.");
        $this->shippingAddressId = $shippingAddressId;
        return $this;
    }

    public function setOrderStatus(string $orderStatus): CustomerOrder {
        $this->orderStatus = $orderStatus;
        return $this;        
    }

    public function setTotalAmount(float $totalAmount): CustomerOrder {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function setTotalTax(float $totalTax): CustomerOrder {
        $this->totalTax = $totalTax;
        return $this;
    }

    public function setDiscountAmount(float $discountAmount): CustomerOrder {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    public function setShippingCharges(float $shippingCharges): CustomerOrder {
        $this->shippingCharges = $shippingCharges;
        return $this;
    }

    public function setGrandTotal(float $grandTotal): CustomerOrder {
        $this->grandTotal = $grandTotal;
        return $this;
    }

    public function insert(): CustomerOrder{
        $this->conn->createQueryBuilder()
            ->insert('customer_order')
            ->values([
                'order_date' => '?',
                'customer_id' => '?',
                'shipping_address_id' => '?',
                'order_status' => '?',
                'total_amount' => '?',
                'total_tax' => '?',
                'discount_amount' => '?',
                'shipping_charges' => '?',
                'grand_total' => '?'
            ])->setParameters([
                $this->orderDate,
                $this->customerId,
                $this->shippingAddressId,
                $this->orderStatus,
                $this->totalAmount,
                $this->totalTax,
                $this->discountAmount,
                $this->shippingCharges,
                $this->grandTotal
            ])->execute();
        $this->id = $this->conn->lastInsertId();
        return $this;
    }
    
    public function update(){
        $this->conn->createQueryBuilder()
            ->update('customer_order')
            ->set('order_date', '?')
            ->set('customer_id', '?')
            ->set('shipping_address_id', '?')
            ->set('order_status', '?')
            ->set('total_amount', '?')
            ->set('total_tax', '?')
            ->set('discount_amount', '?')
            ->set('shipping_charges', '?')
            ->set('grand_total', '?')
            ->where('id = ?')
            ->setParameters([
                $this->orderDate,
                $this->customerId,
                $this->shippingAddressId,
                $this->orderStatus,
                $this->totalAmount,
                $this->totalTax,
                $this->discountAmount,
                $this->shippingCharges,
                $this->grandTotal,
                $this->id
            ])->execute();
    }
    
    public function delete(){
        
    }
}

