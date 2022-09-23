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
        for key, val in _config.items():
            if key == "db_port":
                # Special case: port number
                self.db_port = int(val)
            elif key.startswith("db_"):
                # Prevent prototype pollution by enforcing prefix
                setattr(DBConfig, key, val)
