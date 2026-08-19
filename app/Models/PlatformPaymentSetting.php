<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PlatformPaymentSetting extends Model
{
    protected $fillable = ['bank_name','account_name','account_number','support_phone','support_whatsapp','support_email'];
    public static function current(): self { return static::firstOrCreate(['id'=>1]); }
}
