<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->foreignId('marketing_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->foreignId('marketing_user_id')
                ->nullable()
                ->after('pelanggan_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        $marketingAssignments = DB::table('pengajuan_kredit as pk')
            ->join('pengajuan_logs as pl', 'pl.pengajuan_id', '=', 'pk.id')
            ->join('users as u', 'u.id', '=', 'pl.changed_by')
            ->where('u.role', 'marketing')
            ->selectRaw('pk.pelanggan_id, MIN(pl.changed_by) as marketing_user_id')
            ->groupBy('pk.pelanggan_id')
            ->get();

        foreach ($marketingAssignments as $assignment) {
            DB::table('pelanggan')
                ->where('id', $assignment->pelanggan_id)
                ->whereNull('marketing_user_id')
                ->update([
                    'marketing_user_id' => $assignment->marketing_user_id,
                ]);
        }

        $applicationAssignments = DB::table('pengajuan_kredit as pk')
            ->join('pelanggan as p', 'p.id', '=', 'pk.pelanggan_id')
            ->whereNull('pk.marketing_user_id')
            ->whereNotNull('p.marketing_user_id')
            ->select('pk.id', 'p.marketing_user_id')
            ->get();

        foreach ($applicationAssignments as $assignment) {
            DB::table('pengajuan_kredit')
                ->where('id', $assignment->id)
                ->update([
                    'marketing_user_id' => $assignment->marketing_user_id,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('pengajuan_kredit', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketing_user_id');
        });

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marketing_user_id');
        });
    }
};
