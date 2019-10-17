<?php

namespace App;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


/**
 * Represents the instance of the coin address for any user
 * Can be any Coin that is supported in the config
 *
 * Class Address
 * @package App
 */
class Address extends Model
{
    use Uuids;
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    public $incrementing = false;

    /**
     * Relationship with the user
     */
    public function user()
    {
        return $this -> belongsTo(\App\User::class, 'user_id', 'id');
    }


    /**
     * String how long was passed since adding address
     *
     * @return string
     */
    public function getAddedAgoAttribute()
    {
        return Carbon::parse($this -> created_at) -> diffForHumans();
    }

}
