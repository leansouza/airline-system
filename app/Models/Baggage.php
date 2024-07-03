<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baggage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'baggage_number',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}