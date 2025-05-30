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
        Schema::table('contacts', function (Blueprint $table) {
            $table->json('custom_fields')->nullable();
            $table->json('alternate_emails')->nullable();
            $table->json('alternate_phones')->nullable();
            $table->boolean('is_merged')->default(false);
            $table->unsignedBigInteger('merged_into')->nullable()->after('is_merged');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'custom_fields',
                'alternate_emails',
                'alternate_phones',
                'is_merged',
                'merged_into',
            ]);
        });
    }

};
