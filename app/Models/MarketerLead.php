<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketerLead extends Model
{
    protected $fillable = [
        'name', 'whatsapp_number', 'email', 'has_sold_online', 'what_sold', 'sales_result',
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'landing_page',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'has_sold_online' => 'boolean',
    ];
}
