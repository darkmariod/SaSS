<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->default('Men')->after('barber_shop_id');

            $table->enum('service_type', [
                'main',
                'option',
                'addon',
            ])->default('main')->after('description');

            $table->foreignId('parent_service_id')
                ->nullable()
                ->after('service_type')
                ->constrained('services')
                ->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0)->after('is_active');

            $table->index(['barber_shop_id', 'category', 'service_type'], 'services_flow_lookup_idx');
            $table->index(['parent_service_id', 'service_type'], 'services_parent_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_flow_lookup_idx');
            $table->dropIndex('services_parent_type_idx');

            $table->dropConstrainedForeignId('parent_service_id');

            $table->dropColumn([
                'category',
                'service_type',
                'sort_order',
            ]);
        });
    }
};
