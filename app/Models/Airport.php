<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    /**
     * Os atributos que são mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'iata_code',
        'city_id',
    ];

    /**
     * Relacionamento com a cidade.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}