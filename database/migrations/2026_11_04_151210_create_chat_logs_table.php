<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable();
            $table->string('session_id', 100)->nullable();
            $table->text('user_message');
            $table->string('called_function', 100)->nullable();
            $table->json('function_payload_json')->nullable();
            $table->text('bot_response');
            $table->boolean('is_out_of_scope')->default(false);
            $table->enum('category', ['in_scope_db', 'out_of_scope_irrelevant', 'needs_admin'])->default('in_scope_db');
            $table->enum('status', ['resolved', 'pending_admin', 'answered_by_admin'])->default('resolved');
            $table->text('admin_reply')->nullable();
            $table->bigInteger('admin_id')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('chat_logs');
    }
};
