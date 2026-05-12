<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Renombrar cotizacions → cotizaciones
        if (Schema::hasTable('cotizacions')) {
            Schema::rename('cotizacions', 'cotizaciones');
        }

        // 2. Agregar role a users (si no existe)
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('asistente');
            });
        }

        // 3. clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('identification')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('activo');
        });

        // 4. cotizaciones
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('quote_number')->unique();
            $table->string('event_type')->nullable();
            $table->date('event_date')->nullable();
            $table->integer('guests')->default(1);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('pendiente');
            $table->text('notes')->nullable();
        });

        // 5. eventos
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->nullOnDelete();
            $table->string('name');
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->string('bartender_name')->nullable();
            $table->string('status')->default('programado');
            $table->text('notes')->nullable();
        });

        // 6. ingredientes
        Schema::table('ingredientes', function (Blueprint $table) {
            $table->string('name');
            $table->string('unit')->default('unidad');
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->string('status')->default('activo');
        });

        // 7. paquetes
        Schema::table('paquetes', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('guests_min')->default(1);
            $table->integer('guests_max')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('activo');
        });

        // 8. recetas
        Schema::table('recetas', function (Blueprint $table) {
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('preparation')->nullable();
            $table->integer('servings')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('activa');
        });

        // 9. pagos
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->nullOnDelete();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('efectivo');
            $table->date('paid_at')->nullable();
            $table->string('reference')->nullable();
            $table->string('status')->default('pendiente');
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        // 9. pagos
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'cotizacion_id', 'evento_id', 'amount', 'payment_method',
                'paid_at', 'reference', 'status', 'notes',
            ]);
        });

        // 8. recetas
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'preparation', 'servings', 'price', 'status']);
        });

        // 7. paquetes
        Schema::table('paquetes', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'guests_min', 'guests_max', 'price', 'status']);
        });

        // 6. ingredientes
        Schema::table('ingredientes', function (Blueprint $table) {
            $table->dropColumn(['name', 'unit', 'stock', 'min_stock', 'cost', 'status']);
        });

        // 5. eventos
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['cotizacion_id']);
            $table->dropColumn([
                'cliente_id', 'cotizacion_id', 'name', 'event_date',
                'location', 'bartender_name', 'status', 'notes',
            ]);
        });

        // 4. cotizaciones
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn([
                'cliente_id', 'quote_number', 'event_type', 'event_date',
                'guests', 'subtotal', 'tax', 'total', 'status', 'notes',
            ]);
        });

        // 3. clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'email', 'phone', 'company', 'identification',
                'address', 'notes', 'status',
            ]);
        });

        // 2. users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // 1. Renombrar cotizaciones → cotizacions
        if (Schema::hasTable('cotizaciones')) {
            Schema::rename('cotizaciones', 'cotizacions');
        }
    }
};
