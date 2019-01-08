DELETE FROM dashboards WHERE user_id NOT IN (SELECT user_id FROM users) AND dashboard_id NOT IN (SELECT DISTINCT dashboard_id FROM users_widgets);
UPDATE dashboards SET user_id = (SELECT user_id FROM users WHERE level = 10 LIMIT 1) WHERE user_id NOT IN (SELECT user_id FROM users);
