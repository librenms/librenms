ALTER TABLE `poller_cluster` DROP INDEX `poller_cluster_node_id_unique`;
ALTER TABLE `poller_cluster` ADD UNIQUE `poller_cluster_node_id_poller_name_unique` (`node_id`, `poller_name`);
