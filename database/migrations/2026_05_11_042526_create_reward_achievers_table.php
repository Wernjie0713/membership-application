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
        Schema::create('reward_achievers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promotion_reward_tier_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('threshold_reached');
            $table->unsignedInteger('referral_count');
            $table->decimal('reward_amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(
                ['member_id', 'promotion_id', 'promotion_reward_tier_id', 'threshold_reached'],
                'reward_achievers_unique_reward'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_achievers');
    }
};
