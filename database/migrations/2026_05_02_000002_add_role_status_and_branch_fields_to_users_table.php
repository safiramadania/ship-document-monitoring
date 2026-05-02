<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default(UserRole::UserCabang->value)->after('password')->index();
            $table->string('status')->default(UserStatus::Pending->value)->after('role')->index();
            $table->foreignId('branch_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('job_title')->nullable()->after('branch_id');
            $table->foreignId('approved_by')->nullable()->after('job_title')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejected_reason')->nullable()->after('approved_at');
            $table->timestamp('last_login_at')->nullable()->after('rejected_reason');
            $table->timestamp('last_seen_at')->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn([
                'role',
                'status',
                'job_title',
                'approved_at',
                'rejected_reason',
                'last_login_at',
                'last_seen_at',
            ]);
        });
    }
};
