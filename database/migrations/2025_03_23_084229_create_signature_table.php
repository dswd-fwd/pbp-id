<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('signature', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['respondent', 'interviewer']); // Add the type column
            $table->text('sign_image');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature');
    }
};


/// final syntax for the signature

/// dapat 1 and 2 ang ginamit instead direct word na 'respondent' and 'interviewer'
