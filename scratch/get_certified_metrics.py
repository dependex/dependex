import sqlite3
import json

con = sqlite3.connect('data/acat_community.sqlite')
cur = con.cursor()

data = {}

# 1. cat_clubs_italy
data['cat_clubs_italy_total'] = cur.execute('SELECT count(*) FROM cat_clubs_italy').fetchone()[0]
data['cat_clubs_italy_local_clubs'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE level='LOCAL_CLUB'").fetchone()[0]
data['cat_clubs_italy_coordination_entities'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE level!='LOCAL_CLUB'").fetchone()[0]
data['cat_clubs_italy_levels'] = dict(cur.execute("SELECT level, count(*) FROM cat_clubs_italy GROUP BY level").fetchall())
data['cat_clubs_italy_meeting_day_filled'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE meeting_day IS NOT NULL AND meeting_day != '' AND meeting_day != 'Da concordare'").fetchone()[0]
data['cat_clubs_italy_meeting_day_tbd'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE meeting_day IS NULL OR meeting_day = '' OR meeting_day = 'Da concordare'").fetchone()[0]
data['cat_clubs_italy_phone_filled'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE phone IS NOT NULL AND phone != ''").fetchone()[0]
data['cat_clubs_italy_email_filled'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE email IS NOT NULL AND email != ''").fetchone()[0]
data['cat_clubs_italy_address_filled'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE address IS NOT NULL AND address != ''").fetchone()[0]
data['cat_clubs_italy_coords_valid'] = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE latitude != 0.0 AND longitude != 0.0").fetchone()[0]
data['cat_clubs_italy_regions_count'] = cur.execute("SELECT count(DISTINCT region) FROM cat_clubs_italy WHERE region IS NOT NULL AND region != ''").fetchone()[0]
data['cat_clubs_italy_provinces_count'] = cur.execute("SELECT count(DISTINCT province) FROM cat_clubs_italy WHERE province IS NOT NULL AND province != ''").fetchone()[0]
data['cat_clubs_italy_cities_count'] = cur.execute("SELECT count(DISTINCT city) FROM cat_clubs_italy WHERE city IS NOT NULL AND city != ''").fetchone()[0]

# 2. dependex_world_registry
data['world_registry_total'] = cur.execute('SELECT count(*) FROM dependex_world_registry').fetchone()[0]
data['world_registry_italy_total'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country='Italy'").fetchone()[0]
data['world_registry_foreign_total'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country!='Italy'").fetchone()[0]
data['world_registry_countries_count'] = cur.execute("SELECT count(DISTINCT country) FROM dependex_world_registry WHERE country IS NOT NULL AND country != ''").fetchone()[0]
data['world_registry_italy_local_clubs'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country='Italy' AND network_level='LOCAL_CLUB'").fetchone()[0]
data['world_registry_italy_acat'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country='Italy' AND network_level IN ('TERRITORIAL','PROVINCIAL','TERRITORIAL_ASSOCIATION','TERRITORIAL_ACAT')").fetchone()[0]
data['world_registry_italy_arcat'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country='Italy' AND network_level='REGIONAL'").fetchone()[0]
data['world_registry_italy_aicat'] = cur.execute("SELECT count(*) FROM dependex_world_registry WHERE country='Italy' AND network_level='NATIONAL'").fetchone()[0]
data['world_registry_italy_local_families'] = cur.execute("SELECT SUM(families_count) FROM dependex_world_registry WHERE country='Italy' AND network_level='LOCAL_CLUB'").fetchone()[0]
data['world_registry_all_families_raw_sum'] = cur.execute("SELECT SUM(families_count) FROM dependex_world_registry").fetchone()[0]

# 3. crm_club_contacts
data['crm_contacts_total'] = cur.execute('SELECT count(*) FROM crm_club_contacts').fetchone()[0]
data['crm_contacts_direct_emails'] = cur.execute("SELECT count(*) FROM crm_club_contacts WHERE email_type='DIRECT'").fetchone()[0]
data['crm_contacts_coordination_emails'] = cur.execute("SELECT count(*) FROM crm_club_contacts WHERE email_type='COORDINATION_INHERITED'").fetchone()[0]
data['crm_contacts_phones_filled'] = cur.execute("SELECT count(*) FROM crm_club_contacts WHERE primary_phone IS NOT NULL AND primary_phone != ''").fetchone()[0]
data['crm_contacts_unique_tokens'] = cur.execute("SELECT count(DISTINCT unsubscribe_token) FROM crm_club_contacts").fetchone()[0]

# 4. network_entities (legacy table)
data['network_entities_total'] = cur.execute('SELECT count(*) FROM network_entities').fetchone()[0]
data['network_entities_italy'] = cur.execute("SELECT count(*) FROM network_entities WHERE country IN ('IT', 'Italy')").fetchone()[0]
data['network_entities_italy_clubs'] = cur.execute("SELECT count(*) FROM network_entities WHERE country IN ('IT', 'Italy') AND level='CLUB'").fetchone()[0]
data['network_entities_foreign'] = cur.execute("SELECT count(*) FROM network_entities WHERE country NOT IN ('IT', 'Italy')").fetchone()[0]

# 5. events & bookings
data['events_total'] = cur.execute('SELECT count(*) FROM events').fetchone()[0]
data['event_bookings_total'] = cur.execute('SELECT count(*) FROM event_bookings').fetchone()[0]

print(json.dumps(data, indent=2))
