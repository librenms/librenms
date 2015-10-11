UPDATE widgets SET widget_title = 'World map' WHERE widget = 'worldmap';
UPDATE widgets SET widget_title = 'Globe map' WHERE widget = 'globe';
UPDATE users_widgets SET title = 'World Map' WHERE widget_id = (SELECT widget_id FROM widgets WHERE widget = 'worldmap');
UPDATE users_widgets SET title = 'Globe Map' WHERE widget_id = (SELECT widget_id FROM widgets WHERE widget = 'globe');
