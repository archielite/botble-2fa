<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'authenticatable_id');
        });

        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->string('authenticatable_type', 255)->after('authenticatable_id')->nullable();
        });

        DB::table('two_factor_authentications')->update([
            'authenticatable_type' => 'Botble\\ACL\\Models\\User',
        ]);

        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->string('authenticatable_type', 255)->nullable(false)->change();
        });

        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->index(['authenticatable_id', 'authenticatable_type'], '2fa_authenticatable_index');
        });
    }

    public function down(): void
    {
        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->dropIndex('2fa_authenticatable_index');
            $table->dropColumn('authenticatable_type');
            $table->renameColumn('authenticatable_id', 'user_id');
        });
    }
};
