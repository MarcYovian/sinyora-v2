<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Index yang ditambahkan berdasarkan kolom yang sering digunakan untuk:
     * - Pencarian (search)
     * - Filter (where clause)
     * - Pengurutan (order by)
     * - Relasi (foreign key without index)
     */
    public function up(): void
    {
        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('name', 'idx_users_name');
            $table->index('created_at', 'idx_users_created_at');
        });

        // Events table
        Schema::table('events', function (Blueprint $table) {
            $table->index('name', 'idx_events_name');
            $table->index('status', 'idx_events_status');
            $table->index('start_recurring', 'idx_events_start_recurring');
            $table->index('end_recurring', 'idx_events_end_recurring');
            $table->index('recurrence_type', 'idx_events_recurrence_type');
            $table->index('created_at', 'idx_events_created_at');
            // Composite index for date range queries
            $table->index(['start_recurring', 'end_recurring'], 'idx_events_date_range');
            // Composite index for filtering by status and date
            $table->index(['status', 'start_recurring'], 'idx_events_status_date');
        });

        // Event recurrences table
        Schema::table('event_recurrences', function (Blueprint $table) {
            $table->index('date', 'idx_event_recurrences_date');
            $table->index('time_start', 'idx_event_recurrences_time_start');
            // Composite index for date and time queries
            $table->index(['date', 'time_start'], 'idx_event_recurrences_date_time');
        });

        // Articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->index('title', 'idx_articles_title');
            $table->index('is_published', 'idx_articles_is_published');
            $table->index('published_at', 'idx_articles_published_at');
            $table->index('views', 'idx_articles_views');
            $table->index('created_at', 'idx_articles_created_at');
            $table->index('deleted_at', 'idx_articles_deleted_at');
            // Composite index for published articles listing
            $table->index(['is_published', 'published_at'], 'idx_articles_published_listing');
            // Composite index for soft delete with published status
            $table->index(['deleted_at', 'is_published'], 'idx_articles_active_published');
        });

        // Article categories table
        Schema::table('article_categories', function (Blueprint $table) {
            $table->index('name', 'idx_article_categories_name');
        });

        // Tags table
        Schema::table('tags', function (Blueprint $table) {
            $table->index('name', 'idx_tags_name');
        });

        // Article_tag pivot table
        Schema::table('article_tag', function (Blueprint $table) {
            // Composite index for efficient pivot table queries
            $table->index(['article_id', 'tag_id'], 'idx_article_tag_composite');
        });

        // Assets table
        Schema::table('assets', function (Blueprint $table) {
            $table->index('name', 'idx_assets_name');
            $table->index('slug', 'idx_assets_slug');
            $table->index('is_active', 'idx_assets_is_active');
            $table->index('quantity', 'idx_assets_quantity');
            $table->index('storage_location', 'idx_assets_storage_location');
            // Composite index for active assets with quantity
            $table->index(['is_active', 'quantity'], 'idx_assets_active_quantity');
        });

        // Asset categories table
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->index('name', 'idx_asset_categories_name');
        });

        // Borrowings table
        Schema::table('borrowings', function (Blueprint $table) {
            $table->index('status', 'idx_borrowings_status');
            $table->index('start_datetime', 'idx_borrowings_start_datetime');
            $table->index('end_datetime', 'idx_borrowings_end_datetime');
            $table->index('borrower', 'idx_borrowings_borrower');
            $table->index('created_at', 'idx_borrowings_created_at');
            // Composite index for date range queries
            $table->index(['start_datetime', 'end_datetime'], 'idx_borrowings_date_range');
            // Composite index for status and date filtering
            $table->index(['status', 'start_datetime'], 'idx_borrowings_status_date');
        });

        // Asset_borrowing pivot table
        Schema::table('asset_borrowing', function (Blueprint $table) {
            // Composite index for efficient pivot table queries
            $table->index(['borrowing_id', 'asset_id'], 'idx_asset_borrowing_composite');
        });

        // Documents table
        Schema::table('documents', function (Blueprint $table) {
            $table->index('status', 'idx_documents_status');
            $table->index('doc_date', 'idx_documents_doc_date');
            $table->index('city', 'idx_documents_city');
            $table->index('subject', 'idx_documents_subject');
            $table->index('original_file_name', 'idx_documents_original_file_name');
            $table->index('processed_at', 'idx_documents_processed_at');
            $table->index('created_at', 'idx_documents_created_at');
            // Composite index for status-based queries with date
            $table->index(['status', 'created_at'], 'idx_documents_status_created');
        });

        // Signatures table
        Schema::table('signatures', function (Blueprint $table) {
            $table->index('name', 'idx_signatures_name');
            $table->index('position', 'idx_signatures_position');
        });

        // Organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->index('name', 'idx_organizations_name');
            $table->index('code', 'idx_organizations_code');
            $table->index('is_active', 'idx_organizations_is_active');
        });

        // Locations table
        Schema::table('locations', function (Blueprint $table) {
            $table->index('name', 'idx_locations_name');
            $table->index('is_active', 'idx_locations_is_active');
        });

        // Contacts table
        Schema::table('contacts', function (Blueprint $table) {
            $table->index('name', 'idx_contacts_name');
            $table->index('email', 'idx_contacts_email');
            $table->index('status', 'idx_contacts_status');
            $table->index('created_at', 'idx_contacts_created_at');
        });

        // Guest submitters table
        Schema::table('guest_submitters', function (Blueprint $table) {
            $table->index('name', 'idx_guest_submitters_name');
            $table->index('email', 'idx_guest_submitters_email');
            $table->index('phone_number', 'idx_guest_submitters_phone');
        });

        // Public menus table
        Schema::table('public_menus', function (Blueprint $table) {
            $table->index('main_menu', 'idx_public_menus_main_menu');
            $table->index('is_active', 'idx_public_menus_is_active');
            $table->index('sort', 'idx_public_menus_sort');
            // Composite index for active menus with sorting
            $table->index(['is_active', 'sort'], 'idx_public_menus_active_sort');
        });

        // Schedules table
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('start_time', 'idx_schedules_start_time');
            $table->index('end_time', 'idx_schedules_end_time');
        });

        // Mass schedules table
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->index('day_of_week', 'idx_mass_schedules_day');
            $table->index('start_time', 'idx_mass_schedules_start_time');
            $table->index('is_active', 'idx_mass_schedules_is_active');
            $table->index('label', 'idx_mass_schedules_label');
            // Composite index for filtering active schedules by day
            $table->index(['is_active', 'day_of_week'], 'idx_mass_schedules_active_day');
        });

        // Activities table
        Schema::table('activities', function (Blueprint $table) {
            $table->index('name', 'idx_activities_name');
        });

        // Event categories table
        Schema::table('event_categories', function (Blueprint $table) {
            $table->index('name', 'idx_event_categories_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_name');
            $table->dropIndex('idx_users_created_at');
        });

        // Events table
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_events_name');
            $table->dropIndex('idx_events_status');
            $table->dropIndex('idx_events_start_recurring');
            $table->dropIndex('idx_events_end_recurring');
            $table->dropIndex('idx_events_recurrence_type');
            $table->dropIndex('idx_events_created_at');
            $table->dropIndex('idx_events_date_range');
            $table->dropIndex('idx_events_status_date');
        });

        // Event recurrences table
        Schema::table('event_recurrences', function (Blueprint $table) {
            $table->dropIndex('idx_event_recurrences_date');
            $table->dropIndex('idx_event_recurrences_time_start');
            $table->dropIndex('idx_event_recurrences_date_time');
        });

        // Articles table
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex('idx_articles_title');
            $table->dropIndex('idx_articles_is_published');
            $table->dropIndex('idx_articles_published_at');
            $table->dropIndex('idx_articles_views');
            $table->dropIndex('idx_articles_created_at');
            $table->dropIndex('idx_articles_deleted_at');
            $table->dropIndex('idx_articles_published_listing');
            $table->dropIndex('idx_articles_active_published');
        });

        // Article categories table
        Schema::table('article_categories', function (Blueprint $table) {
            $table->dropIndex('idx_article_categories_name');
        });

        // Tags table
        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('idx_tags_name');
        });

        // Article_tag pivot table
        Schema::table('article_tag', function (Blueprint $table) {
            $table->dropIndex('idx_article_tag_composite');
        });

        // Assets table
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_assets_name');
            $table->dropIndex('idx_assets_slug');
            $table->dropIndex('idx_assets_is_active');
            $table->dropIndex('idx_assets_quantity');
            $table->dropIndex('idx_assets_storage_location');
            $table->dropIndex('idx_assets_active_quantity');
        });

        // Asset categories table
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropIndex('idx_asset_categories_name');
        });

        // Borrowings table
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropIndex('idx_borrowings_status');
            $table->dropIndex('idx_borrowings_start_datetime');
            $table->dropIndex('idx_borrowings_end_datetime');
            $table->dropIndex('idx_borrowings_borrower');
            $table->dropIndex('idx_borrowings_created_at');
            $table->dropIndex('idx_borrowings_date_range');
            $table->dropIndex('idx_borrowings_status_date');
        });

        // Asset_borrowing pivot table
        Schema::table('asset_borrowing', function (Blueprint $table) {
            $table->dropIndex('idx_asset_borrowing_composite');
        });

        // Documents table
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('idx_documents_status');
            $table->dropIndex('idx_documents_doc_date');
            $table->dropIndex('idx_documents_city');
            $table->dropIndex('idx_documents_subject');
            $table->dropIndex('idx_documents_original_file_name');
            $table->dropIndex('idx_documents_processed_at');
            $table->dropIndex('idx_documents_created_at');
            $table->dropIndex('idx_documents_status_created');
        });

        // Signatures table
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex('idx_signatures_name');
            $table->dropIndex('idx_signatures_position');
        });

        // Organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_organizations_name');
            $table->dropIndex('idx_organizations_code');
            $table->dropIndex('idx_organizations_is_active');
        });

        // Locations table
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex('idx_locations_name');
            $table->dropIndex('idx_locations_is_active');
        });

        // Contacts table
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('idx_contacts_name');
            $table->dropIndex('idx_contacts_email');
            $table->dropIndex('idx_contacts_status');
            $table->dropIndex('idx_contacts_created_at');
        });

        // Guest submitters table
        Schema::table('guest_submitters', function (Blueprint $table) {
            $table->dropIndex('idx_guest_submitters_name');
            $table->dropIndex('idx_guest_submitters_email');
            $table->dropIndex('idx_guest_submitters_phone');
        });

        // Public menus table
        Schema::table('public_menus', function (Blueprint $table) {
            $table->dropIndex('idx_public_menus_main_menu');
            $table->dropIndex('idx_public_menus_is_active');
            $table->dropIndex('idx_public_menus_sort');
            $table->dropIndex('idx_public_menus_active_sort');
        });

        // Schedules table
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_schedules_start_time');
            $table->dropIndex('idx_schedules_end_time');
        });

        // Mass schedules table
        Schema::table('mass_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_mass_schedules_day');
            $table->dropIndex('idx_mass_schedules_start_time');
            $table->dropIndex('idx_mass_schedules_is_active');
            $table->dropIndex('idx_mass_schedules_label');
            $table->dropIndex('idx_mass_schedules_active_day');
        });

        // Activities table
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('idx_activities_name');
        });

        // Event categories table
        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropIndex('idx_event_categories_name');
        });
    }
};
