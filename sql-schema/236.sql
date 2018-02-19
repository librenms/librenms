DROP INDEX device_id ON bgpPeers;
CREATE INDEX device_id ON bgpPeers (device_id, context_name);
DROP INDEX device_id ON bgpPeers_cbgp;
CREATE INDEX device_id ON bgpPeers_cbgp (device_id, bgpPeerIdentifier, context_name);
