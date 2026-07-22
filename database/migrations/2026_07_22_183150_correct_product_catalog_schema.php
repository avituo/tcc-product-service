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
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->decimal('price', total: 12, places: 2)->default(0)->change();
            $table->decimal('discount', total: 12, places: 2)->default(0)->change();
            $table->unsignedInteger('quantity')->default(0)->change();
            $table->unsignedBigInteger('version')->default(1);
            $table->softDeletes();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropSoftDeletes();
            $table->dropColumn('version');
            $table->string('image')->nullable(false)->change();
            $table->decimal('price')->default(0)->change();
            $table->decimal('discount')->default(0)->change();
            $table->integer('quantity')->change();
        });
    }
};
