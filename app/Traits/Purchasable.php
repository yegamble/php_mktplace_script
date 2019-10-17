<?php

namespace App\Traits;


use App\Dispute;
use App\DisputeMessage;
use App\Events\Purchase\NewPurchase;
use App\Events\Purchase\ProductDelivered;
use App\Events\Purchase\ProductDisputed;
use App\Events\Purchase\ProductDisputeResolved;
use App\Events\Purchase\ProductSent;
use App\Exceptions\RequestException;

use App\User;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait Purchasable {
    /**
     *  Runs purchased procedure
     *  It has been called in DB transaction
     */
    public function purchased()
    {
        // Generate Payment Service from Service Container
        $this -> payment = app() -> makeWith(\App\Marketplace\Payment\Payment::class, ['purchase' => $this]);

        // Runs purchased procedure of the payment
        $this -> getPayment() -> purchased();
        // Prepare purchase
        $this -> encryptMessage();
        // calculate bitcoin to pay in this moment
        $this -> to_pay = $this -> getPayment() -> usdToCoin($this -> getSumDollars());
        // Substract the quantity from the product
        $this -> offer -> product -> substractQuantity($this -> quantity);
        $this -> offer -> product -> save();

        event(new NewPurchase($this));

        // if it is autodelivery mark as sent and run sent procedure
        if($this -> offer -> product -> isAutodelivery()){
            // Mark as sent and run sent procedure
            $this -> getPayment() -> sent();
            $this -> state = 'sent';
            $this -> save();

            // pull products to the delivered product section
            $productsToDelivery = $this -> offer -> product -> digital -> getProducts($this -> quantity);

            $this -> delivered_product = implode("\n", $productsToDelivery);
            $this -> save();
        }
    }

    /**
     * Runs procedure when the product is sent
     * Atomic in transaction
     */
    public function sent()
    {

        if(!$this -> isVendor())
            throw new RequestException('You must be vendor of this product to mark this sale as sent!');

        try{
            DB::beginTransaction();
            // Payment service runs procedure
            $this -> getPayment() -> sent();

            $this -> state = 'sent';
            $this -> save();

            DB::commit();
            event(new ProductSent($this));

        }
        catch (\Exception $e){
            DB::rollBack();
            throw new RequestException('Error happened! Please try again later!');
        }

    }

    /**
     * Runs procedure when the product is delivered
     * Atomic in transactions
     */
    public function delivered()
    {
        if(!$this -> isBuyer())
            throw new RequestException('You must be buyer to mark this purchase as delivered!');

        try{
            DB::beginTransaction();
            // Delivered procedure from Payment service
            $this -> getPayment() -> delivered();

            $this->state = 'delivered';

            $this -> save();
            DB::commit();
            event(new ProductDelivered($this));
        }
        catch (RequestException $e){
            DB::rollBack();
            // delegate request exception
            throw new RequestException($e ->getMessage());
        }
        catch (\Exception $e){

            DB::rollBack();

            Log::error($e ->getMessage());
            throw new RequestException('Error happened! Please try again later!');
        }

    }

    /**
     * Runs procedure when the product is marked as disputed
     * Atomic in transaction
     */
    public function disputed()
    {
        try{
            DB::beginTransaction();
            // Disputed procedure from selected
            $this -> getPayment() -> disputed();

            $this -> state = 'disputed';

            $this -> save();
            DB::commit();

        }
        catch (\Exception $e){
            DB::rollBack();
            throw new RequestException('Error happened! Please try again later!');
        }
    }
    /**
     * Make dispute and dispute message
     *
     * @throws RequestException
     */
    public function makeDispute($message)
    {
        if(!$this->canMakeDispute())
            throw new RequestException('You don\' have permission to make dispute!');

        try{
            DB::beginTransaction();
            // Make dispute
            $newDispute = new Dispute();
            $newDispute -> save();
            // Make message
            $newDisputeMessage = new DisputeMessage();
            $newDisputeMessage -> setDispute($newDispute);
            $newDisputeMessage -> message = $message;
            $newDisputeMessage -> setAuthor(auth() -> user());
            $newDisputeMessage -> save();


            // Mark as disputed
            $this -> state = 'disputed';
            $this -> setDispute($newDispute);

            $this -> save();

            DB::commit();
            event(new ProductDisputed($this,auth()->user()));
        }
        catch (\Exception $e){
            DB::rollBack();
            throw new RequestException('Something went wrong! Please try again!' . $e -> getMessage());
        }

    }

    /**
     * Resolving disputes
     *
     * @param string $winnerId
     * @throws RequestException
     */
    public function resolveDispute(string $winnerId)
    {
        $winner = User::find($winnerId);
        if(is_null($winner)) throw new RequestException('This user can not be winner!');

        // user is not neither vendor or buyer
        if(!$this -> isBuyer($winner) && !$this -> isVendor($winner))
            throw new RequestException('User must be vendor or buyer!');

        try{
            DB::beginTransaction();

            // run resolved procedure
            $this -> getPayment() -> resolved(['receiving_address' => $winner -> coinAddress($this -> getPayment() -> coinLabel()) -> address]);

            // Set the winner
            $this -> dispute -> winner_id = $winner -> id;
            $this -> dispute -> save();

            DB::commit();
            event(new ProductDisputeResolved($this));
        }
        catch (\Exception $e){
            DB::rollBack();
            dd($e);
            throw new RequestException('Something went wrong, please try again!' . $e -> getMessage());
        }
    }
}