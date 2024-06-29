<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
// List all sequences (auto increment)
// SELECT c.relname FROM pg_class c WHERE c.relkind = 'S' order BY c.relname;
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
   * Run the migrations.
   */
  public function up(): void{
    Schema::create('sms_providers', function (Blueprint $table) {
      $table->id();
      $table->string('provider');
      $table->string('details');
      $table->unsignedInteger('sms_max_limit_per_day')->default(0);
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();
    });

    // Insert default providers
    DB::table('sms_providers')->insert([
      'id' => '100',
      'provider' => "sms.to",
      'sms_max_limit_per_day' => 1000,
      'details' => "Intergotelecom Platform SMS provider.",
    ]);

    Schema::create('sms_provider_user', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('sms_provider_id');
      $table->unsignedBigInteger('user_id');
      // We can add deifferent limit for each user here
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();

      $table->unique(['sms_provider_id', 'user_id']);

      $table->index('user_id');
      $table->index('sms_provider_id');
      $table->foreign('user_id')->references('id')->on('users')->constrained();
      $table->foreign('sms_provider_id')->references('id')->on('sms_providers')->constrained();
    });

    Schema::create('message_statuses', function (Blueprint $table) {
      $table->id();
      $table->string('status')->unique();
      $table->longText('description');
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();
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
      ]
    ]);

    // NOTE: The original technical specification does not explicitly require that messages
    //       belong to users. However it makes sense to add this relation.
    Schema::create('messages', function (Blueprint $table) {
      $table->id();
      $table->string('message');
      $table->string('phone_number');
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('message_status_id');
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();

      $table->index('user_id');
      $table->index('message_status_id');
      $table->foreign('user_id')->references('id')->on('users')->constrained();
      $table->foreign('message_status_id')->references('id')->on('message_statuses')->constrained();
    });

    Schema::create('badwords', function (Blueprint $table) {
      $table->id();
      $table->string('badword')->unique();
      $table->timestamp('created_at')->useCurrent();
      $table->timestamp('updated_at')->useCurrent();
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
