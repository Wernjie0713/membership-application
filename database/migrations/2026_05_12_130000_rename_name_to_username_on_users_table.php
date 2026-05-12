<?php

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
        if (! Schema::hasColumn('users', 'name') || Schema::hasColumn('users', 'username')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'username');
        });

        DB::table('users')
            ->select('id', 'username')
            ->orderBy('id')
            ->get()
            ->each(function (object $user) {
                $baseUsername = Str::of($user->username)
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '')
                    ->limit(24, '')
                    ->value();

                $baseUsername = $baseUsername !== '' ? $baseUsername : 'user'.$user->id;

                $candidate = $baseUsername;
                $suffix = 1;

                while (
                    DB::table('users')
                        ->where('username', $candidate)
                        ->where('id', '!=', $user->id)
                        ->exists()
                ) {
                    $candidate = Str::limit($baseUsername, 20, '').$suffix;
                    $suffix++;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $candidate]);
            });

        DB::statement('ALTER TABLE users MODIFY username VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE users ADD UNIQUE users_username_unique (username)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'username') || Schema::hasColumn('users', 'name')) {
            return;
        }

        DB::statement('ALTER TABLE users DROP INDEX users_username_unique');

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'name');
        });
    }
};
