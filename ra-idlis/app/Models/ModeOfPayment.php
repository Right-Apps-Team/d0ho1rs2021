<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeOfPayment extends Model
{
    protected $table = 'madeofpayment';
    protected $primaryKey = 'mop_code';
    public $incrementing = false;
    protected $keyType = 'string';
}
