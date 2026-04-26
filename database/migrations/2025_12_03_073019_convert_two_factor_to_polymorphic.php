<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('two_factor_authentications')) {
            return;
        }

        if (
            Schema::hasColumn('two_factor_authentications', 'user_id')
            && ! Schema::hasColumn('two_factor_authentications', 'authenticatable_id')
        ) {
            Schema::table('two_factor_authentications', function (Blueprint $table): void {
                $table->renameColumn('user_id', 'authenticatable_id');
            });
        }

        if (! Schema::hasColumn('two_factor_authentications', 'authenticatable_type')) {
            Schema::table('two_factor_authentications', function (Blueprint $table): void {
                $table->string('authenticatable_type', 255)->after('authenticatable_id')->nullable();
            });
        }

        DB::table('two_factor_authentications')
            ->whereNull('authenticatable_type')
            ->update(['authenticatable_type' => 'Botble\\ACL\\Models\\User']);

        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            $table->string('authenticatable_type', 255)->nullable(false)->change();
        });

        if (! $this->hasIndex('two_factor_authentications', '2fa_authenticatable_index')) {
            Schema::table('two_factor_authentications', function (Blueprint $table): void {
                $table->index(['authenticatable_id', 'authenticatable_type'], '2fa_authenticatable_index');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('two_factor_authentications')) {
            return;
        }

        Schema::table('two_factor_authentications', function (Blueprint $table): void {
            if ($this->hasIndex('two_factor_authentications', '2fa_authenticatable_index')) {
                $table->dropIndex('2fa_authenticatable_index');
            }

            if (Schema::hasColumn('two_factor_authentications', 'authenticatable_type')) {
                $table->dropColumn('authenticatable_type');
            }

            if (
                Schema::hasColumn('two_factor_authentications', 'authenticatable_id')
                && ! Schema::hasColumn('two_factor_authentications', 'user_id')
            ) {
                $table->renameColumn('authenticatable_id', 'user_id');
            }
        });
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $index]
        );
    }
};
