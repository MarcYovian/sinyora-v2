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
        Schema::create('detailables', function (Blueprint $table) {
            // Foreign key ke tabel documents
            $table->foreignId('document_id')->constrained()->onDelete('cascade');

            // Kolom polimorfik untuk model detail
            // (LicensingDocument, InvitationDocument, dll)
            $table->unsignedBigInteger('detailable_id');
            $table->string('detailable_type');

            // Menambahkan index untuk performa query
            $table->index(['detailable_id', 'detailable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detailables');
    }
};
