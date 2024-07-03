<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'birthdate',
    ];
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
