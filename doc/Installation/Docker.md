# Docker

An official LibreNMS docker image is available on
[DockerHub](https://hub.docker.com/r/librenms/librenms/). The image is
based on Alpine Linux and Nginx.

# Documentation

The [GitHub repository](https://github.com/librenms/docker) holds the full
install and configuration documentation.

# Quick install
1. Install docker: https://docs.docker.com/engine/install/
2. Download and unzip the compose files:
```
mkdir librenms
cd librenms
wget https://github.com/librenms/docker/archive/refs/heads/master.zip
unzip master.zip
cd docker-master/examples/compose
```
3. Set a new mariadb or mysql password in `.env` (`MARIADB_PASSWORD` or `MYSQL_PASSWORD`). Then read `compose.yml`.
4. Start the docker containers:
```
sudo docker compose -f compose.yml up -d
```
5. Open the web interface at `http://localhost:8000` to complete the configuration. Use the correct IP address or name instead of `localhost`.
