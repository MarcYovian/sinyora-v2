<?php

use App\Enums\EventApprovalStatus;
use App\Enums\EventRecurrenceType;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->date('start_recurring');
            $table->date('end_recurring');
            $table->enum('status', EventApprovalStatus::values())->default(EventApprovalStatus::PENDING);
            $table->enum('recurrence_type', EventRecurrenceType::values())->default(EventRecurrenceType::CUSTOM);
            $table->string('created_by')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained();
            $table->foreignId('event_category_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
