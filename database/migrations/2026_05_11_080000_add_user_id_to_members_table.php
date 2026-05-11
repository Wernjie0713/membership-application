<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('members')
            ->whereNull('user_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $member) {
                $user = User::query()->firstOrCreate(
                    ['email' => $member->email],
                    [
                        'name' => trim($member->first_name.' '.$member->last_name),
                        'email_verified_at' => now(),
                        'password' => Str::random(32),
                    ]
                );

                DB::table('members')
                    ->where('id', $member->id)
                    ->update(['user_id' => $user->id]);
            });

        Schema::table('members', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
