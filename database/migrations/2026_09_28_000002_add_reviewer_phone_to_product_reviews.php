<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * customerStoreReview() previously de-duplicated reviews by the
     * logged-in customer's email. Email is now optional, so a mobile-only
     * customer's reviews were never matched (NULL <> NULL in SQL), and the
     * form even required an email that was never actually used. This adds
     * reviewer_phone so reviews can be tied to the one identifier every
     * account is guaranteed to have: mobile.
     */
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->string('reviewer_phone')->nullable()->after('reviewer_email');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropColumn('reviewer_phone');
        });
    }
};
