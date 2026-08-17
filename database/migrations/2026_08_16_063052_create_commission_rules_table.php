<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')
                ->constrained('partnership_programs')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('event')->default('sale');

            $table->unsignedInteger('level')->default(0);

            // Percentage is the default commission model used by the
            // application's existing rules and keeps legacy rule creation
            // compatible while still allowing explicit fixed commissions.
            $table->enum('commission_type', [
                'percentage',
                'fixed',
            ])->default('percentage');

            $table->decimal('value', 15, 4);

            $table->decimal('maximum_amount', 15, 2)
                ->nullable();

            $table->boolean('status')->default(true);

            $table->unsignedInteger('priority')->default(0);

            $table->timestamps();

            $table->index([
                'program_id',
                'product_id',
                'event',
                'level',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_rules');
    }
};
