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
        // ==========================================
        // TAHAP 1: TABEL DIMENSI MASTER (INDEPENDEN)
        // ==========================================

        // 1. Tabel Dimensi Kuartal
        Schema::create('dim_kuartal', function (Blueprint $table) {
            $table->id('id_kuartal'); // Primary Key
            $table->string('nama_kuartal', 10);
            $table->tinyInteger('nomor_kuartal');
        });

        // 2. Tabel Dimensi Tahun
        Schema::create('dim_tahun', function (Blueprint $table) {
            $table->id('id_tahun');
            $table->smallInteger('tahun');
        });

        // 3. Tabel Dimensi Rasio (Induk Kategori Rasio)
        Schema::create('dim_rasio', function (Blueprint $table) {
            $table->id('id_rasio');
            $table->string('kategori', 50)->nullable();
        });

        // 4. Tabel Dimensi Perusahaan
        Schema::create('dim_perusahaan', function (Blueprint $table) {
            $table->id('id_perusahaan');
            $table->string('nama_perusahaan', 150)->nullable();
            $table->string('kode_saham', 10)->nullable();
            $table->string('sektor', 50)->nullable();
            $table->string('sub_sektor', 50)->nullable();
        });

        // ==========================================
        // TAHAP 2: TABEL DIMENSI TURUNAN (DEPENDEN)
        // ==========================================

        // 5. Tabel Dimensi Waktu (Butuh Tahun & Kuartal)
        Schema::create('dim_waktu', function (Blueprint $table) {
            $table->id('id_waktu');
            $table->unsignedBigInteger('id_tahun');
            $table->unsignedBigInteger('id_kuartal');
            $table->date('tanggal')->nullable();

            // Foreign Keys
            $table->foreign('id_tahun')->references('id_tahun')->on('dim_tahun')->onDelete('cascade');
            $table->foreign('id_kuartal')->references('id_kuartal')->on('dim_kuartal')->onDelete('cascade');
        });

        // 6. Tabel Dimensi Likuiditas (Butuh Dim Rasio)
        Schema::create('dim_likuiditas', function (Blueprint $table) {
            $table->id('id_likuiditas');
            $table->unsignedBigInteger('id_rasio');
            $table->string('nama_rasio', 100)->nullable();
            $table->string('rumus', 255)->nullable();
            $table->string('keterangan', 255)->nullable();

            $table->foreign('id_rasio')->references('id_rasio')->on('dim_rasio')->onDelete('cascade');
        });

        // 7. Tabel Dimensi Profitabilitas (Butuh Dim Rasio)
        Schema::create('dim_profitabilitas', function (Blueprint $table) {
            $table->id('id_profitabilitas');
            $table->unsignedBigInteger('id_rasio');
            $table->string('nama_rasio', 100)->nullable();
            $table->string('rumus', 255)->nullable();
            $table->string('keterangan', 255)->nullable();

            $table->foreign('id_rasio')->references('id_rasio')->on('dim_rasio')->onDelete('cascade');
        });

        // 8. Tabel Dimensi Solvabilitas (Butuh Dim Rasio)
        Schema::create('dim_solvabilitas', function (Blueprint $table) {
            $table->id('id_solvabilitas');
            $table->unsignedBigInteger('id_rasio');
            $table->string('nama_rasio', 100)->nullable();
            $table->string('rumus', 255)->nullable();
            $table->string('keterangan', 255)->nullable();

            $table->foreign('id_rasio')->references('id_rasio')->on('dim_rasio')->onDelete('cascade');
        });

        // ==========================================
        // TAHAP 3: TABEL FAKTA (TRANSAKSI UTAMA)
        // ==========================================

        // 9. Tabel Fakta Kinerja Keuangan
        Schema::create('fact_kinerja_keuangan', function (Blueprint $table) {
            $table->id('id_fakta');

            // Foreign Keys Columns
            $table->unsignedBigInteger('id_perusahaan');
            $table->unsignedBigInteger('id_waktu');
            $table->unsignedBigInteger('id_rasio')->nullable();

            // Kolom Nilai Nominal (Decimal presisi tinggi)
            $table->decimal('total_aset', 22, 2)->nullable();
            $table->decimal('total_kewajiban', 22, 2)->nullable();
            $table->decimal('total_ekuitas', 22, 2)->nullable();
            $table->decimal('pendapatan', 22, 2)->nullable();
            $table->decimal('total_expense', 22, 2)->nullable();
            $table->decimal('laba_bersih', 22, 2)->nullable();
            $table->decimal('ebitda', 22, 2)->nullable();
            $table->decimal('ebit', 22, 2)->nullable();

            // Kolom Nilai Rasio (Decimal presisi koma)
            $table->decimal('current_ratio', 10, 4)->nullable();
            $table->decimal('quick_ratio', 10, 4)->nullable();
            $table->decimal('cash_ratio', 10, 4)->nullable();
            $table->decimal('der', 10, 4)->nullable();
            $table->decimal('dar', 10, 4)->nullable();
            $table->decimal('roa', 10, 4)->nullable();
            $table->decimal('roe', 10, 4)->nullable();
            $table->decimal('npm', 10, 4)->nullable();
            $table->decimal('gpm', 10, 4)->nullable();
            $table->decimal('opm', 10, 4)->nullable();

            $table->date('tanggal_pencatatan')->nullable();

            // Definisi Foreign Keys
            $table->foreign('id_perusahaan')->references('id_perusahaan')->on('dim_perusahaan')->onDelete('cascade');
            $table->foreign('id_waktu')->references('id_waktu')->on('dim_waktu')->onDelete('cascade');
            $table->foreign('id_rasio')->references('id_rasio')->on('dim_rasio')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel dengan urutan terbalik (Anak dulu, baru Induk)
        Schema::dropIfExists('fact_kinerja_keuangan');
        Schema::dropIfExists('dim_solvabilitas');
        Schema::dropIfExists('dim_profitabilitas');
        Schema::dropIfExists('dim_likuiditas');
        Schema::dropIfExists('dim_waktu');
        Schema::dropIfExists('dim_perusahaan');
        Schema::dropIfExists('dim_rasio');
        Schema::dropIfExists('dim_tahun');
        Schema::dropIfExists('dim_kuartal');
    }
};
