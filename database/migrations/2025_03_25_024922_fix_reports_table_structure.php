<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixReportsTableStructure extends Migration
{
    public function up()
    {
        if (Schema::hasTable('reports')) {
            Schema::table('reports', function (Blueprint $table) {
                if (!Schema::hasColumn('reports', 'type')) {
                    $table->string('type')->nullable()->after('id');
                }
                if (!Schema::hasColumn('reports', 'report_date')) {
                    $table->date('report_date')->nullable()->after('type');
                }
                if (!Schema::hasColumn('reports', 'data')) {
                    $table->json('data')->nullable()->after('report_date');
                }
                if (!Schema::hasColumn('reports', 'summary')) {
                    $table->text('summary')->nullable()->after('data');
                }
                if (!Schema::hasColumn('reports', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->after('summary');
                }
            });

            DB::table('reports')->update([
                'type' => 'daily',
                'report_date' => now()->toDateString(),
                'data' => json_encode([]),
                'summary' => 'Relatório migrado',
                'user_id' => 1 
            ]);

            Schema::table('reports', function (Blueprint $table) {
                $table->string('type')->nullable(false)->change();
                $table->date('report_date')->nullable(false)->change();
                $table->json('data')->nullable(false)->change();
                $table->text('summary')->nullable(false)->change();
                $table->foreignId('user_id')->nullable(false)->change();
            });
        }
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
}