# Database Schema — Live Snapshot

## `academy_completions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `module_sic_id` | TEXT | NO | 0 |
| `completed_at` | TEXT | YES | 0 |
| `certificate_sic_id` | TEXT | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |
| `completion_type` | TEXT | YES | 0 |

## `academy_courses`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `code` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `category` | TEXT | YES | 0 |
| `description` | TEXT | YES | 0 |
| `rank_required` | TEXT | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `certificate_enabled` | INTEGER | YES | 0 |
| `validation_status` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `official_reference` | INTEGER | YES | 0 |

## `academy_enrollments`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `course_sic_id` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `progress_pct` | REAL | YES | 0 |
| `started_at` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |

## `academy_lesson_progress`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `enrollment_sic_id` | TEXT | NO | 0 |
| `lesson_sic_id` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |

## `academy_lessons`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `course_sic_id` | TEXT | NO | 0 |
| `lesson_order` | INTEGER | NO | 0 |
| `title` | TEXT | NO | 0 |
| `lesson_type` | TEXT | YES | 0 |
| `content_html` | TEXT | YES | 0 |
| `video_url` | TEXT | YES | 0 |
| `duration_min` | INTEGER | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |

## `academy_modules`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `category` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `description` | TEXT | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `rank_required` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `acat_news_feed`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `guid` | TEXT | YES | 0 |
| `tag` | TEXT | NO | 0 |
| `tag_label` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `summary` | TEXT | NO | 0 |
| `source_name` | TEXT | NO | 0 |
| `source_url` | TEXT | NO | 0 |
| `published_date` | TEXT | NO | 0 |
| `created_at` | DATETIME | YES | 0 |
| `is_pinned` | INTEGER | YES | 0 |

## `achievements`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `owner_type` | TEXT | YES | 0 |
| `owner_sic_id` | TEXT | YES | 0 |
| `achievement_type` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `awarded_at` | TEXT | YES | 0 |

## `acl_permissions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `subject_type` | TEXT | NO | 0 |
| `subject_code` | TEXT | NO | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `resource` | TEXT | NO | 0 |
| `action` | TEXT | NO | 0 |
| `effect` | TEXT | NO | 0 |
| `created_at` | TEXT | YES | 0 |

## `addiction_areas`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `code` | TEXT | YES | 0 |
| `label` | TEXT | YES | 0 |
| `category` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `admin_resets`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `target_user_sic_id` | TEXT | NO | 0 |
| `admin_user_sic_id` | TEXT | NO | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `reason` | TEXT | YES | 0 |
| `recovery_token_hash` | TEXT | YES | 0 |
| `expires_at` | TEXT | YES | 0 |
| `used_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `assessment_sessions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `assessment_sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `started_at` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |
| `score` | REAL | YES | 0 |
| `interpretation_code` | TEXT | YES | 0 |
| `raw_json` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |

## `assessments`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `code` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `category` | TEXT | YES | 0 |
| `evidence_level` | TEXT | YES | 0 |
| `professional_only` | INTEGER | YES | 0 |
| `config_json` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `audit_log`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `actor_sic_id` | TEXT | YES | 0 |
| `action` | TEXT | NO | 0 |
| `target_sic_id` | TEXT | YES | 0 |
| `ip_hash` | TEXT | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_activity`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `kind` | TEXT | YES | 0 |
| `detail` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_chat_log`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `question` | TEXT | YES | 0 |
| `answer` | TEXT | YES | 0 |
| `grounded` | INTEGER | YES | 0 |
| `source` | TEXT | YES | 0 |
| `ip_hash` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_entities`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `name` | TEXT | NO | 1 |
| `kind` | TEXT | YES | 0 |
| `hits` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_eval_questions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `q` | TEXT | YES | 0 |
| `expected` | TEXT | YES | 0 |
| `tag` | TEXT | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_eval_runs`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `ran_at` | TEXT | YES | 0 |
| `questions` | INTEGER | YES | 0 |
| `hits` | INTEGER | YES | 0 |
| `hit_rate` | REAL | YES | 0 |
| `mrr` | REAL | YES | 0 |
| `detail` | TEXT | YES | 0 |

## `brain_feedback`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `node_id` | TEXT | NO | 0 |
| `vote` | INTEGER | NO | 0 |
| `question` | TEXT | YES | 0 |
| `correction` | TEXT | YES | 0 |
| `ip_hash` | TEXT | YES | 0 |
| `day` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_files`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `path` | TEXT | NO | 1 |
| `hash` | TEXT | YES | 0 |
| `size` | INTEGER | YES | 0 |
| `mtime` | INTEGER | YES | 0 |
| `nodes` | INTEGER | YES | 0 |
| `source_kind` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `last_processed` | TEXT | YES | 0 |

## `brain_jobs`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `kind` | TEXT | YES | 0 |
| `payload` | TEXT | YES | 0 |
| `state` | TEXT | YES | 0 |
| `attempts` | INTEGER | YES | 0 |
| `run_after` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `brain_knowledge`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `source_path` | TEXT | NO | 0 |
| `title` | TEXT | YES | 0 |
| `md_path` | TEXT | YES | 0 |
| `summary` | TEXT | YES | 0 |
| `chars` | INTEGER | YES | 0 |
| `source_hash` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `brain_links`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `node_a` | TEXT | NO | 0 |
| `node_b` | TEXT | NO | 0 |
| `kind` | TEXT | YES | 0 |
| `weight` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `brain_meta`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `k` | TEXT | NO | 1 |
| `v` | TEXT | YES | 0 |

## `brain_node_entities`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `node_id` | TEXT | NO | 1 |
| `entity` | TEXT | NO | 2 |

## `brain_nodes`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | TEXT | NO | 1 |
| `section` | TEXT | YES | 0 |
| `weight` | INTEGER | YES | 0 |
| `path` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `content` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |
| `source` | TEXT | YES | 0 |
| `hash` | TEXT | YES | 0 |
| `lang` | TEXT | YES | 0 |
| `feedback_score` | INTEGER | YES | 0 |
| `review_state` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `cat_clubs_italy`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_name` | TEXT | NO | 0 |
| `level` | TEXT | NO | 0 |
| `region` | TEXT | NO | 0 |
| `province` | TEXT | YES | 0 |
| `city` | TEXT | NO | 0 |
| `address` | TEXT | YES | 0 |
| `cap` | TEXT | YES | 0 |
| `meeting_day` | TEXT | YES | 0 |
| `meeting_time` | TEXT | YES | 0 |
| `meeting_frequency` | TEXT | YES | 0 |
| `meeting_venue` | TEXT | YES | 0 |
| `servitore_insegnante` | TEXT | YES | 0 |
| `phone` | TEXT | YES | 0 |
| `phone_secondary` | TEXT | YES | 0 |
| `email` | TEXT | YES | 0 |
| `website` | TEXT | YES | 0 |
| `parent_entity` | TEXT | YES | 0 |
| `parent_sic_id` | TEXT | YES | 0 |
| `asl_serd_reference` | TEXT | YES | 0 |
| `latitude` | REAL | NO | 0 |
| `longitude` | REAL | NO | 0 |
| `geo_accuracy` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `source_type` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |
| `updated_at` | DATETIME | YES | 0 |
| `families_count` | INTEGER | YES | 0 |

## `certificates`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `owner_sic_id` | TEXT | YES | 0 |
| `certificate_type` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `verify_token` | TEXT | YES | 0 |
| `pdf_path` | TEXT | YES | 0 |
| `issued_at` | TEXT | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |

## `chat_conversations`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `title` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `chat_messages`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `conversation_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `role` | TEXT | NO | 0 |
| `content` | TEXT | NO | 0 |
| `grounded` | INTEGER | YES | 0 |
| `sources_json` | TEXT | YES | 0 |
| `privacy_scope` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `checkins`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `checkin_date` | TEXT | NO | 0 |
| `mood` | INTEGER | YES | 0 |
| `stress` | INTEGER | YES | 0 |
| `craving` | INTEGER | YES | 0 |
| `sleep` | INTEGER | YES | 0 |
| `note` | TEXT | YES | 0 |
| `privacy_scope` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `club_attendance`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `meeting_date` | TEXT | NO | 0 |
| `verified_by_sic_id` | TEXT | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `club_compliance_snapshots`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `family_count` | INTEGER | YES | 0 |
| `servant_count` | INTEGER | YES | 0 |
| `meeting_frequency` | TEXT | YES | 0 |
| `pre_multiplication` | INTEGER | YES | 0 |
| `multiplication_required` | INTEGER | YES | 0 |
| `compliance_score` | REAL | YES | 0 |
| `details_json` | TEXT | YES | 0 |
| `recorded_at` | TEXT | YES | 0 |

## `club_contacts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `contact_type` | TEXT | NO | 0 |
| `label` | TEXT | YES | 0 |
| `value` | TEXT | YES | 0 |
| `public` | INTEGER | YES | 0 |
| `verified_at` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |

## `club_memberships`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `membership_type` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `joined_at` | TEXT | YES | 0 |
| `left_at` | TEXT | YES | 0 |

## `club_metrics`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `club_sic_id` | TEXT | YES | 0 |
| `metric_code` | TEXT | YES | 0 |
| `metric_value` | REAL | YES | 0 |
| `period` | TEXT | YES | 0 |
| `recorded_at` | TEXT | YES | 0 |

## `club_multiplications`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `origin_club_sic_id` | TEXT | YES | 0 |
| `new_club_sic_id` | TEXT | YES | 0 |
| `planned_at` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |

## `club_rank_history`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `rank_name` | TEXT | NO | 0 |
| `qualifying_drx` | REAL | NO | 0 |
| `metrics_json` | TEXT | YES | 0 |
| `awarded_at` | TEXT | YES | 0 |

## `club_rank_requirements`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `rank_name` | TEXT | YES | 1 |
| `threshold_drx` | INTEGER | NO | 0 |
| `min_active_families` | INTEGER | NO | 0 |
| `min_events_365` | INTEGER | NO | 0 |
| `min_checkins_365` | INTEGER | NO | 0 |
| `min_academy_completions_365` | INTEGER | NO | 0 |
| `min_volunteer_hours_365` | REAL | NO | 0 |
| `min_hudolin_compliance` | REAL | NO | 0 |

## `club_rank_unlocks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `rank_name` | TEXT | NO | 0 |
| `unlock_code` | TEXT | NO | 0 |
| `label` | TEXT | NO | 0 |
| `description` | TEXT | YES | 0 |

## `club_socials`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_sic_id` | TEXT | NO | 0 |
| `platform` | TEXT | NO | 0 |
| `url` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `last_verified` | TEXT | YES | 0 |

## `club_status_history`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `status` | TEXT | NO | 0 |
| `valid_from` | TEXT | YES | 0 |
| `valid_to` | TEXT | YES | 0 |
| `first_seen` | TEXT | YES | 0 |
| `last_seen` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `commerce_audit_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `event_type` | VARCHAR(64) | NO | 0 |
| `entity_type` | VARCHAR(64) | NO | 0 |
| `entity_id` | VARCHAR(64) | NO | 0 |
| `ip_address` | VARCHAR(64) | YES | 0 |
| `user_agent` | TEXT | YES | 0 |
| `details_json` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_businesses`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `code` | VARCHAR(64) | NO | 0 |
| `name` | VARCHAR(120) | NO | 0 |
| `domain` | VARCHAR(190) | NO | 0 |
| `allowed_origins` | TEXT | YES | 0 |
| `brand_color` | VARCHAR(16) | YES | 0 |
| `support_email` | VARCHAR(190) | YES | 0 |
| `default_currency` | VARCHAR(8) | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_cart_items`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `cart_id` | VARCHAR(64) | NO | 0 |
| `offer_id` | VARCHAR(64) | NO | 0 |
| `quantity` | INTEGER | YES | 0 |
| `unit_price` | DECIMAL(10,2) | NO | 0 |
| `line_total` | DECIMAL(10,2) | NO | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_carts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `session_token` | VARCHAR(128) | NO | 0 |
| `business_id` | VARCHAR(64) | NO | 0 |
| `currency` | VARCHAR(8) | YES | 0 |
| `coupon_id` | VARCHAR(64) | YES | 0 |
| `discount_amount` | DECIMAL(10,2) | YES | 0 |
| `customer_id` | VARCHAR(64) | YES | 0 |
| `customer_email` | VARCHAR(190) | YES | 0 |
| `source_domain` | VARCHAR(190) | YES | 0 |
| `attribution_json` | TEXT | YES | 0 |
| `status` | VARCHAR(32) | YES | 0 |
| `created_at` | DATETIME | YES | 0 |
| `updated_at` | DATETIME | YES | 0 |

## `commerce_coupons`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `code` | VARCHAR(64) | NO | 0 |
| `business_id` | VARCHAR(64) | YES | 0 |
| `discount_type` | VARCHAR(16) | YES | 0 |
| `discount_value` | DECIMAL(10,2) | NO | 0 |
| `min_order_amount` | DECIMAL(10,2) | YES | 0 |
| `max_uses` | INTEGER | YES | 0 |
| `uses_count` | INTEGER | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `expires_at` | DATETIME | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_customers`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `email` | VARCHAR(190) | NO | 0 |
| `first_name` | VARCHAR(120) | YES | 0 |
| `last_name` | VARCHAR(120) | YES | 0 |
| `phone` | VARCHAR(64) | YES | 0 |
| `company_name` | VARCHAR(190) | YES | 0 |
| `vat_number` | VARCHAR(64) | YES | 0 |
| `fiscal_code` | VARCHAR(64) | YES | 0 |
| `address_line1` | VARCHAR(255) | YES | 0 |
| `city` | VARCHAR(120) | YES | 0 |
| `postal_code` | VARCHAR(32) | YES | 0 |
| `country` | VARCHAR(8) | YES | 0 |
| `marketing_consent` | INTEGER | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_fulfillments`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `order_id` | VARCHAR(64) | NO | 0 |
| `fulfillment_type` | VARCHAR(64) | NO | 0 |
| `status` | VARCHAR(32) | YES | 0 |
| `access_token` | VARCHAR(128) | YES | 0 |
| `content_json` | TEXT | YES | 0 |
| `fulfilled_at` | DATETIME | YES | 0 |

## `commerce_offers`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `business_id` | VARCHAR(64) | NO | 0 |
| `product_id` | VARCHAR(64) | NO | 0 |
| `offer_code` | VARCHAR(64) | NO | 0 |
| `title` | VARCHAR(255) | NO | 0 |
| `subtitle` | VARCHAR(255) | YES | 0 |
| `description` | TEXT | YES | 0 |
| `price` | DECIMAL(10,2) | NO | 0 |
| `currency` | VARCHAR(8) | YES | 0 |
| `vat_rate` | DECIMAL(5,2) | YES | 0 |
| `offer_type` | VARCHAR(32) | YES | 0 |
| `billing_interval` | VARCHAR(32) | YES | 0 |
| `offer_tier` | INTEGER | YES | 0 |
| `badge` | VARCHAR(64) | YES | 0 |
| `bonus_text` | TEXT | YES | 0 |
| `guarantee_text` | TEXT | YES | 0 |
| `return_url` | VARCHAR(255) | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_order_items`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `order_id` | VARCHAR(64) | NO | 0 |
| `offer_id` | VARCHAR(64) | NO | 0 |
| `product_id` | VARCHAR(64) | NO | 0 |
| `product_name` | VARCHAR(255) | NO | 0 |
| `quantity` | INTEGER | YES | 0 |
| `unit_price` | DECIMAL(10,2) | NO | 0 |
| `vat_rate` | DECIMAL(5,2) | YES | 0 |
| `line_total` | DECIMAL(10,2) | NO | 0 |

## `commerce_orders`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `order_number` | VARCHAR(64) | NO | 0 |
| `business_id` | VARCHAR(64) | NO | 0 |
| `cart_id` | VARCHAR(64) | YES | 0 |
| `customer_id` | VARCHAR(64) | NO | 0 |
| `currency` | VARCHAR(8) | YES | 0 |
| `subtotal` | DECIMAL(10,2) | NO | 0 |
| `vat_amount` | DECIMAL(10,2) | YES | 0 |
| `discount_amount` | DECIMAL(10,2) | YES | 0 |
| `total_amount` | DECIMAL(10,2) | NO | 0 |
| `payment_status` | VARCHAR(32) | YES | 0 |
| `fulfillment_status` | VARCHAR(32) | YES | 0 |
| `payment_method` | VARCHAR(32) | YES | 0 |
| `source_domain` | VARCHAR(190) | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `attribution_json` | TEXT | YES | 0 |
| `customer_notes` | TEXT | YES | 0 |
| `idempotency_key` | VARCHAR(128) | YES | 0 |
| `created_at` | DATETIME | YES | 0 |
| `updated_at` | DATETIME | YES | 0 |

## `commerce_payment_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `payment_id` | VARCHAR(64) | YES | 0 |
| `event_type` | VARCHAR(128) | NO | 0 |
| `event_id` | VARCHAR(128) | NO | 0 |
| `payload_json` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_payments`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `order_id` | VARCHAR(64) | NO | 0 |
| `provider` | VARCHAR(32) | YES | 0 |
| `paypal_order_id` | VARCHAR(128) | YES | 0 |
| `paypal_capture_id` | VARCHAR(128) | YES | 0 |
| `status` | VARCHAR(32) | YES | 0 |
| `amount` | DECIMAL(10,2) | NO | 0 |
| `currency` | VARCHAR(8) | YES | 0 |
| `payer_email` | VARCHAR(190) | YES | 0 |
| `payer_id` | VARCHAR(64) | YES | 0 |
| `payer_name` | VARCHAR(190) | YES | 0 |
| `merchant_id` | VARCHAR(64) | YES | 0 |
| `raw_payload_json` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_products`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `business_id` | VARCHAR(64) | NO | 0 |
| `sku` | VARCHAR(64) | NO | 0 |
| `name` | VARCHAR(255) | NO | 0 |
| `description` | TEXT | YES | 0 |
| `product_type` | VARCHAR(32) | YES | 0 |
| `default_price` | DECIMAL(10,2) | NO | 0 |
| `currency` | VARCHAR(8) | YES | 0 |
| `vat_rate` | DECIMAL(5,2) | YES | 0 |
| `image_url` | VARCHAR(255) | YES | 0 |
| `fulfillment_type` | VARCHAR(64) | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `commerce_subscriptions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | VARCHAR(64) | YES | 1 |
| `business_id` | VARCHAR(64) | NO | 0 |
| `customer_id` | VARCHAR(64) | NO | 0 |
| `offer_id` | VARCHAR(64) | NO | 0 |
| `external_sub_id` | VARCHAR(128) | NO | 0 |
| `status` | VARCHAR(32) | YES | 0 |
| `plan_id` | VARCHAR(128) | YES | 0 |
| `current_period_start` | DATETIME | YES | 0 |
| `current_period_end` | DATETIME | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |
| `updated_at` | DATETIME | YES | 0 |

## `community_drop_recipients`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `drop_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `amount` | REAL | NO | 0 |
| `ledger_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `community_drop_rules`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `threshold_drx` | INTEGER | NO | 0 |
| `label` | TEXT | NO | 0 |
| `recipient_count` | INTEGER | NO | 0 |
| `max_drop_drx` | INTEGER | NO | 0 |
| `active` | INTEGER | YES | 0 |

## `community_drops`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `rule_threshold` | INTEGER | YES | 0 |
| `trigger_reserve` | REAL | YES | 0 |
| `community_pool_before` | REAL | YES | 0 |
| `total_distributed` | REAL | YES | 0 |
| `recipient_count` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `executed_at` | TEXT | YES | 0 |

## `community_insights`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `insight_type` | TEXT | NO | 0 |
| `period` | TEXT | YES | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `metric_key` | TEXT | YES | 0 |
| `metric_value` | REAL | YES | 0 |
| `dimensions_json` | TEXT | YES | 0 |
| `privacy_level` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `consent_log`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `consent_code` | TEXT | NO | 0 |
| `value` | INTEGER | NO | 0 |
| `version` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `cortex_edges`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `source_id` | TEXT | NO | 0 |
| `target_id` | TEXT | NO | 0 |
| `rel_type` | TEXT | NO | 0 |
| `properties` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `cortex_graph`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `node_id` | TEXT | NO | 0 |
| `node_type` | TEXT | NO | 0 |
| `properties` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `cortex_interactions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `user_id` | INTEGER | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `session_id` | TEXT | YES | 0 |
| `task` | TEXT | YES | 0 |
| `task_type` | TEXT | YES | 0 |
| `agent` | TEXT | YES | 0 |
| `input_data` | TEXT | YES | 0 |
| `result` | TEXT | YES | 0 |
| `feedback` | INTEGER | YES | 0 |
| `processed` | INTEGER | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `cortex_knowledge`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `concept` | TEXT | NO | 0 |
| `content` | TEXT | NO | 0 |
| `source_type` | TEXT | YES | 0 |
| `source_id` | TEXT | YES | 0 |
| `confidence` | REAL | YES | 0 |
| `created_at` | DATETIME | YES | 0 |

## `country_research_status`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `country` | TEXT | NO | 0 |
| `languages_used` | TEXT | YES | 0 |
| `queries_executed` | INTEGER | YES | 0 |
| `sources_checked` | INTEGER | YES | 0 |
| `verified_clubs` | INTEGER | YES | 0 |
| `probable_clubs` | INTEGER | YES | 0 |
| `historical_clubs` | INTEGER | YES | 0 |
| `completeness_score` | INTEGER | YES | 0 |
| `current_network_status` | TEXT | YES | 0 |
| `latest_source_date` | TEXT | YES | 0 |
| `last_researched_at` | TEXT | YES | 0 |
| `missing_data` | TEXT | YES | 0 |
| `next_queries` | TEXT | YES | 0 |

## `crm_club_contacts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `club_id` | INTEGER | YES | 0 |
| `entity_name` | TEXT | NO | 0 |
| `level` | TEXT | NO | 0 |
| `category` | TEXT | NO | 0 |
| `region` | TEXT | NO | 0 |
| `province` | TEXT | YES | 0 |
| `city` | TEXT | NO | 0 |
| `address` | TEXT | YES | 0 |
| `cap` | TEXT | YES | 0 |
| `primary_email` | TEXT | NO | 0 |
| `email_type` | TEXT | NO | 0 |
| `primary_phone` | TEXT | NO | 0 |
| `phone_secondary` | TEXT | YES | 0 |
| `website` | TEXT | YES | 0 |
| `servitore_insegnante` | TEXT | YES | 0 |
| `coordination_entity` | TEXT | YES | 0 |
| `families_count` | INTEGER | YES | 0 |
| `meeting_day` | TEXT | YES | 0 |
| `meeting_time` | TEXT | YES | 0 |
| `outreach_status` | TEXT | YES | 0 |
| `outreach_step` | INTEGER | YES | 0 |
| `last_contact_at` | TEXT | YES | 0 |
| `unsubscribe_token` | TEXT | NO | 0 |
| `unsubscribed_at` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `daily_access`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `access_date` | TEXT | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |

## `dao_comments`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `proposal_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `body` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `dao_proposal_votes`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `proposal_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `choice` | TEXT | NO | 0 |
| `weight` | REAL | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `dao_proposals`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `title` | TEXT | NO | 0 |
| `body` | TEXT | YES | 0 |
| `proposal_type` | TEXT | YES | 0 |
| `voting_method` | TEXT | YES | 0 |
| `quorum_pct` | REAL | YES | 0 |
| `threshold_pct` | REAL | YES | 0 |
| `status` | TEXT | YES | 0 |
| `opens_at` | TEXT | YES | 0 |
| `closes_at` | TEXT | YES | 0 |
| `created_by_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `dao_topics`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `title` | TEXT | NO | 0 |
| `body` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |
| `minimum_rank` | TEXT | YES | 0 |
| `allowed_roles_json` | TEXT | YES | 0 |
| `voting_enabled` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_by_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `dao_votes`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `topic_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `choice` | TEXT | NO | 0 |
| `created_at` | TEXT | YES | 0 |

## `data_conflicts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `field_name` | TEXT | NO | 0 |
| `value_a` | TEXT | YES | 0 |
| `source_a_sic_id` | TEXT | YES | 0 |
| `value_b` | TEXT | YES | 0 |
| `source_b_sic_id` | TEXT | YES | 0 |
| `resolution_status` | TEXT | YES | 0 |
| `resolved_value` | TEXT | YES | 0 |
| `resolved_by_sic_id` | TEXT | YES | 0 |
| `resolved_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `dependex_world_edges`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `edge_sic_id` | TEXT | NO | 0 |
| `source_sic_id` | TEXT | NO | 0 |
| `target_sic_id` | TEXT | NO | 0 |
| `relation` | TEXT | NO | 0 |
| `confidence` | INTEGER | YES | 0 |
| `source_reference` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `dependex_world_registry`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_name` | TEXT | NO | 0 |
| `original_type` | TEXT | YES | 0 |
| `network_level` | TEXT | NO | 0 |
| `network_rank` | INTEGER | NO | 0 |
| `rank_color` | TEXT | YES | 0 |
| `continent` | TEXT | YES | 0 |
| `country` | TEXT | YES | 0 |
| `region` | TEXT | YES | 0 |
| `province` | TEXT | YES | 0 |
| `city` | TEXT | YES | 0 |
| `address` | TEXT | YES | 0 |
| `latitude` | REAL | YES | 0 |
| `longitude` | REAL | YES | 0 |
| `geo_accuracy` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `parent_sic_id` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `source_type` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `meeting` | TEXT | YES | 0 |
| `public_contact` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `direct_children` | INTEGER | YES | 0 |
| `network_descendants` | INTEGER | YES | 0 |
| `public_data_score` | INTEGER | YES | 0 |
| `is_synthetic` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |
| `website` | TEXT | YES | 0 |
| `phone` | TEXT | YES | 0 |
| `email` | TEXT | YES | 0 |
| `geo_confidence` | INTEGER | YES | 0 |
| `geocoded_from` | TEXT | YES | 0 |
| `geocoded_at` | TEXT | YES | 0 |
| `original_name` | TEXT | YES | 0 |
| `translated_name` | TEXT | YES | 0 |
| `original_hierarchy` | TEXT | YES | 0 |
| `postal_code` | TEXT | YES | 0 |
| `facebook` | TEXT | YES | 0 |
| `instagram` | TEXT | YES | 0 |
| `youtube` | TEXT | YES | 0 |
| `linkedin` | TEXT | YES | 0 |
| `x_twitter` | TEXT | YES | 0 |
| `founding_year` | INTEGER | YES | 0 |
| `closure_year` | INTEGER | YES | 0 |
| `reopening_year` | INTEGER | YES | 0 |
| `hudolin_confirmed` | INTEGER | YES | 0 |
| `confidence_score` | INTEGER | YES | 0 |
| `last_verified` | TEXT | YES | 0 |
| `geo_provider` | TEXT | YES | 0 |
| `geo_provider_ref` | TEXT | YES | 0 |
| `territorial_association` | TEXT | YES | 0 |
| `regional_federation` | TEXT | YES | 0 |
| `national_federation` | TEXT | YES | 0 |
| `families_count` | INTEGER | YES | 0 |

## `document_templates`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `code` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `category` | TEXT | YES | 0 |
| `schema_json` | TEXT | YES | 0 |
| `html_template` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `documents`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `owner_scope_sic_id` | TEXT | YES | 0 |
| `doc_type` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `file_path` | TEXT | YES | 0 |
| `hash_sha256` | TEXT | YES | 0 |
| `blockchain_tx` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `donations`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `project_sic_id` | TEXT | YES | 0 |
| `donor_user_sic_id` | TEXT | YES | 0 |
| `amount_eur` | REAL | YES | 0 |
| `anonymous` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `payment_reference` | TEXT | YES | 0 |

## `drx_entitlements`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `amount` | REAL | NO | 0 |
| `source_type` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `rank_eligible` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `accrued_at` | TEXT | YES | 0 |
| `claim_deadline` | TEXT | YES | 0 |
| `claimed_at` | TEXT | YES | 0 |
| `reserved_at` | TEXT | YES | 0 |
| `ledger_sic_id` | TEXT | YES | 0 |

## `drx_idempotency`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `idempotency_key` | TEXT | NO | 0 |
| `ledger_sic_id` | TEXT | NO | 0 |
| `created_at` | TEXT | YES | 0 |

## `drx_ledger`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `club_sic_id` | TEXT | YES | 0 |
| `amount` | REAL | NO | 0 |
| `source_type` | TEXT | NO | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `rank_eligible` | INTEGER | NO | 0 |
| `status` | TEXT | YES | 0 |
| `blockchain_tx` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `idempotency_key` | TEXT | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |
| `source_date` | TEXT | YES | 0 |

## `drx_settings`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `key` | TEXT | YES | 1 |
| `value` | TEXT | NO | 0 |
| `updated_at` | TEXT | YES | 0 |

## `drx_vault_ledger`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `pool` | TEXT | NO | 0 |
| `direction` | TEXT | NO | 0 |
| `amount` | REAL | NO | 0 |
| `source_type` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `metadata_json` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `email_preferences`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `user_sic_id` | TEXT | YES | 1 |
| `transactional` | INTEGER | YES | 0 |
| `club` | INTEGER | YES | 0 |
| `academy` | INTEGER | YES | 0 |
| `events` | INTEGER | YES | 0 |
| `dao` | INTEGER | YES | 0 |
| `marketing` | INTEGER | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `email_queue`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `template_code` | TEXT | YES | 0 |
| `to_email` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `subject` | TEXT | NO | 0 |
| `html_body` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `attempts` | INTEGER | YES | 0 |
| `scheduled_at` | TEXT | YES | 0 |
| `sent_at` | TEXT | YES | 0 |
| `last_error` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `email_templates`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `code` | TEXT | NO | 0 |
| `subject` | TEXT | NO | 0 |
| `html_body` | TEXT | NO | 0 |
| `active` | INTEGER | YES | 0 |

## `email_tracking_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `send_id` | TEXT | NO | 0 |
| `event_type` | TEXT | NO | 0 |
| `target_url` | TEXT | YES | 0 |
| `tag` | TEXT | YES | 0 |
| `ip_hash` | TEXT | NO | 0 |
| `user_agent_short` | TEXT | YES | 0 |
| `created_at` | INTEGER | NO | 0 |
| `created_iso` | TEXT | NO | 0 |

## `entity_aliases`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `alias` | TEXT | NO | 0 |
| `language` | TEXT | YES | 0 |
| `alias_type` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |

## `entity_field_conflicts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `field_name` | TEXT | NO | 0 |
| `value_a` | TEXT | YES | 0 |
| `source_a` | TEXT | YES | 0 |
| `value_b` | TEXT | YES | 0 |
| `source_b` | TEXT | YES | 0 |
| `resolution_status` | TEXT | YES | 0 |
| `resolved_value` | TEXT | YES | 0 |
| `resolved_at` | TEXT | YES | 0 |

## `entity_source_links`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `entity_sic_id` | TEXT | NO | 0 |
| `source_sic_id` | TEXT | NO | 0 |
| `relation` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `field_name` | TEXT | YES | 0 |
| `value_text` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `entity_status_history`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `status` | TEXT | NO | 0 |
| `valid_from` | TEXT | YES | 0 |
| `valid_to` | TEXT | YES | 0 |
| `first_seen` | TEXT | YES | 0 |
| `last_seen` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `notes` | TEXT | YES | 0 |

## `event_bookings`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `event_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `full_name` | TEXT | NO | 0 |
| `email` | TEXT | NO | 0 |
| `phone` | TEXT | NO | 0 |
| `role_type` | TEXT | YES | 0 |
| `dietary_notes` | TEXT | YES | 0 |
| `num_seats` | INTEGER | YES | 0 |
| `total_amount` | REAL | YES | 0 |
| `payment_method` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `ip_address` | TEXT | YES | 0 |
| `created_at` | DATETIME | YES | 0 |
| `first_name` | TEXT | YES | 0 |
| `last_name` | TEXT | YES | 0 |
| `payment_status` | TEXT | YES | 0 |
| `payment_tx_id` | TEXT | YES | 0 |
| `updated_at` | DATETIME | YES | 0 |

## `event_registrations`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `event_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `checked_in_at` | TEXT | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `verified_by_sic_id` | TEXT | YES | 0 |
| `checkin_code` | TEXT | YES | 0 |

## `events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `owner_scope_sic_id` | TEXT | YES | 0 |
| `type` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `description` | TEXT | YES | 0 |
| `starts_at` | TEXT | YES | 0 |
| `ends_at` | TEXT | YES | 0 |
| `venue` | TEXT | YES | 0 |
| `comune` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |
| `rank_required` | TEXT | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |
| `capacity` | INTEGER | YES | 0 |
| `price_eur` | REAL | YES | 0 |
| `online_url` | TEXT | YES | 0 |
| `address` | TEXT | YES | 0 |
| `latitude` | REAL | YES | 0 |
| `longitude` | REAL | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `image_url` | TEXT | YES | 0 |
| `organizer` | TEXT | YES | 0 |
| `trainer` | TEXT | YES | 0 |
| `registration_deadline` | TEXT | YES | 0 |

## `families`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `name` | TEXT | YES | 0 |
| `club_sic_id` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `family_members`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `family_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `relation_type` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `field_confidence`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `entity_sic_id` | TEXT | NO | 0 |
| `field_name` | TEXT | NO | 0 |
| `confidence` | INTEGER | NO | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `verified_at` | TEXT | YES | 0 |

## `form_submissions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `form_sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `payload_json` | TEXT | YES | 0 |
| `document_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `form_templates`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `code` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `schema_json` | TEXT | YES | 0 |
| `output_type` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `funnel_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `session_token` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `page` | TEXT | NO | 0 |
| `funnel_stage` | TEXT | NO | 0 |
| `action` | TEXT | NO | 0 |
| `event_data` | TEXT | YES | 0 |
| `ip_hash` | TEXT | NO | 0 |
| `user_agent_short` | TEXT | YES | 0 |
| `created_at` | INTEGER | NO | 0 |
| `created_iso` | TEXT | NO | 0 |

## `funnel_intent_profiles`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `session_token` | TEXT | YES | 1 |
| `user_sic_id` | TEXT | YES | 0 |
| `dominant_intent` | TEXT | YES | 0 |
| `current_stage` | TEXT | YES | 0 |
| `porta_selected` | TEXT | YES | 0 |
| `total_events` | INTEGER | YES | 0 |
| `dwell_seconds` | INTEGER | YES | 0 |
| `max_scroll_depth` | INTEGER | YES | 0 |
| `opened_faq_count` | INTEGER | YES | 0 |
| `searched_clubs_count` | INTEGER | YES | 0 |
| `gamification_played` | INTEGER | YES | 0 |
| `contact_attempted` | INTEGER | YES | 0 |
| `contact_completed` | INTEGER | YES | 0 |
| `first_seen` | INTEGER | NO | 0 |
| `last_seen` | INTEGER | NO | 0 |
| `ip_hash` | TEXT | NO | 0 |

## `generated_documents`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `template_sic_id` | TEXT | YES | 0 |
| `owner_sic_id` | TEXT | YES | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `payload_json` | TEXT | YES | 0 |
| `html_path` | TEXT | YES | 0 |
| `pdf_path` | TEXT | YES | 0 |
| `sha256` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `geo_enrichment_queue`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `query` | TEXT | NO | 0 |
| `priority` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `attempts` | INTEGER | YES | 0 |
| `last_error` | TEXT | YES | 0 |
| `geocoded_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `geo_history`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `latitude` | REAL | YES | 0 |
| `longitude` | REAL | YES | 0 |
| `geo_accuracy` | TEXT | YES | 0 |
| `geocoded_from` | TEXT | YES | 0 |
| `geocoder` | TEXT | YES | 0 |
| `geo_confidence` | REAL | YES | 0 |
| `valid_from` | TEXT | YES | 0 |
| `valid_to` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |

## `geo_observations_v2`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `latitude` | REAL | YES | 0 |
| `longitude` | REAL | YES | 0 |
| `geo_accuracy` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `geocoded_from` | TEXT | YES | 0 |
| `provider` | TEXT | YES | 0 |
| `provider_ref` | TEXT | YES | 0 |
| `raw_json` | TEXT | YES | 0 |
| `observed_at` | TEXT | YES | 0 |
| `selected` | INTEGER | YES | 0 |

## `geocode_queue`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `entity_sic_id` | TEXT | NO | 0 |
| `query_text` | TEXT | NO | 0 |
| `status` | TEXT | YES | 0 |
| `attempts` | INTEGER | YES | 0 |
| `provider` | TEXT | YES | 0 |
| `result_json` | TEXT | YES | 0 |
| `last_error` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |
| `accuracy_hint` | TEXT | YES | 0 |
| `fallback_level` | INTEGER | YES | 0 |
| `provider_ref` | TEXT | YES | 0 |

## `global_network_entities`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `level` | TEXT | YES | 0 |
| `country` | TEXT | YES | 0 |
| `entity_name` | TEXT | YES | 0 |
| `region` | TEXT | YES | 0 |
| `city` | TEXT | YES | 0 |
| `address` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `parent_name` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `source_type` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `meeting` | TEXT | YES | 0 |
| `public_contact` | TEXT | YES | 0 |
| `last_checked` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `hudolin_rules`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `code` | TEXT | NO | 0 |
| `title` | TEXT | NO | 0 |
| `description` | TEXT | YES | 0 |
| `rule_type` | TEXT | NO | 0 |
| `config_json` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `version` | INTEGER | YES | 0 |
| `updated_at` | TEXT | YES | 0 |
| `authority` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `policy_class` | TEXT | YES | 0 |
| `effective_note` | TEXT | YES | 0 |

## `integration_adapters`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `code` | TEXT | YES | 0 |
| `label` | TEXT | YES | 0 |
| `interface_version` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `config_json` | TEXT | YES | 0 |

## `journal_entries`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `entry_date` | TEXT | NO | 0 |
| `mood` | INTEGER | YES | 0 |
| `gratitude` | TEXT | YES | 0 |
| `note` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `lifestyle_dimensions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `code` | TEXT | YES | 0 |
| `label` | TEXT | YES | 0 |
| `icon` | TEXT | YES | 0 |

## `lifestyle_scores`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `dimension_code` | TEXT | YES | 0 |
| `score` | REAL | YES | 0 |
| `source` | TEXT | YES | 0 |
| `recorded_at` | TEXT | YES | 0 |

## `meta`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `key` | TEXT | YES | 1 |
| `value` | TEXT | YES | 0 |

## `mission_completions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `mission_sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `club_sic_id` | TEXT | YES | 0 |
| `verified` | INTEGER | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |
| `completed_at` | TEXT | YES | 0 |
| `completion_key` | TEXT | YES | 0 |

## `missions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `category` | TEXT | YES | 0 |
| `description` | TEXT | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `rank_required` | TEXT | YES | 0 |
| `repeat_rule` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `module_registry`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `code` | TEXT | YES | 1 |
| `label` | TEXT | YES | 0 |
| `source_path` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `version` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `ncke_chunks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `document_sic_id` | TEXT | NO | 0 |
| `section` | TEXT | YES | 0 |
| `content` | TEXT | NO | 0 |
| `token_estimate` | INTEGER | YES | 0 |
| `freshness` | TEXT | YES | 0 |
| `confidence` | REAL | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `ncke_chunks_fts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `chunk_sic_id` |  | YES | 0 |
| `section` |  | YES | 0 |
| `content` |  | YES | 0 |

## `ncke_chunks_fts_config`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `k` |  | NO | 1 |
| `v` |  | YES | 0 |

## `ncke_chunks_fts_content`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `c0` |  | YES | 0 |
| `c1` |  | YES | 0 |
| `c2` |  | YES | 0 |

## `ncke_chunks_fts_data`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `block` | BLOB | YES | 0 |

## `ncke_chunks_fts_docsize`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sz` | BLOB | YES | 0 |

## `ncke_chunks_fts_idx`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `segid` |  | NO | 1 |
| `term` |  | NO | 2 |
| `pgno` |  | YES | 0 |

## `ncke_documents`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `source_path` | TEXT | NO | 0 |
| `title` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `content_hash` | TEXT | NO | 0 |
| `version` | INTEGER | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `ncke_feedback`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `query_sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `rating` | INTEGER | YES | 0 |
| `comment` | TEXT | YES | 0 |
| `corrected_answer` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `ncke_health_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `component` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `details_json` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `ncke_human_review`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `query_sic_id` | TEXT | YES | 0 |
| `reason` | TEXT | YES | 0 |
| `priority` | INTEGER | YES | 0 |
| `assigned_role` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `ncke_queries`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `query_text` | TEXT | NO | 0 |
| `intent` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `complexity` | INTEGER | YES | 0 |
| `strategies_json` | TEXT | YES | 0 |
| `confidence` | REAL | YES | 0 |
| `provider` | TEXT | YES | 0 |
| `cost_usd` | REAL | YES | 0 |
| `latency_ms` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `ncke_runtime_status`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `status` | TEXT | YES | 0 |
| `mode` | TEXT | YES | 0 |
| `last_bootstrap_at` | TEXT | YES | 0 |
| `details_json` | TEXT | YES | 0 |

## `ncke_sources`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `title` | TEXT | YES | 0 |
| `source_type` | TEXT | YES | 0 |
| `uri` | TEXT | YES | 0 |
| `publisher` | TEXT | YES | 0 |
| `source_date` | TEXT | YES | 0 |
| `last_checked` | TEXT | YES | 0 |
| `confidence` | REAL | YES | 0 |
| `content_hash` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `network_entities`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `level` | TEXT | NO | 0 |
| `entity_name` | TEXT | NO | 0 |
| `country` | TEXT | YES | 0 |
| `region` | TEXT | YES | 0 |
| `province` | TEXT | YES | 0 |
| `comune` | TEXT | YES | 0 |
| `address` | TEXT | YES | 0 |
| `cap` | TEXT | YES | 0 |
| `phone` | TEXT | YES | 0 |
| `email` | TEXT | YES | 0 |
| `website` | TEXT | YES | 0 |
| `parent_name` | TEXT | YES | 0 |
| `parent_sic_id` | TEXT | YES | 0 |
| `meeting_day` | TEXT | YES | 0 |
| `meeting_time` | TEXT | YES | 0 |
| `servitore_insegnante` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `source_type` | TEXT | YES | 0 |
| `source_date` | TEXT | YES | 0 |
| `verification_status` | TEXT | YES | 0 |
| `active_status` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |
| `latitude` | REAL | YES | 0 |
| `longitude` | REAL | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |
| `site_scope` | TEXT | YES | 0 |
| `map_enabled` | INTEGER | YES | 0 |
| `network_enabled` | INTEGER | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |
| `geo_accuracy` | TEXT | YES | 0 |
| `geocoded_from` | TEXT | YES | 0 |
| `geocoder` | TEXT | YES | 0 |
| `geocoded_at` | TEXT | YES | 0 |
| `geo_confidence` | REAL | YES | 0 |
| `continent` | TEXT | YES | 0 |
| `original_entity_type` | TEXT | YES | 0 |
| `original_local_name` | TEXT | YES | 0 |
| `original_hierarchy` | TEXT | YES | 0 |

## `notifications`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `channel` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `body` | TEXT | YES | 0 |
| `target_url` | TEXT | YES | 0 |
| `read_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `osint_changes`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | YES | 0 |
| `change_type` | TEXT | NO | 0 |
| `field_name` | TEXT | YES | 0 |
| `old_value` | TEXT | YES | 0 |
| `new_value` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `detected_at` | TEXT | YES | 0 |

## `osint_conflicts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `field_name` | TEXT | NO | 0 |
| `value_a` | TEXT | YES | 0 |
| `source_a_sic_id` | TEXT | YES | 0 |
| `value_b` | TEXT | YES | 0 |
| `source_b_sic_id` | TEXT | YES | 0 |
| `resolution_status` | TEXT | YES | 0 |
| `selected_value` | TEXT | YES | 0 |
| `resolved_by_sic_id` | TEXT | YES | 0 |
| `resolved_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `osint_entities_aliases`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `alias` | TEXT | NO | 0 |
| `language` | TEXT | YES | 0 |
| `alias_type` | TEXT | YES | 0 |
| `source_url` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `osint_entity_sources`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | NO | 0 |
| `source_sic_id` | TEXT | NO | 0 |
| `is_primary` | INTEGER | YES | 0 |
| `relevant_summary` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `osint_sources`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `url` | TEXT | NO | 0 |
| `domain` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `publisher` | TEXT | YES | 0 |
| `source_level` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `publication_date` | TEXT | YES | 0 |
| `retrieved_at` | TEXT | YES | 0 |
| `last_modified` | TEXT | YES | 0 |
| `content_hash` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `notes` | TEXT | YES | 0 |

## `privacy_consents`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `consent_code` | TEXT | NO | 0 |
| `version` | TEXT | NO | 0 |
| `granted` | INTEGER | NO | 0 |
| `granted_at` | TEXT | YES | 0 |
| `revoked_at` | TEXT | YES | 0 |

## `professionals`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `profession` | TEXT | YES | 0 |
| `register_name` | TEXT | YES | 0 |
| `register_number` | TEXT | YES | 0 |
| `verification_status` | TEXT | YES | 0 |
| `organization` | TEXT | YES | 0 |
| `territory` | TEXT | YES | 0 |
| `public_profile` | INTEGER | YES | 0 |

## `project_updates`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `project_sic_id` | TEXT | NO | 0 |
| `title` | TEXT | YES | 0 |
| `body` | TEXT | YES | 0 |
| `amount_spent` | REAL | YES | 0 |
| `created_by_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `projects`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `owner_scope_sic_id` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `category` | TEXT | YES | 0 |
| `description` | TEXT | YES | 0 |
| `goal_eur` | REAL | YES | 0 |
| `raised_eur` | REAL | YES | 0 |
| `status` | TEXT | YES | 0 |
| `starts_at` | TEXT | YES | 0 |
| `ends_at` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `rank_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `owner_type` | TEXT | NO | 0 |
| `owner_sic_id` | TEXT | NO | 0 |
| `old_rank` | TEXT | YES | 0 |
| `new_rank` | TEXT | YES | 0 |
| `qualifying_drx` | REAL | YES | 0 |
| `source` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `rank_unlocks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `rank_name` | TEXT | NO | 0 |
| `unlock_code` | TEXT | NO | 0 |
| `label` | TEXT | NO | 0 |
| `description` | TEXT | YES | 0 |
| `audience` | TEXT | YES | 0 |

## `ranks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `rank_order` | INTEGER | NO | 0 |
| `name` | TEXT | NO | 0 |
| `threshold_drx` | INTEGER | NO | 0 |
| `unlocks_json` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `registration_events`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `email_hash` | TEXT | YES | 0 |
| `status` | TEXT | NO | 0 |
| `reason` | TEXT | YES | 0 |
| `ip_hash` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `research_changesets`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `run_sic_id` | TEXT | YES | 0 |
| `entity_sic_id` | TEXT | YES | 0 |
| `change_type` | TEXT | NO | 0 |
| `before_json` | TEXT | YES | 0 |
| `after_json` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `research_runs`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `country` | TEXT | YES | 0 |
| `started_at` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |
| `queries_count` | INTEGER | YES | 0 |
| `new_entities` | INTEGER | YES | 0 |
| `updated_entities` | INTEGER | YES | 0 |
| `new_sources` | INTEGER | YES | 0 |
| `notes` | TEXT | YES | 0 |

## `research_sources`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_sic_id` | TEXT | YES | 0 |
| `source_url` | TEXT | NO | 0 |
| `source_domain` | TEXT | YES | 0 |
| `source_title` | TEXT | YES | 0 |
| `publisher` | TEXT | YES | 0 |
| `source_date` | TEXT | YES | 0 |
| `retrieved_at` | TEXT | YES | 0 |
| `language` | TEXT | YES | 0 |
| `source_level` | TEXT | YES | 0 |
| `source_status` | TEXT | YES | 0 |
| `content_hash` | TEXT | YES | 0 |
| `summary` | TEXT | YES | 0 |

## `research_tasks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `country` | TEXT | YES | 0 |
| `entity_sic_id` | TEXT | YES | 0 |
| `priority` | TEXT | YES | 0 |
| `task_type` | TEXT | NO | 0 |
| `query_text` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `reason` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `research_terms`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `term` | TEXT | NO | 0 |
| `language` | TEXT | YES | 0 |
| `country` | TEXT | YES | 0 |
| `canonical_term` | TEXT | YES | 0 |
| `term_type` | TEXT | YES | 0 |
| `source_sic_id` | TEXT | YES | 0 |
| `confidence` | INTEGER | YES | 0 |
| `active` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `roles`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `code` | TEXT | NO | 0 |
| `label` | TEXT | NO | 0 |
| `level` | INTEGER | NO | 0 |

## `security_rate_limits`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `bucket` | TEXT | YES | 1 |
| `hits` | INTEGER | YES | 0 |
| `window_start` | INTEGER | NO | 0 |
| `blocked_until` | INTEGER | YES | 0 |

## `settings`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `key` | TEXT | YES | 1 |
| `value` | TEXT | YES | 0 |

## `sic_registry`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `entity_type` | TEXT | NO | 0 |
| `entity_id` | INTEGER | YES | 0 |
| `parent_sic_id` | TEXT | YES | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `status` | TEXT | NO | 0 |
| `created_at` | TEXT | NO | 0 |
| `metadata_json` | TEXT | YES | 0 |

## `site_live_sessions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `session_token` | TEXT | YES | 1 |
| `user_sic_id` | TEXT | YES | 0 |
| `ip_hash` | TEXT | NO | 0 |
| `last_seen` | INTEGER | NO | 0 |
| `created_at` | INTEGER | NO | 0 |

## `sobriety_accrual_state`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `user_sic_id` | TEXT | YES | 1 |
| `last_awarded_date` | TEXT | YES | 0 |
| `total_awarded_days` | INTEGER | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `sobriety_milestones`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `days` | INTEGER | YES | 0 |
| `title` | TEXT | YES | 0 |
| `drx_reward` | INTEGER | YES | 0 |
| `badge_code` | TEXT | YES | 0 |
| `nft_eligible` | INTEGER | YES | 0 |
| `certificate_enabled` | INTEGER | YES | 0 |

## `sobriety_records`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `start_date` | TEXT | YES | 0 |
| `current_streak` | INTEGER | YES | 0 |
| `lifetime_days` | INTEGER | YES | 0 |
| `opted_in_leaderboard` | INTEGER | YES | 0 |
| `display_mode` | TEXT | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `system_watchdog_logs`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `component` | TEXT | NO | 0 |
| `severity` | TEXT | YES | 0 |
| `status` | TEXT | NO | 0 |
| `message` | TEXT | NO | 0 |
| `metrics_json` | TEXT | YES | 0 |
| `created_at` | INTEGER | NO | 0 |
| `created_iso` | TEXT | NO | 0 |

## `treasury_accounts`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `owner_scope_sic_id` | TEXT | YES | 0 |
| `currency` | TEXT | YES | 0 |
| `account_type` | TEXT | YES | 0 |
| `label` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

## `treasury_transactions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `account_sic_id` | TEXT | YES | 0 |
| `direction` | TEXT | YES | 0 |
| `amount` | REAL | YES | 0 |
| `category` | TEXT | YES | 0 |
| `description` | TEXT | YES | 0 |
| `document_sic_id` | TEXT | YES | 0 |
| `approved_by_sic_id` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `trusted_devices`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `token_hash` | TEXT | NO | 0 |
| `label` | TEXT | YES | 0 |
| `last_seen_at` | TEXT | YES | 0 |
| `expires_at` | TEXT | YES | 0 |
| `revoked_at` | TEXT | YES | 0 |

## `user_consents`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `user_sic_id` | TEXT | NO | 0 |
| `consent_type` | TEXT | NO | 0 |
| `version` | TEXT | NO | 0 |
| `accepted` | INTEGER | NO | 0 |
| `ip_hash` | TEXT | YES | 0 |
| `user_agent_hash` | TEXT | YES | 0 |
| `accepted_at` | TEXT | YES | 0 |

## `user_preferences`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `user_sic_id` | TEXT | YES | 1 |
| `language` | TEXT | YES | 0 |
| `sobriety_leaderboard` | INTEGER | YES | 0 |
| `leaderboard_display` | TEXT | YES | 0 |
| `email_notifications` | INTEGER | YES | 0 |
| `push_notifications` | INTEGER | YES | 0 |
| `reduced_motion` | INTEGER | YES | 0 |
| `updated_at` | TEXT | YES | 0 |

## `user_roles`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `user_sic_id` | TEXT | NO | 0 |
| `role_code` | TEXT | NO | 0 |
| `scope_sic_id` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `valid_from` | TEXT | YES | 0 |
| `valid_to` | TEXT | YES | 0 |

## `user_support_areas`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `area_code` | TEXT | YES | 0 |
| `source` | TEXT | YES | 0 |
| `visibility` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `users`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `email` | TEXT | NO | 0 |
| `display_name` | TEXT | NO | 0 |
| `password_hash` | TEXT | NO | 0 |
| `status` | TEXT | NO | 0 |
| `rank_name` | TEXT | NO | 0 |
| `drx_balance` | REAL | NO | 0 |
| `sobriety_start_date` | TEXT | YES | 0 |
| `recovery_code_hash` | TEXT | YES | 0 |
| `recovery_code_changed_at` | TEXT | YES | 0 |
| `last_login_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |
| `legacy_sic_id` | TEXT | YES | 0 |

## `volunteer_actions`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `project_sic_id` | TEXT | YES | 0 |
| `user_sic_id` | TEXT | YES | 0 |
| `hours` | REAL | YES | 0 |
| `description` | TEXT | YES | 0 |
| `verified` | INTEGER | YES | 0 |
| `drx_awarded` | INTEGER | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `workflow_instances`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `workflow_sic_id` | TEXT | YES | 0 |
| `owner_scope_sic_id` | TEXT | YES | 0 |
| `current_step` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `context_json` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `workflow_tasks`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | NO | 0 |
| `workflow_instance_sic_id` | TEXT | YES | 0 |
| `title` | TEXT | NO | 0 |
| `assigned_user_sic_id` | TEXT | YES | 0 |
| `assigned_role` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |
| `due_at` | TEXT | YES | 0 |
| `completed_at` | TEXT | YES | 0 |
| `created_at` | TEXT | YES | 0 |

## `workflows`

| Campo | Tipo | Null | PK |
|---|---|---:|---:|
| `id` | INTEGER | YES | 1 |
| `sic_id` | TEXT | YES | 0 |
| `code` | TEXT | YES | 0 |
| `title` | TEXT | YES | 0 |
| `owner_scope` | TEXT | YES | 0 |
| `config_json` | TEXT | YES | 0 |
| `status` | TEXT | YES | 0 |

