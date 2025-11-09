<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('discoveries', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('moderated_at')->nullable()->after('rejection_reason');
            $table->foreignId('moderated_by')->nullable()->after('moderated_at')->constrained('users');
        });
    }

    public function down()
    {
        Schema::table('discoveries', function (Blueprint $table) {
            $table->dropColumn(['rejection_reason', 'moderated_at', 'moderated_by']);
        });
    }
};
