-- Run database.sql first to create the Club Hub database and tables.
-- Run this file only if the previous techworkshop database contains data.

INSERT INTO clubhub.participants (id, full_name, email, created_at)
SELECT id, full_name, email, created_at
FROM techworkshop.participants
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  email = VALUES(email),
  created_at = VALUES(created_at);

INSERT INTO clubhub.`groups` (id, name, description, created_at)
SELECT id, name, description, created_at
FROM techworkshop.`groups`
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  description = VALUES(description),
  created_at = VALUES(created_at);

INSERT INTO clubhub.participant_groups (participant_id, group_id, joined_at)
SELECT participant_id, group_id, joined_at
FROM techworkshop.participant_groups
ON DUPLICATE KEY UPDATE joined_at = VALUES(joined_at);
