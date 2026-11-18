<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes everything the old website stored that the mobile app does not use:
 * the business registry and its statistics, itineraries, the trip planner,
 * the CRM, billing, the concierge widget, email analysis, the audit trail and
 * the per-user AI companion with its chat tables.
 *
 * Written for both starting points. On a database copied from the website it
 * drops the tables and columns; on a fresh install, where none of them were
 * ever created, every statement is a no-op.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'accounts', 'activity_log', 'agent_conversations', 'agent_conversation_messages', 'ai_assistants',
        'ai_invocations', 'ai_invocation_payloads', 'assistants', 'assistant_types', 'availability_days',
        'badges', 'badge_point_of_interest', 'billing_checkouts', 'billing_events', 'billing_transactions',
        'booking_requests', 'booking_request_deliveries', 'booking_request_events', 'catchments',
        'chat_messageables', 'chat_messages', 'chat_participants', 'chat_sessions', 'company_financials',
        'concierge_conversations', 'concierge_messages', 'concierge_recordings', 'contact_reveals', 'counties',
        'crm_analyses', 'crm_contacts', 'crm_notes', 'crm_organizations', 'email_attachments', 'email_categories',
        'email_filter_rules', 'email_messages', 'email_replies', 'email_signals', 'email_temperatures',
        'email_threads', 'email_thread_statuses', 'eurostat_observations', 'features', 'feature_plan',
        'financial_benchmarks', 'ins_matrices', 'ins_observations', 'itineraries', 'itinerary_stops',
        'knowledge_entries', 'leads', 'lead_statuses', 'localities', 'locality_boundaries', 'locality_insights',
        'locality_redirects', 'locality_supplies', 'mailboxes', 'mailbox_statuses', 'map_features',
        'metadata_keys', 'metadata_values', 'notification_logs', 'operator_kinds', 'planner_appearances',
        'planner_conversations', 'planner_messages', 'planner_outreach', 'plans', 'plan_prices',
        'point_of_interest_protected_area', 'poi_requests', 'protected_areas', 'registry_benchmarks',
        'registry_datasets', 'shares', 'subscriptions', 'subscription_items', 'supply_benchmarks',
        'tourist_guides', 'views', 'votes', 'widgets',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();

        if (Schema::hasColumn('users', 'account_id')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('account_id');
            });
        }

        foreach (['date_of_birth', 'gender', 'enable_influence_mode'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }

        if (Schema::hasColumn('legal_acceptances', 'account_id')) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->dropForeign(['account_id']);
                $table->dropIndex(['account_id', 'accepted_at']);
                $table->dropColumn('account_id');
            });
        }

        // The acknowledgement of business-contract clauses and the data processing
        // agreement belonged to the business product; a traveller app has neither.
        DB::table('legal_acceptances')->whereIn('slug', ['unusual-clauses-acknowledgement', 'data-processing-agreement'])->delete();
        DB::table('legal_documents')->where('slug', 'data-processing-agreement')->delete();

        if (Schema::hasColumn('point_of_interest_categories', 'kind')) {
            Schema::table('point_of_interest_categories', function (Blueprint $table): void {
                $table->dropIndex(['kind']);
                $table->dropColumn('kind');
            });
        }

        // Photos attached to the removed chat messages.
        DB::table('media')->where('model_type', 'App\\Models\\ChatMessage')->delete();
    }

    /**
     * The removed features no longer exist in the code, so there is nothing to
     * restore their tables for.
     */
    public function down(): void
    {
        //
    }
};
