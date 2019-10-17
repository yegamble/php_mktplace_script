<?php


namespace App\Marketplace\Payment;

use App\Purchase;

/**
 * Payment interface that define any purchase procedure
 *
 * Interface Payment
 * @package App\Marketplace\Payment
 */
abstract class Payment
{
    /**
     * Instance of the purchase that runs this procedures
     *
     * @var Purchase
     */
    protected $purchase;

    public function __construct(Purchase $purchase)
    {
        // Set the target purchase in constructor
        $this -> purchase = $purchase;
    }


    abstract public function purchased();

    abstract public function sent();

    abstract public function delivered();

    /**
     * in parameters key 'receiving_address' must be defined to send to this address
     *
     * @param array $parameters
     * @return mixed
     */
    abstract public function resolved( array $parameters );

    /**
     * Returns amount to pay
     *
     * @return float
     */
    abstract function balance() : float;


    /**
     * Converts from USD amount to equivalent coin amount
     *
     * @return float
     */
    abstract function usdToCoin( $usd ) : float;


    /**
     * Returns label of this coin
     *
     * @return string
     */
    abstract function coinLabel() : string;

}