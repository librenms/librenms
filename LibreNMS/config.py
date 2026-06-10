import os


class DBConfig:
    """
    Bare minimal config class for LibreNMS.DB class usage
    """

    # Start with defaults and override
    db_host = "localhost"
    db_port = 0
    db_socket = None
    db_user = "librenms"
    db_pass = ""
    db_name = "librenms"
    db_sslmode = "disabled"
    db_ssl_ca = "/etc/ssl/certs/ca-certificates.crt"

    def populate(self, _config):
        self.db_host = os.getenv("DB_HOST", _config.get("db_host", self.db_host))
        self.db_name = os.getenv("DB_DATABASE", _config.get("db_name", self.db_name))
        self.db_pass = os.getenv("DB_PASSWORD", _config.get("db_pass", self.db_pass))
        self.db_port = int(os.getenv("DB_PORT", _config.get("db_port", self.db_port)))
        self.db_socket = os.getenv(
            "DB_SOCKET", _config.get("db_socket", self.db_socket)
        )
        self.db_user = os.getenv("DB_USERNAME", _config.get("db_user", self.db_user))
        self.db_sslmode = os.getenv(
            "DB_SSLMODE", _config.get("db_sslmode", self.db_sslmode)
        )
        self.db_ssl_ca = os.getenv(
            "MYSQL_ATTR_SSL_CA", _config.get("db_ssl_ca", self.db_ssl_ca)
        )
