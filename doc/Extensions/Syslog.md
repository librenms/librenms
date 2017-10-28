@version:3.5
@include "scl.conf"

# syslog-ng configuration file.
#
# This should behave pretty much like the original syslog on RedHat. But
# it could be configured a lot smarter.
#
# See syslog-ng(8) and syslog-ng.conf(5) for more information.
#
# Note: it also sources additional configuration files (*.conf)
#       located in /etc/syslog-ng/conf.d/

options {
        chain_hostnames(off);
        flush_lines(0);
        use_dns(no);
        use_fqdn(no);
        owner("root");
        group("adm");
        perm(0640);
        stats_freq(0);
        bad_hostname("^gconfd$");
};


source s_sys {
    system();
    internal();

};

source s_net {
        tcp(port(514) flags(syslog-protocol));
        udp(port(514) flags(syslog-protocol));
};


########################
# Destinations
########################
destination d_librenms {
        program("/opt/librenms/syslog.php" template ("$HOST||$FACILITY||$PRIORITY||$LEVEL||$TAG||$R_YEAR-$R_MONTH-$R_DAY $R_HOUR:$R_MIN:$R_SEC||$MSG||$PROGRAM\n") template-escape(yes));
};

filter f_kernel     { facility(kern); };
filter f_default    { level(info..emerg) and
                        not (facility(mail)
                        or facility(authpriv)
                        or facility(cron)); };
filter f_auth       { facility(authpriv); };
filter f_mail       { facility(mail); };
filter f_emergency  { level(emerg); };
filter f_news       { facility(uucp) or
                        (facility(news)
                        and level(crit..emerg)); };
filter f_boot   { facility(local7); };
filter f_cron   { facility(cron); };

########################
# Log paths
########################
log {
        source(s_net);
        source(s_sys);
        destination(d_librenms);
};

# Source additional configuration files (.conf extension only)
@include "/etc/syslog-ng/conf.d/*.conf"


# vim:ft=syslog-ng:ai:si:ts=4:sw=4:et:
