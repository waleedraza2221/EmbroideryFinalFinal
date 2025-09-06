<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('newsletter_subscriptions', function(Blueprint $table){
            $table->id();
            $table->string('email')->unique();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('newsletter_subscriptions'); }
};
