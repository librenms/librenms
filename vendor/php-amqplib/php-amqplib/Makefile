test:
	phpunit
.PHONY: benchmark
benchmark:
	@echo "Publishing 4000 msgs with 1KB of content:"
	php benchmark/producer.php 4000
	@echo "Consuming 4000:"
	php benchmark/consumer.php
	@echo "Stream produce 100:"
	php benchmark/stream_tmp_produce.php 100
	@echo "Socket produce 100:"
	php benchmark/socket_tmp_produce.php 100