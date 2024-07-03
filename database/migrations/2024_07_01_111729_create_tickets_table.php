<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_flights_id')->constrained()->onDelete('cascade');
            $table->foreignId('visitor_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number')->unique();
            $table->string('passenger_name');
            $table->string('passenger_cpf');
            $table->date('passenger_birthdate');
            $table->decimal('total_price', 8, 2);
            $table->boolean('has_baggage')->default(false);
            $table->string('baggage_number')->nullable()->unique();
            $table->enum('status', ['active', 'cancelled', 'pending'])->default('active');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
