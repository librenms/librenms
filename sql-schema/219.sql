CREATE TABLE application_metrics (app_id INT(11) NOT NULL, metric VARCHAR(18) NOT NULL, value INT(11), value_prev INT(11));
CREATE UNIQUE INDEX application_metrics_app_id_metric_uindex ON application_metrics (app_id, metric);
