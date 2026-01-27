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
        Schema::table('fact_kinerja_keuangan', function (Blueprint $table) {
            // 1. HAPUS kolom lama yang tidak lagi digunakan (berdasarkan kode sebelumnya)
            // Kita gunakan if (Schema::hasColumn) untuk mencegah error jika kolom tidak ada
            $columnsODrop = ['total_aset', 'total_kewajiban', 'total_expense', 'laba_bersih', 'ebitda', 'ebit'];

            foreach ($columnsODrop as $col) {
                if (Schema::hasColumn('fact_kinerja_keuangan', $col)) {
                    $table->dropColumn($col);
                }
            }

            // 2. TAMBAH kolom baru sesuai permintaan
            // Menggunakan ->after('id_rasio') agar posisi kolom rapi setelah foreign key

            $table->decimal('kas', 22, 2)->nullable()->after('id_rasio');
            $table->decimal('persediaan', 22, 2)->nullable()->after('kas');
            $table->decimal('aktiva_lancar', 22, 2)->nullable()->after('persediaan');
            $table->decimal('utang_lancar', 22, 2)->nullable()->after('aktiva_lancar');

            $table->decimal('total_aktiva', 22, 2)->nullable()->after('utang_lancar'); // Pengganti total_aset
            $table->decimal('total_utang', 22, 2)->nullable()->after('total_aktiva');   // Pengganti total_kewajiban

            // Catatan: 'total_ekuitas' dan 'pendapatan' TIDAK ditambahkan ulang
            // karena sudah ada di migration sebelumnya.

            $table->decimal('laba_setelah_pajak', 22, 2)->nullable()->after('pendapatan'); // Pengganti laba_bersih
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fact_kinerja_keuangan', function (Blueprint $table) {
            // Kembalikan ke kondisi semula jika di-rollback

            // 1. Hapus kolom baru
            $table->dropColumn([
                'kas',
                'persediaan',
                'aktiva_lancar',
                'utang_lancar',
                'total_aktiva',
                'total_utang',
                'laba_setelah_pajak'
            ]);

            // 2. Tambah kembali kolom lama
            $table->decimal('total_aset', 22, 2)->nullable();
            $table->decimal('total_kewajiban', 22, 2)->nullable();
            $table->decimal('total_expense', 22, 2)->nullable();
            $table->decimal('laba_bersih', 22, 2)->nullable();
            $table->decimal('ebitda', 22, 2)->nullable();
            $table->decimal('ebit', 22, 2)->nullable();
        });
    }
};
