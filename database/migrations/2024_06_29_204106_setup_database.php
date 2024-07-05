<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//
// Use the following queries for PostgreSQL,
// to examine the schema and ensure referential
// integrity, indexes, and auto-increment values.
//

//
// List all foreign keys
//
// SELECT conrelid::regclass AS table_name,
//        conname AS foreign_key,
//        pg_get_constraintdef(oid)
// FROM   pg_constraint
// WHERE  contype = 'f'
// AND    connamespace = 'public'::regnamespace
// ORDER  BY conrelid::regclass::text, contype DESC;
//

//
// List all sequences (auto-increment)
// SELECT c.relname FROM pg_class c WHERE c.relkind = 'S' order BY c.relname;
//

//
// List all indexes
//
// SELECT tablename, indexname, indexdef
// FROM pg_indexes
// WHERE schemaname = 'public'
// ORDER BY tablename, indexname;
//

return new class extends Migration{
  /**
   * Create the database schema, and pre-populate default records.
   *
   * NOTE: Even though the default behaviour when defining columns is `NOT NULL`,
   *       we use `nullable(false)` everywhere, because explicit is better than implicit.
   */
  public function up(): void{
    Schema::create('sms_providers', function (Blueprint $table) {
      $table->id()->nullable(false);
      $table->string('provider')->nullable(false);
      $table->string('details')->nullable(false);
      $table->string('api_url')->nullable(false);
      $table->unsignedInteger('sms_max_limit_per_day')->default(0);
      $table->timestamp('created_at')->useCurrent()->nullable(false);
      $table->timestamp('updated_at')->useCurrent()->nullable(false);

      $table->unique(['provider']);
      $table->unique(['api_url']);
    });

    // Insert default SMS providers
    DB::table('sms_providers')->insert([
      'id' => '100',
      'provider' => "sms.to",
      'sms_max_limit_per_day' => 1000,
      'api_url' => 'https://api.sms.to/sms/send',
      'details' => "Intergotelecom Platform SMS provider.",
    ]);

    // Insert default SMS providers
    DB::table('sms_providers')->insert([
      'id' => '200',
      'provider' => "acme-sms-provider.com",
      'api_url' => 'example.com/acme-sms-provider',
      'sms_max_limit_per_day' => 900,
      'details' => "ACME Telecom SMS provider.",
    ]);

    // Insert default SMS providers
    DB::table('sms_providers')->insert([
      'id' => '300',
      'provider' => "anotherone-sms-provider.com",
      'api_url' => 'example.com/anotherone-sms-provider',
      'sms_max_limit_per_day' => 800,
      'details' => "Anotherone Telecom SMS provider.",
    ]);

    Schema::create('sms_provider_user', function (Blueprint $table) {
      $table->id()->nullable(false);
      $table->unsignedBigInteger('sms_provider_id')->nullable(false);
      $table->unsignedBigInteger('user_id')->nullable(false);
      // We can add deifferent limit for each user here
      $table->timestamp('created_at')->useCurrent()->nullable(false);
      $table->timestamp('updated_at')->useCurrent()->nullable(false);

      $table->unique(['sms_provider_id', 'user_id']);

      $table->index('user_id');
      $table->index('sms_provider_id');
      $table->foreign('user_id')->references('id')->on('users')->constrained();
      $table->foreign('sms_provider_id')->references('id')->on('sms_providers')->constrained();
    });

    Schema::create('message_statuses', function (Blueprint $table) {
      $table->id()->nullable(false);
      $table->string('status')->unique()->nullable(false);
      $table->longText('description')->nullable(false);
      $table->timestamp('created_at')->useCurrent()->nullable(false);
      $table->timestamp('updated_at')->useCurrent()->nullable(false);
    });

    // Insert default message statues
    DB::table('message_statuses')->insert([
      [
        'id' => '100',
        'status' => "delivered",
        'description' => "Message was successfully delivered.",
      ],
      [
        'id' => '200',
        'status' => "pending",
        'description' => "Message delivery is initiated but its delivery status is pending",
      ],
      [
        'id' => '300',
        'status' => "failed",
        'description' => "Message delivery failed.",
      ],
      [
        'id' => '400',
        'status' => "queued",
        'description' => "Message delivery failed.",
      ]
    ]);

    Schema::create('messages', function (Blueprint $table) {
      $table->id()->nullable(false);
      $table->string('message')->nullable(false);
      $table->string('phone_number')->nullable(false);
      $table->string('sender_id')->nullable(false);
      // Default NULL. Must be updated with the external message id, returned from the SMS provider.
      $table->string('sms_provider_message_id')->nullable(true);
      $table->unsignedBigInteger('user_id')->nullable(false);
      $table->unsignedBigInteger('sms_provider_id')->nullable(false);
      $table->unsignedBigInteger('message_status_id')->nullable(false);
      $table->timestamp('created_at')->useCurrent()->nullable(false);
      $table->timestamp('updated_at')->useCurrent()->nullable(false);

      $table->index('user_id');
      $table->index('sms_provider_id');
      $table->index('message_status_id');
      $table->foreign('user_id')->references('id')->on('users')->constrained();
      $table->foreign('sms_provider_id')->references('id')->on('sms_providers')->constrained();
      $table->foreign('message_status_id')->references('id')->on('message_statuses')->constrained();
    });

    Schema::create('badwords', function (Blueprint $table) {
      $table->id()->nullable(false);
      $table->string('badword')->unique()->nullable(false);
      $table->timestamp('created_at')->useCurrent()->nullable(false);
      $table->timestamp('updated_at')->useCurrent()->nullable(false);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    //
  }
};
