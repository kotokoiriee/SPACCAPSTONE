# SPAC Developer Notes

## NEVER use COUNT(DISTINCT) across multiple LEFT JOINs
BAD - 18,363ms with just 2 barangays:
SELECT b.name, COUNT(DISTINCT r.resident_id), COUNT(DISTINCT f.id)
FROM barangays b
LEFT JOIN residents r ON r.barangay_id = b.barangay_id
LEFT JOIN families  f ON f.barangay_id = b.barangay_id
GROUP BY b.barangay_id

GOOD - 4ms at any scale:
1. SELECT barangay_id, name FROM barangays
2. SELECT barangay_id, COUNT(*) FROM residents GROUP BY barangay_id
3. SELECT barangay_id, COUNT(*) FROM families GROUP BY barangay_id
Then merge in PHP using $tmp[$row["barangay_id"]]

## NEVER use SHOW COLUMNS on every page load
BAD:  $conn->query("SHOW COLUMNS FROM barangays LIKE 'status'")
GOOD: Use column_exists() helper with static cache

## Files fixed May 2025
- dashboards/superadmin/index.php line 327 (all_brgy_list)
- dashboards/superadmin/index.php line 370 (report_barangays)
- dashboards/superadmin/get_live_stats.php (brgy JOIN queries)

## Indexes added
ALTER TABLE residents ADD INDEX idx_brgy_active (barangay_id, is_active);
ALTER TABLE residents ADD INDEX idx_brgy_rid (barangay_id, is_active, resident_id);
ALTER TABLE families ADD INDEX idx_brgy_id (barangay_id);
ALTER TABLE families ADD INDEX idx_brgy_fid (barangay_id, family_id);

## San Pedro has 54 barangays total
New query pattern handles all 54 + 500k residents in under 10ms.
Old JOIN pattern would have crashed.
