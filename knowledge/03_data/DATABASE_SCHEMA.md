# Database — schema cognitivo

## `academy_completions`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `module_sic_id`, `completed_at`, `certificate_sic_id`, `drx_awarded`, `completion_type`

## `academy_courses`

Record attuali: **10**

Campi: `id`, `sic_id`, `code`, `title`, `category`, `description`, `rank_required`, `drx_reward`, `certificate_enabled`, `validation_status`, `status`, `source_url`, `official_reference`

## `academy_enrollments`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `course_sic_id`, `status`, `progress_pct`, `started_at`, `completed_at`

## `academy_lesson_progress`

Record attuali: **0**

Campi: `id`, `sic_id`, `enrollment_sic_id`, `lesson_sic_id`, `status`, `completed_at`

## `academy_lessons`

Record attuali: **46**

Campi: `id`, `sic_id`, `course_sic_id`, `lesson_order`, `title`, `lesson_type`, `content_html`, `video_url`, `duration_min`, `drx_reward`, `status`

## `academy_modules`

Record attuali: **10**

Campi: `id`, `sic_id`, `category`, `title`, `description`, `drx_reward`, `rank_required`, `status`, `legacy_sic_id`

## `achievements`

Record attuali: **0**

Campi: `id`, `sic_id`, `owner_type`, `owner_sic_id`, `achievement_type`, `title`, `source_sic_id`, `awarded_at`

## `acl_permissions`

Record attuali: **84**

Campi: `id`, `sic_id`, `subject_type`, `subject_code`, `scope_sic_id`, `resource`, `action`, `effect`, `created_at`

## `addiction_areas`

Record attuali: **10**

Campi: `id`, `code`, `label`, `category`, `status`

## `admin_resets`

Record attuali: **0**

Campi: `id`, `sic_id`, `target_user_sic_id`, `admin_user_sic_id`, `scope_sic_id`, `reason`, `recovery_token_hash`, `expires_at`, `used_at`, `created_at`

## `assessment_sessions`

Record attuali: **0**

Campi: `id`, `sic_id`, `assessment_sic_id`, `user_sic_id`, `started_at`, `completed_at`, `score`, `interpretation_code`, `raw_json`, `visibility`

## `assessments`

Record attuali: **4**

Campi: `id`, `sic_id`, `code`, `title`, `category`, `evidence_level`, `professional_only`, `config_json`, `status`

## `audit_log`

Record attuali: **0**

Campi: `id`, `sic_id`, `actor_sic_id`, `action`, `target_sic_id`, `ip_hash`, `metadata_json`, `created_at`

## `certificates`

Record attuali: **0**

Campi: `id`, `sic_id`, `owner_sic_id`, `certificate_type`, `title`, `source_sic_id`, `verify_token`, `pdf_path`, `issued_at`, `metadata_json`

## `chat_conversations`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `title`, `status`, `created_at`, `updated_at`

## `chat_messages`

Record attuali: **0**

Campi: `id`, `sic_id`, `conversation_sic_id`, `user_sic_id`, `role`, `content`, `grounded`, `sources_json`, `privacy_scope`, `created_at`

## `checkins`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `checkin_date`, `mood`, `stress`, `craving`, `sleep`, `note`, `privacy_scope`, `created_at`

## `club_attendance`

Record attuali: **0**

Campi: `id`, `sic_id`, `club_sic_id`, `user_sic_id`, `meeting_date`, `verified_by_sic_id`, `drx_awarded`, `created_at`

## `club_compliance_snapshots`

Record attuali: **0**

Campi: `id`, `sic_id`, `club_sic_id`, `family_count`, `servant_count`, `meeting_frequency`, `pre_multiplication`, `multiplication_required`, `compliance_score`, `details_json`, `recorded_at`

## `club_contacts`

Record attuali: **22**

Campi: `id`, `sic_id`, `club_sic_id`, `contact_type`, `label`, `value`, `public`, `verified_at`, `source_url`

## `club_memberships`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `club_sic_id`, `membership_type`, `status`, `joined_at`, `left_at`

## `club_metrics`

Record attuali: **0**

Campi: `id`, `sic_id`, `club_sic_id`, `metric_code`, `metric_value`, `period`, `recorded_at`

## `club_multiplications`

Record attuali: **0**

Campi: `id`, `sic_id`, `origin_club_sic_id`, `new_club_sic_id`, `planned_at`, `completed_at`, `status`, `notes`

## `club_rank_history`

Record attuali: **0**

Campi: `id`, `sic_id`, `club_sic_id`, `rank_name`, `qualifying_drx`, `metrics_json`, `awarded_at`

## `club_rank_requirements`

Record attuali: **9**

Campi: `rank_name`, `threshold_drx`, `min_active_families`, `min_events_365`, `min_checkins_365`, `min_academy_completions_365`, `min_volunteer_hours_365`, `min_hudolin_compliance`

## `club_rank_unlocks`

Record attuali: **9**

Campi: `id`, `rank_name`, `unlock_code`, `label`, `description`

## `club_socials`

Record attuali: **0**

Campi: `id`, `sic_id`, `club_sic_id`, `platform`, `url`, `status`, `last_verified`

## `club_status_history`

Record attuali: **915**

Campi: `id`, `sic_id`, `entity_sic_id`, `status`, `valid_from`, `valid_to`, `first_seen`, `last_seen`, `source_sic_id`, `confidence`, `notes`, `created_at`

## `community_drop_recipients`

Record attuali: **0**

Campi: `id`, `sic_id`, `drop_sic_id`, `user_sic_id`, `amount`, `ledger_sic_id`, `created_at`

## `community_drop_rules`

Record attuali: **5**

Campi: `id`, `threshold_drx`, `label`, `recipient_count`, `max_drop_drx`, `active`

## `community_drops`

Record attuali: **0**

Campi: `id`, `sic_id`, `rule_threshold`, `trigger_reserve`, `community_pool_before`, `total_distributed`, `recipient_count`, `status`, `created_at`, `executed_at`

## `community_insights`

Record attuali: **0**

Campi: `id`, `sic_id`, `insight_type`, `period`, `scope_sic_id`, `metric_key`, `metric_value`, `dimensions_json`, `privacy_level`, `created_at`

## `consent_log`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `consent_code`, `value`, `version`, `created_at`

## `country_research_status`

Record attuali: **36**

Campi: `id`, `sic_id`, `country`, `languages_used`, `queries_executed`, `sources_checked`, `verified_clubs`, `probable_clubs`, `historical_clubs`, `completeness_score`, `current_network_status`, `latest_source_date`, `last_researched_at`, `missing_data`, `next_queries`

## `daily_access`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `access_date`, `drx_awarded`

## `dao_comments`

Record attuali: **0**

Campi: `id`, `sic_id`, `proposal_sic_id`, `user_sic_id`, `body`, `status`, `created_at`

## `dao_proposal_votes`

Record attuali: **0**

Campi: `id`, `sic_id`, `proposal_sic_id`, `user_sic_id`, `choice`, `weight`, `created_at`

## `dao_proposals`

Record attuali: **0**

Campi: `id`, `sic_id`, `scope_sic_id`, `title`, `body`, `proposal_type`, `voting_method`, `quorum_pct`, `threshold_pct`, `status`, `opens_at`, `closes_at`, `created_by_sic_id`, `created_at`

## `dao_topics`

Record attuali: **3**

Campi: `id`, `sic_id`, `scope_sic_id`, `title`, `body`, `visibility`, `minimum_rank`, `allowed_roles_json`, `voting_enabled`, `status`, `created_by_sic_id`, `created_at`, `legacy_sic_id`

## `dao_votes`

Record attuali: **0**

Campi: `id`, `sic_id`, `topic_sic_id`, `user_sic_id`, `choice`, `created_at`

## `data_conflicts`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `field_name`, `value_a`, `source_a_sic_id`, `value_b`, `source_b_sic_id`, `resolution_status`, `resolved_value`, `resolved_by_sic_id`, `resolved_at`, `created_at`

## `dependex_world_edges`

Record attuali: **504**

Campi: `id`, `edge_sic_id`, `source_sic_id`, `target_sic_id`, `relation`, `confidence`, `source_reference`, `created_at`

## `dependex_world_registry`

Record attuali: **546**

Campi: `id`, `sic_id`, `entity_name`, `original_type`, `network_level`, `network_rank`, `rank_color`, `continent`, `country`, `region`, `province`, `city`, `address`, `latitude`, `longitude`, `geo_accuracy`, `status`, `parent_sic_id`, `source_url`, `source_type`, `language`, `meeting`, `public_contact`, `notes`, `direct_children`, `network_descendants`, `public_data_score`, `is_synthetic`, `created_at`, `updated_at`, `website`, `phone`, `email`, `geo_confidence`, `geocoded_from`, `geocoded_at`, `original_name`, `translated_name`, `original_hierarchy`, `postal_code`, `facebook`, `instagram`, `youtube`, `linkedin`, `x_twitter`, `founding_year`, `closure_year`, `reopening_year`, `hudolin_confirmed`, `confidence_score`, `last_verified`, `geo_provider`, `geo_provider_ref`, `territorial_association`, `regional_federation`, `national_federation`

## `document_templates`

Record attuali: **10**

Campi: `id`, `sic_id`, `code`, `title`, `category`, `schema_json`, `html_template`, `status`

## `documents`

Record attuali: **0**

Campi: `id`, `sic_id`, `owner_scope_sic_id`, `doc_type`, `title`, `file_path`, `hash_sha256`, `blockchain_tx`, `status`, `created_at`, `legacy_sic_id`

## `donations`

Record attuali: **0**

Campi: `id`, `sic_id`, `project_sic_id`, `donor_user_sic_id`, `amount_eur`, `anonymous`, `created_at`, `status`, `payment_reference`

## `drx_entitlements`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `amount`, `source_type`, `source_sic_id`, `rank_eligible`, `status`, `accrued_at`, `claim_deadline`, `claimed_at`, `reserved_at`, `ledger_sic_id`

## `drx_idempotency`

Record attuali: **0**

Campi: `id`, `idempotency_key`, `ledger_sic_id`, `created_at`

## `drx_ledger`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `club_sic_id`, `amount`, `source_type`, `source_sic_id`, `rank_eligible`, `status`, `blockchain_tx`, `created_at`, `idempotency_key`, `metadata_json`, `source_date`

## `drx_settings`

Record attuali: **12**

Campi: `key`, `value`, `updated_at`

## `drx_vault_ledger`

Record attuali: **0**

Campi: `id`, `sic_id`, `pool`, `direction`, `amount`, `source_type`, `source_sic_id`, `metadata_json`, `created_at`

## `email_preferences`

Record attuali: **0**

Campi: `user_sic_id`, `transactional`, `club`, `academy`, `events`, `dao`, `marketing`, `updated_at`

## `email_queue`

Record attuali: **0**

Campi: `id`, `sic_id`, `template_code`, `to_email`, `user_sic_id`, `subject`, `html_body`, `status`, `attempts`, `scheduled_at`, `sent_at`, `last_error`, `created_at`

## `email_templates`

Record attuali: **9**

Campi: `id`, `sic_id`, `code`, `subject`, `html_body`, `active`

## `entity_aliases`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `alias`, `language`, `alias_type`, `source_sic_id`, `confidence`

## `entity_field_conflicts`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `field_name`, `value_a`, `source_a`, `value_b`, `source_b`, `resolution_status`, `resolved_value`, `resolved_at`

## `entity_source_links`

Record attuali: **630**

Campi: `id`, `entity_sic_id`, `source_sic_id`, `relation`, `confidence`, `field_name`, `value_text`, `created_at`

## `entity_status_history`

Record attuali: **519**

Campi: `id`, `sic_id`, `entity_sic_id`, `status`, `valid_from`, `valid_to`, `first_seen`, `last_seen`, `source_sic_id`, `confidence`, `notes`

## `event_registrations`

Record attuali: **0**

Campi: `id`, `sic_id`, `event_sic_id`, `user_sic_id`, `status`, `checked_in_at`, `drx_awarded`, `created_at`, `verified_by_sic_id`, `checkin_code`

## `events`

Record attuali: **3**

Campi: `id`, `sic_id`, `owner_scope_sic_id`, `type`, `title`, `description`, `starts_at`, `ends_at`, `venue`, `comune`, `visibility`, `rank_required`, `drx_reward`, `status`, `created_at`, `legacy_sic_id`, `capacity`, `price_eur`, `online_url`, `address`, `latitude`, `longitude`

## `families`

Record attuali: **0**

Campi: `id`, `sic_id`, `name`, `club_sic_id`, `status`, `created_at`

## `family_members`

Record attuali: **0**

Campi: `id`, `family_sic_id`, `user_sic_id`, `relation_type`, `status`

## `field_confidence`

Record attuali: **630**

Campi: `id`, `entity_sic_id`, `field_name`, `confidence`, `source_sic_id`, `verified_at`

## `form_submissions`

Record attuali: **0**

Campi: `id`, `sic_id`, `form_sic_id`, `user_sic_id`, `scope_sic_id`, `payload_json`, `document_sic_id`, `created_at`

## `form_templates`

Record attuali: **4**

Campi: `id`, `sic_id`, `code`, `title`, `schema_json`, `output_type`, `status`

## `generated_documents`

Record attuali: **0**

Campi: `id`, `sic_id`, `template_sic_id`, `owner_sic_id`, `scope_sic_id`, `title`, `payload_json`, `html_path`, `pdf_path`, `sha256`, `status`, `created_at`

## `geo_enrichment_queue`

Record attuali: **379**

Campi: `id`, `sic_id`, `entity_sic_id`, `query`, `priority`, `status`, `attempts`, `last_error`, `geocoded_at`, `created_at`, `updated_at`

## `geo_history`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `latitude`, `longitude`, `geo_accuracy`, `geocoded_from`, `geocoder`, `geo_confidence`, `valid_from`, `valid_to`, `source_url`

## `geo_observations_v2`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `latitude`, `longitude`, `geo_accuracy`, `confidence`, `geocoded_from`, `provider`, `provider_ref`, `raw_json`, `observed_at`, `selected`

## `geocode_queue`

Record attuali: **352**

Campi: `id`, `entity_sic_id`, `query_text`, `status`, `attempts`, `provider`, `result_json`, `last_error`, `updated_at`, `accuracy_hint`, `fallback_level`, `provider_ref`

## `global_network_entities`

Record attuali: **344**

Campi: `id`, `sic_id`, `level`, `country`, `entity_name`, `region`, `city`, `address`, `status`, `parent_name`, `source_url`, `source_type`, `language`, `meeting`, `public_contact`, `last_checked`, `notes`, `legacy_sic_id`

## `hudolin_rules`

Record attuali: **10**

Campi: `id`, `sic_id`, `code`, `title`, `description`, `rule_type`, `config_json`, `status`, `version`, `updated_at`, `authority`, `source_url`, `policy_class`, `effective_note`

## `integration_adapters`

Record attuali: **8**

Campi: `id`, `code`, `label`, `interface_version`, `status`, `config_json`

## `journal_entries`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `entry_date`, `mood`, `gratitude`, `note`, `visibility`, `created_at`, `updated_at`

## `lifestyle_dimensions`

Record attuali: **14**

Campi: `id`, `code`, `label`, `icon`

## `lifestyle_scores`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `dimension_code`, `score`, `source`, `recorded_at`

## `meta`

Record attuali: **2**

Campi: `key`, `value`

## `mission_completions`

Record attuali: **0**

Campi: `id`, `sic_id`, `mission_sic_id`, `user_sic_id`, `club_sic_id`, `verified`, `drx_awarded`, `completed_at`, `completion_key`

## `missions`

Record attuali: **6**

Campi: `id`, `sic_id`, `title`, `category`, `description`, `drx_reward`, `rank_required`, `repeat_rule`, `status`, `legacy_sic_id`

## `module_registry`

Record attuali: **8**

Campi: `code`, `label`, `source_path`, `status`, `version`, `notes`, `updated_at`

## `network_entities`

Record attuali: **543**

Campi: `id`, `sic_id`, `level`, `entity_name`, `country`, `region`, `province`, `comune`, `address`, `cap`, `phone`, `email`, `website`, `parent_name`, `parent_sic_id`, `meeting_day`, `meeting_time`, `servitore_insegnante`, `source_url`, `source_type`, `source_date`, `verification_status`, `active_status`, `notes`, `latitude`, `longitude`, `created_at`, `updated_at`, `site_scope`, `map_enabled`, `network_enabled`, `legacy_sic_id`, `geo_accuracy`, `geocoded_from`, `geocoder`, `geocoded_at`, `geo_confidence`, `continent`, `original_entity_type`, `original_local_name`, `original_hierarchy`

## `notifications`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `channel`, `title`, `body`, `target_url`, `read_at`, `created_at`

## `osint_changes`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `change_type`, `field_name`, `old_value`, `new_value`, `source_sic_id`, `detected_at`

## `osint_conflicts`

Record attuali: **0**

Campi: `id`, `sic_id`, `entity_sic_id`, `field_name`, `value_a`, `source_a_sic_id`, `value_b`, `source_b_sic_id`, `resolution_status`, `selected_value`, `resolved_by_sic_id`, `resolved_at`, `created_at`

## `osint_entities_aliases`

Record attuali: **541**

Campi: `id`, `sic_id`, `entity_sic_id`, `alias`, `language`, `alias_type`, `source_url`, `confidence`, `created_at`

## `osint_entity_sources`

Record attuali: **497**

Campi: `id`, `sic_id`, `entity_sic_id`, `source_sic_id`, `is_primary`, `relevant_summary`, `confidence`, `created_at`

## `osint_sources`

Record attuali: **84**

Campi: `id`, `sic_id`, `url`, `domain`, `title`, `publisher`, `source_level`, `language`, `publication_date`, `retrieved_at`, `last_modified`, `content_hash`, `status`, `notes`

## `privacy_consents`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `consent_code`, `version`, `granted`, `granted_at`, `revoked_at`

## `professionals`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `profession`, `register_name`, `register_number`, `verification_status`, `organization`, `territory`, `public_profile`

## `project_updates`

Record attuali: **0**

Campi: `id`, `sic_id`, `project_sic_id`, `title`, `body`, `amount_spent`, `created_by_sic_id`, `created_at`

## `projects`

Record attuali: **2**

Campi: `id`, `sic_id`, `owner_scope_sic_id`, `title`, `category`, `description`, `goal_eur`, `raised_eur`, `status`, `starts_at`, `ends_at`, `legacy_sic_id`

## `rank_events`

Record attuali: **0**

Campi: `id`, `sic_id`, `owner_type`, `owner_sic_id`, `old_rank`, `new_rank`, `qualifying_drx`, `source`, `created_at`

## `rank_unlocks`

Record attuali: **9**

Campi: `id`, `rank_name`, `unlock_code`, `label`, `description`, `audience`

## `ranks`

Record attuali: **9**

Campi: `id`, `sic_id`, `rank_order`, `name`, `threshold_drx`, `unlocks_json`, `legacy_sic_id`

## `research_changesets`

Record attuali: **0**

Campi: `id`, `sic_id`, `run_sic_id`, `entity_sic_id`, `change_type`, `before_json`, `after_json`, `created_at`

## `research_runs`

Record attuali: **0**

Campi: `id`, `sic_id`, `country`, `started_at`, `completed_at`, `queries_count`, `new_entities`, `updated_entities`, `new_sources`, `notes`

## `research_sources`

Record attuali: **543**

Campi: `id`, `sic_id`, `entity_sic_id`, `source_url`, `source_domain`, `source_title`, `publisher`, `source_date`, `retrieved_at`, `language`, `source_level`, `source_status`, `content_hash`, `summary`

## `research_tasks`

Record attuali: **1502**

Campi: `id`, `sic_id`, `country`, `entity_sic_id`, `priority`, `task_type`, `query_text`, `status`, `reason`, `created_at`, `updated_at`

## `research_terms`

Record attuali: **37**

Campi: `id`, `sic_id`, `term`, `language`, `country`, `canonical_term`, `term_type`, `source_sic_id`, `confidence`, `active`, `created_at`

## `roles`

Record attuali: **13**

Campi: `id`, `code`, `label`, `level`

## `security_rate_limits`

Record attuali: **0**

Campi: `bucket`, `hits`, `window_start`, `blocked_until`

## `settings`

Record attuali: **18**

Campi: `key`, `value`

## `sic_registry`

Record attuali: **194**

Campi: `id`, `sic_id`, `entity_type`, `entity_id`, `parent_sic_id`, `scope_sic_id`, `status`, `created_at`, `metadata_json`

## `sobriety_accrual_state`

Record attuali: **0**

Campi: `user_sic_id`, `last_awarded_date`, `total_awarded_days`, `updated_at`

## `sobriety_milestones`

Record attuali: **17**

Campi: `id`, `days`, `title`, `drx_reward`, `badge_code`, `nft_eligible`, `certificate_enabled`

## `sobriety_records`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `start_date`, `current_streak`, `lifetime_days`, `opted_in_leaderboard`, `display_mode`, `updated_at`

## `sqlite_sequence`

Record attuali: **41**

Campi: `name`, `seq`

## `treasury_accounts`

Record attuali: **0**

Campi: `id`, `sic_id`, `owner_scope_sic_id`, `currency`, `account_type`, `label`, `status`

## `treasury_transactions`

Record attuali: **0**

Campi: `id`, `sic_id`, `account_sic_id`, `direction`, `amount`, `category`, `description`, `document_sic_id`, `approved_by_sic_id`, `created_at`

## `trusted_devices`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `token_hash`, `label`, `last_seen_at`, `expires_at`, `revoked_at`

## `user_preferences`

Record attuali: **0**

Campi: `user_sic_id`, `language`, `sobriety_leaderboard`, `leaderboard_display`, `email_notifications`, `push_notifications`, `reduced_motion`, `updated_at`

## `user_roles`

Record attuali: **0**

Campi: `id`, `user_sic_id`, `role_code`, `scope_sic_id`, `status`, `valid_from`, `valid_to`

## `user_support_areas`

Record attuali: **0**

Campi: `id`, `sic_id`, `user_sic_id`, `area_code`, `source`, `visibility`, `created_at`

## `users`

Record attuali: **0**

Campi: `id`, `sic_id`, `email`, `display_name`, `password_hash`, `status`, `rank_name`, `drx_balance`, `sobriety_start_date`, `recovery_code_hash`, `recovery_code_changed_at`, `last_login_at`, `created_at`, `legacy_sic_id`

## `volunteer_actions`

Record attuali: **0**

Campi: `id`, `sic_id`, `project_sic_id`, `user_sic_id`, `hours`, `description`, `verified`, `drx_awarded`, `created_at`

## `workflow_instances`

Record attuali: **0**

Campi: `id`, `sic_id`, `workflow_sic_id`, `owner_scope_sic_id`, `current_step`, `status`, `context_json`, `created_at`

## `workflow_tasks`

Record attuali: **0**

Campi: `id`, `sic_id`, `workflow_instance_sic_id`, `title`, `assigned_user_sic_id`, `assigned_role`, `status`, `due_at`, `completed_at`, `created_at`

## `workflows`

Record attuali: **0**

Campi: `id`, `sic_id`, `code`, `title`, `owner_scope`, `config_json`, `status`
