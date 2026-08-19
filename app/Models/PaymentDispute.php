<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDispute extends Model
{
    protected $fillable = ['order_id','customer_id','reason','message','attachment_path','status','resolved_by','resolved_at'];
    protected $casts = ['resolved_at' => 'datetime'];
    public function order(){ return $this->belongsTo(Order::class); }
    public function customer(){ return $this->belongsTo(User::class,'customer_id'); }
    public function resolver(){ return $this->belongsTo(User::class,'resolved_by'); }
}
