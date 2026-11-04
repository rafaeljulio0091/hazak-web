<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table): void {
            $table->string('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        $hasNonNumericIdentifiers = match ($driver) {
            'mysql', 'mariadb' => DB::table('sessions')->whereRaw("user_id REGEXP '[^0-9]'")->exists(),
            'sqlite' => DB::table('sessions')->whereRaw("user_id GLOB '*[^0-9]*'")->exists(),
            'pgsql' => DB::table('sessions')->whereRaw("user_id !~ '^[0-9]+$'")->exists(),
            default => true,
        };

        if ($hasNonNumericIdentifiers) {
            throw new LogicException('Sessions contain Firebase UIDs and cannot be converted back to numeric IDs safely.');
        }

        Schema::table('sessions', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
