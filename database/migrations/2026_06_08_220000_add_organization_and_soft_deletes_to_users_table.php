<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Two optional org fields for the sub-admin form (name + free-text
    // details), plus a soft-deletes column so an admin can revoke access
    // without losing audit trails and the login form can surface a
    // tailored "account removed" message instead of the generic
    // invalid-credentials error.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'organization_name')) {
                $table->string('organization_name', 255)->nullable()->after('address');
            }
            if (! Schema::hasColumn('users', 'organization_details')) {
                $table->text('organization_details')->nullable()->after('organization_name');
            }
            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'organization_details')) {
                $table->dropColumn('organization_details');
            }
            if (Schema::hasColumn('users', 'organization_name')) {
                $table->dropColumn('organization_name');
            }
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
