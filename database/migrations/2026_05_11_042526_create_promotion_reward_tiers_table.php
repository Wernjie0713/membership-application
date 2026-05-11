<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotion_reward_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('tier');
            $table->unsignedInteger('referral_threshold');
            $table->decimal('reward_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_recurring')->default(false);
            $table->unsignedInteger('step_increment')->nullable();
            $table->timestamps();

            $table->unique(['promotion_id', 'tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_reward_tiers');
    }
};
