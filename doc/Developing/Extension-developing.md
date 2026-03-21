---
title: Developing SNMP Extensions
description: Developer guideline for creating SNMP extend scripts and systemd/cron cache refresh on the host side.
tags:

- developing
- snmp
- extensions

---

# Developing SNMP Extensions (Developer Guideline)

This guideline is about what you (as an extension developer) can provide to users so setup is predictable across distros and support burden stays low.

!!! success "Value to the user"
    - Clear separation between *code*, *configuration*, and *runtime cache*
    - Faster and more reliable SNMP polling (less timeouts)
    - Easier automation (Ansible/sed) and safer upgrades

## What you provide

As a developer, define what files and snippets you ship so users can install your extension without guessing paths, users, or config locations.

!!! info "Deliverables"
    When you publish an extension, aim to provide:

    - A runnable executable installed to `/usr/local/lib/snmpd/<name>`
    - A documented config file stored under `/etc/snmp/extension/<name>.*`
    - An snmpd snippet line for `/etc/snmp/snmpd.conf.d/librnms.conf`
    - A cache strategy that writes to `/run/snmp/extension/<name>.*`
    - A cron line (for `/etc/cron.d/snmpd`) and a systemd unit/timer example

!!! tip
    Prefer cache + `extend ... /bin/cat ...` over running a heavy script directly in snmpd.

## Linux directory layout

As a developer, place files in standard locations so users can upgrade, back up, and troubleshoot consistently.

This layout follows common Linux conventions for system extensions and keeps things organized by purpose.

!!! info "Why this matters"
    Users can reason about where things live, and you can support one layout across many extensions.

- `/usr/local/lib/snmpd/`
  - Extension executables (scripts/binaries) installed on the monitored host.
- `/etc/snmp/extension/`
  - Per-extension configuration files (json/conf/etc).
- `/etc/snmp/snmpd.conf.d/librnms.conf`
  - SNMP configuration snippets for LibreNMS (`extend` lines).
- `/run/snmp/extension/`
  - Runtime cache files written by extensions (tmpfs; cleared on reboot).

## snmpd integration

As a developer, you should instruct users to add your `extend` configuration to `/etc/snmp/snmpd.conf.d/librnms.conf` (a LibreNMS-specific snippet), rather than editing the main `/etc/snmp/snmpd.conf`.

!!! info "Why use `/etc/snmp/snmpd.conf.d/librnms.conf`"
    - Avoids conflicts with distro defaults and other local SNMP configuration
    - Survives upgrades better: package updates are more likely to touch `snmpd.conf` than your snippet
    - Simplifies support: one file to review/share when troubleshooting
    - Easier automation: tools like Ansible can manage a single line without parsing a large config

### Provide the extend line

Users add your `extend` line to `/etc/snmp/snmpd.conf.d/librnms.conf`.

!!! example "Recommended: return cached output"
    ```conf
    extend myext /bin/cat /run/snmp/extension/myext.json
    ```

!!! example "Alternative: run directly (only for fast scripts)"
    ```conf
    extend myext /usr/local/lib/snmpd/myext --config /etc/snmp/extension/myext.conf
    ```

## OS support notes

As a developer, include distro-specific notes so users can install prerequisites and create directories with the correct package names and service conventions.

!!! info "Why you document both"
    Users commonly deploy on both Debian-family and RedHat-family hosts; package names, service handling, and snmpd runtime user vary.

=== "Debian/Ubuntu"

    !!! example "Install + directories"
        ```bash
        apt-get update
        apt-get install -y snmpd

        install -d -m 0755 /usr/local/lib/snmpd
        install -d -m 0755 /etc/snmp/extension
        install -d -m 0755 /run/snmp/extension
        install -d -m 0755 /etc/snmp/snmpd.conf.d
        ```

    !!! note
        `snmpd` typically runs as `Debian-snmp`.

=== "RedHat/CentOS"

    !!! example "Install + directories"
        ```bash
        dnf install -y net-snmp net-snmp-utils
        systemctl enable --now snmpd

        install -d -m 0755 /usr/local/lib/snmpd
        install -d -m 0755 /etc/snmp/extension
        install -d -m 0755 /run/snmp/extension
        install -d -m 0755 /etc/snmp/snmpd.conf.d
        ```

    !!! note
        `snmpd` commonly runs as `snmp`.

## Install script

As a developer, ship a small installer to reduce copy/paste setup and ensure directories, permissions, and `extend` lines are created correctly.

!!! info "Why provide an install script"
    A small, repeatable install script reduces user setup time, avoids missed steps, and makes your extension easier to deploy at scale.

!!! tip "Design goals"
    - Idempotent: safe to run multiple times
    - Minimal dependencies: use coreutils (`install`, `mkdir`, `chmod`) where possible
    - Clear variables: allow overriding name/URLs/paths
    - Does not overwrite unrelated snmpd config: only manages `/etc/snmp/snmpd.conf.d/librnms.conf` and your files

???+ example "Install script skeleton"
    ```bash
    #!/usr/bin/env bash
    #---------------------------------------------------------------------------------------------------------------
    #
    # Script name : librenms-snmp-extension-myext-install.sh
    # Description : Install script for LibreNMS SNMP extension "myext"
    # Repository  : <https://github.com/example/myext>
    # Version     : 1.0.0
    # Author      : Your Name <your.email@example.com>
    # License     : MIT
    #---------------------------------------------------------------------------------------------------------------

    set -euo pipefail

    ID="unknown"
    ID_LIKE="unknown"
    PKG_FAMILY="unknown"

    EXT_NAME="myext"
    EXT_URL="https://example.invalid/myext"
    EXT_BIN="/usr/local/lib/snmpd/${EXT_NAME}"
    EXT_CONF_DIR="/etc/snmp/extension"
    EXT_CONF="${EXT_CONF_DIR}/${EXT_NAME}.conf"
    EXT_CACHE_DIR="/run/snmp/extension"
    EXT_CACHE="${EXT_CACHE_DIR}/${EXT_NAME}.json"
    SNMP_SNIPPET="/etc/snmp/snmpd.conf.d/librnms.conf"
    SYSTEMD_UNIT_DIR="/etc/systemd/system"
    SYSTEMD_UNIT_TIMER_URL="https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/common/librenms-snmp-extension@.timer"
    SYSTEMD_UNIT_SERVICE_URL="https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/common/librenms-snmp-extension@.service"

    SNMPD_MAIN_CONF="/etc/snmp/snmpd.conf"
    SNMPD_INCLUDE_DIR_LINE="includeDir /etc/snmp/snmpd.conf.d"

    # Default: auto-detect systemd, fallback to cron
    REFRESH_METHOD=""
    # Optional: disable interactivity for automation
    # export AUTO_YES=1
    VERBOSE_LOG=${VERBOSE_LOG:-0}

    # Logging functions
    _date() {
      date +%Y-%m-%d_%H:%M:%S
    }

    log_verbose() {
      [[ "${VERBOSE_LOG}" -eq 1 ]] || return 0
      echo -e "\033[94m VERBOSE: $*\033[0m"
    }

    log_info() {
      echo "$( _date ): INFO: $*"
    }

    log_notice() {
      echo -e "\033[92m$( _date ): NOTICE: $* \033[0m"
    }

    log_warn() {
      echo -e "\033[93m$( _date ): WARN: $* \033[0m"
    }

    log_error() {
      echo -e "\033[91m$( _date ): ERROR: $* \033[0m" >&2
    }

    log_alert() {
      echo -e "\033[91m$( _date ): ALERT: $* \033[0m" >&2
    }

    log_critical() {
      echo -e "\033[91m$( _date ): CRITICAL: $* \033[0m" >&2
    }

    run_cmd() {
      local -a cmd=("$@")
      log_verbose "Running command: ${cmd[*]}"
      "${cmd[@]}"
    }

    error() {
      log_error "$*"
      exit 1
    }

    usage() {
      echo "Usage: $0 [--cron|--systemd]"
      echo "  --cron     Force cron-based cache refresh"
      echo "  --systemd  Force systemd-based cache refresh"
      echo "  (no arg)   Auto-detect systemd, fallback to cron"
      exit 1
    }

    while [[ $# -gt 0 ]]; do
      case "$1" in
        --cron)
          REFRESH_METHOD="cron"
          shift
          ;;
        --systemd)
          REFRESH_METHOD="systemd"
          shift
          ;;
        -h|--help)
          usage
          ;;
        *)
          log_error "Unknown option: $1"
          usage
          ;;
      esac
    done

    if [[ $EUID -ne 0 ]]; then
      log_error "This script must be run with sudo or as root"
      exit 1
    fi

    log_notice "Installing ${EXT_NAME} SNMP extension."

    ask_yes_no() {
      if [[ "${AUTO_YES:-}" == "1" ]]; then
        return 0
      fi
      local prompt="$1"
      local answer

      while true; do
        read -rp "$prompt [y/n]: " answer < /dev/tty
        case "${answer,,}" in
          y|yes) return 0 ;;
          n|no)  return 1 ;;
          *) echo "Please answer y or n." ;;
        esac
      done
    }

    is_installed() {
      command -v "$1" >/dev/null 2>&1
    }

    is_systemd_available() {
      if command -v systemctl >/dev/null 2>&1 && \
         systemctl is-system-running >/dev/null 2>&1; then
        return 0
      fi
      return 1
    }

    detect_refresh_method() {
      if [[ -n "${REFRESH_METHOD}" ]]; then
        return
      fi
      if is_systemd_available; then
        REFRESH_METHOD="systemd"
        log_notice "Detected systemd, using timer-based cache refresh."
      else
        REFRESH_METHOD="cron"
        log_notice "No systemd detected, using cron-based cache refresh."
      fi
    }

    if [[ -r /etc/os-release ]]; then
      . /etc/os-release
    else
      error "Cannot detect OS (missing /etc/os-release)"
    fi

    case "$ID_LIKE $ID" in
      *debian*|*ubuntu*)
        PKG_FAMILY="deb"
        SNMP_USER="Debian-snmp"
        ;;
      *rhel*|*fedora*|*centos*)
        PKG_FAMILY="rpm"
        SNMP_USER="snmp"
        ;;
      *)
        error "Unsupported distro: ID=$ID ID_LIKE=$ID_LIKE"
        ;;
    esac

    debian_install() {
      if ! is_installed curl || ! is_installed snmpd; then
        if ask_yes_no "Install dependencies?"; then
          run_cmd apt update
          run_cmd apt install -y curl snmpd ca-certificates
        else
          log_warn "Skipping dependencies."
        fi
      fi
    }

    rpm_install() {
      if ! is_installed curl || ! is_installed snmpd; then
        if ask_yes_no "Install dependencies?"; then
          run_cmd dnf install -y curl net-snmp net-snmp-utils ca-certificates
          run_cmd systemctl enable --now snmpd || true
        else
          log_warn "Skipping dependencies."
        fi
      fi
    }

    install_deps() {
      case "$PKG_FAMILY" in
        deb)
          debian_install
          ;;
        rpm)
          rpm_install
          ;;
      esac
    }

    if [[ -f "${SNMPD_MAIN_CONF}" ]]; then
      if ! grep -Fqs "${SNMPD_INCLUDE_DIR_LINE}" "${SNMPD_MAIN_CONF}"; then
        log_warn "Missing '${SNMPD_INCLUDE_DIR_LINE}' in ${SNMPD_MAIN_CONF}."
        log_warn "Without it, snmpd may not load ${SNMP_SNIPPET}."

        if ask_yes_no "Append includeDir to ${SNMPD_MAIN_CONF}?"; then
          install -d -m 0755 /etc/snmp/snmpd.conf.d
          cp -a "${SNMPD_MAIN_CONF}" "${SNMPD_MAIN_CONF}.bak.$(date +%Y%m%d%H%M%S)"
          printf '\n%s\n' "${SNMPD_INCLUDE_DIR_LINE}" >> "${SNMPD_MAIN_CONF}"
          log_notice "Appended '${SNMPD_INCLUDE_DIR_LINE}' to ${SNMPD_MAIN_CONF}."
          log_notice "Restart snmpd to apply changes."
        else
          log_warn "Skipping includeDir update."
        fi
      fi
    else
      log_warn "${SNMPD_MAIN_CONF} not found; cannot verify includeDir configuration."
    fi

    install -v -d -m 0755 /usr/local/lib/snmpd
    install -v -d -m 0755 "${EXT_CONF_DIR}"
    install -v -d -m 0755 "${EXT_CACHE_DIR}"
    install -v -d -m 0755 /etc/snmp/snmpd.conf.d

    log_info "Downloading ${EXT_NAME} from ${EXT_URL}..."
    curl -fsSL "${EXT_URL}" -o "${EXT_BIN}" || error "Failed to download ${EXT_NAME}"
    chmod 0755 "${EXT_BIN}"

    if [ ! -f "${EXT_CONF}" ]; then
      log_info "Installing default configuration for ${EXT_NAME}..."
      cat >"${EXT_CONF}" <<'EOF'
    # myext configuration
    EOF
    fi

    EXTEND_LINE="extend ${EXT_NAME} /bin/cat ${EXT_CACHE}"
    if [ ! -f "${SNMP_SNIPPET}" ] || ! grep -Fqs "${EXTEND_LINE}" "${SNMP_SNIPPET}"; then
      printf '%s\n' "${EXTEND_LINE}" >>"${SNMP_SNIPPET}"
      log_notice "Added extend line to ${SNMP_SNIPPET}."
    fi

    detect_refresh_method

    install_cron() {
      log_info "Installing cron job..."
      CRON_FILE="/etc/cron.d/librenms-snmp-extension-${EXT_NAME}"
      cat >"${CRON_FILE}" <<EOF
    PATH=/usr/local/bin:/usr/bin:/bin
    */5 * * * * ${SNMP_USER} /usr/local/lib/snmpd/${EXT_NAME} --config /etc/snmp/extension/${EXT_NAME}.conf --output /run/snmp/extension/${EXT_NAME}.json
    EOF
      chmod 644 "${CRON_FILE}"
      log_notice "Cron job installed to ${CRON_FILE}."
    }

    install_systemd() {
      log_info "Installing systemd timer..."

      install -v -d -m 0755 "${SYSTEMD_UNIT_DIR}"

      # Download common timer unit (idempotent - only if missing)
      TIMER_FILE="${SYSTEMD_UNIT_DIR}/librenms-snmp-extension@.timer"
      if [ ! -f "${TIMER_FILE}" ]; then
        log_info "Downloading common timer unit..."
        curl -fsSL "${SYSTEMD_UNIT_TIMER_URL}" -o "${TIMER_FILE}" || error "Failed to download timer unit"
      else
        log_info "Timer unit already exists at ${TIMER_FILE}."
      fi

      # Download common service unit (idempotent - only if missing)
      SERVICE_FILE="${SYSTEMD_UNIT_DIR}/librenms-snmp-extension@.service"
      if [ ! -f "${SERVICE_FILE}" ]; then
        log_info "Downloading common service unit..."
        curl -fsSL "${SYSTEMD_UNIT_SERVICE_URL}" -o "${SERVICE_FILE}" || error "Failed to download service unit"
      else
        log_info "Service unit already exists at ${SERVICE_FILE}."
      fi

      # Create override for this extension (idempotent)
      OVERRIDE_DIR="${SYSTEMD_UNIT_DIR}/librenms-snmp-extension@${EXT_NAME}.service.d"
      OVERRIDE_FILE="${OVERRIDE_DIR}/override.conf"
      install -v -d -m 0755 "${OVERRIDE_DIR}"

      if [ ! -f "${OVERRIDE_FILE}" ]; then
        cat >"${OVERRIDE_FILE}" <<EOF
    [Service]
    ExecStart=
    ExecStart=/usr/local/lib/snmpd/${EXT_NAME} --config /etc/snmp/extension/${EXT_NAME}.conf --output /run/snmp/extension/${EXT_NAME}.json
    EOF
        log_notice "Override installed at ${OVERRIDE_FILE}."
      else
        log_info "Override already exists at ${OVERRIDE_FILE}."
      fi

      run_cmd systemctl daemon-reload
      run_cmd systemctl enable --now "librenms-snmp-extension@${EXT_NAME}.timer"
      log_notice "Systemd timer enabled for ${EXT_NAME}."
    }

    case "${REFRESH_METHOD}" in
      cron)
        install_cron
        ;;
      systemd)
        install_systemd
        ;;
    esac

    log_notice "Installed ${EXT_NAME} with ${REFRESH_METHOD} cache refresh."
    ```

!!! info "Using error handling with run_cmd"
    The `run_cmd` function logs commands before execution. Use `|| error` to exit on failure:

    ```bash
    log_info "Checking if certificate needs renewal..."
    run_cmd /usr/bin/step certificate needs-renewal "${CERT_LOCATION}" >/dev/null 2>&1 || error "Certificate check failed"
    ```

!!! info "Override systemd service per extension"
    The install script creates an idempotent override at `/etc/systemd/system/librenms-snmp-extension@${EXT_NAME}.service.d/override.conf` that sets the `ExecStart` for your extension.

    This approach is idempotent: re-running the install script will not overwrite an existing override. If you need to modify it later:

    ```bash
    systemctl edit librenms-snmp-extension@${EXT_NAME}.service
    ```

## Documentation

As a developer, provide a documentation page for your extension that is easy to follow, easy to automate, and easy to keep correct over time.

!!! info "Why this matters"
    Good docs reduce support load and prevent fragile one-off setups (permissions, timeouts, reboots, upgrades).

### Structure (content + layout)

As a developer, keep the doc structure consistent so users can skim and automation teams can copy/paste safely.

??? example "Header tree"
    ```md
    # MyExtension

    - Metadata block (script type, paths, cache model)
    - Short intro (1-2 sentences)

    ## Scripted install

    - Install script + usage

    ## Manual install

    ### Prerequisites
    (packages, permissions/sudo, required commands)

    ### Install

    - snmpd extend snippet (`/etc/snmp/snmpd.conf.d/librnms.conf`)
    - Directory setup + cron or systemd

    ## Configuration

    - Config file (`/etc/snmp/extension/<name>.*`)

    ## Verification

    - snmpwalk/snmpget example + expected output
    ```

!!! tip "What to include"
    - Scripted install first, then manual install
    - Complete file paths in backticks and copy/paste-ready commands
    - snmpd snippet location: `/etc/snmp/snmpd.conf.d/librnms.conf`
    - Cache model: direct vs cached (and refresh interval)
    - Verification steps (example `snmpwalk`/`snmpget` + expected output shape)
    - Troubleshooting (timeouts, permission issues, missing `/run` directory after reboot)

### Metadata

As a developer, add a small metadata block near the top of the page so users know what they are deploying.

!!! info "What to include (stable)"
    - Script type: `python`, `bash`, or `perl`
    - Files used: executable path, config path, cache path, snmpd snippet path
    - Cache model: direct vs cached (and refresh interval)
    - Required packages/commands

!!! warning "What to avoid (hard to maintain)"
    - Do not include build time/date or "last updated" timestamps
    - Avoid hard-coded versions in docs (they drift quickly)

!!! example "Metadata block"
    | Field | Value |
    |---|---|
    | Script type | `python` |
    | Executable | `/usr/local/lib/snmpd/myext` |
    | Config | `/etc/snmp/extension/myext.conf` |
    | Cache | `/run/snmp/extension/myext.json` |
    | snmpd snippet | `/etc/snmp/snmpd.conf.d/librnms.conf` |
    | Cache refresh | cron or systemd timer (every 5 minutes) |

## Cache refresh

As a developer, if the application take more then 250ms to complete, provide a caching strategy that writes to `/run/snmp/extension/` and an snmpd `extend` line that returns that file.

!!! info "Why cache refresh"
    - More reliable polling: avoids long-running scripts inside `snmpd`
    - `/run` is tmpfs: document creating `/run/snmp/extension/` (for example `install -d -m 0755 /run/snmp/extension`) and/or use systemd `RuntimeDirectory=` so it is recreated on boot

Your extension should write its output to `/run/snmp/extension/` (json/text) and the snmpd `extend` line should return that file.

You should provide users with both a cron line and a systemd timer example to run the extension on a regular schedule (for example, every 5 minutes).

### Cron

As a developer, provide one cron line users can append, and include guidance for automation.

!!! info "What you provide (cron)"
    - A single cron line users can append to `/etc/cron.d/snmpd`
    - Automation hint: use `sed` (or similar) to find/replace lines, or Ansible `lineinfile`:
      <https://docs.ansible.com/projects/ansible/latest/collections/ansible/builtin/lineinfile_module.html>


!!! example "Cron job (Debian/Ubuntu user shown)"
    ```cron
    # Apache: https://docs.librenms.org/Extensions/Applications/Apache #
    PATH=/usr/local/bin:/usr/bin:/bin
    */5 * * * * Debian-snmp /usr/local/lib/snmpd/myext --config /etc/snmp/extension/myext.conf --output /run/snmp/extension/myext.json
    ```

!!! note
    - The `PATH` variable is required because cron jobs run with a minimal environment.
    - The username field is required in `/etc/cron.d/*` files.
    - On RedHat/CentOS, replace `Debian-snmp` with `snmp` (or the user your snmpd runs as).

### Systemd

As a developer, provide a service + timer example for users that prefer systemd-based scheduling.

Provide a common template so users can drop it in and only change the extension name.

!!! info "What you provide (systemd)"
    - A service + timer example users can drop into `/etc/systemd/system/`
    - Better observability than cron on systemd hosts (journald logging, unit-level status, and dependency management)

Create `/etc/systemd/system/librenms-snmp-extension@.service`:

??? example "Service unit"
    ```ini
    # SNMP common extesion cache. #
    [Unit]
    Description=LibreNMS SNMP extension cache: %i

    [Service]
    Type=oneshot
    User=snmp
    Group=snmp
    RuntimeDirectory=snmp/extension
    RuntimeDirectoryMode=0755
    ExecStart=/usr/local/lib/snmpd/%i --config /etc/snmp/extension/%i.conf --output /run/snmp/extension/%i.json
    NoNewPrivileges=true
    PrivateTmp=true
    ProtectHome=true
    ProtectSystem=full
    ReadWritePaths=/run/snmp/extension
    ```

Create `/etc/systemd/system/librenms-snmp-extension@.timer`:

??? example "Timer unit"
    ```ini
    # SNMP common extension cache. #
    [Unit]
    Description=Run LibreNMS SNMP extension cache refresh: %i

    [Timer]
    OnBootSec=2min
    OnUnitActiveSec=5min
    AccuracySec=30s
    Persistent=true

    [Install]
    WantedBy=timers.target
    ```

??? example "Override `ExecStart=` for a single instance"
    If the common template `ExecStart=` does not match your extension, override it per-instance with a systemd drop-in (without modifying the `@.service` template).

    Example for an extension named `myext`.

    Create `/etc/systemd/system/librenms-snmp-extension@myext.service.d/override.conf`:

    ```ini
    [Service]
    # Clear the ExecStart from the template, then set a new one
    ExecStart=
    ExecStart=/usr/local/lib/snmpd/myext \
        --config /etc/snmp/extension/myext.conf \
        --output /run/snmp/extension/myext.json \
        --extra-flag
    ```

    Reload and restart the service:

    ```bash
    systemctl daemon-reload
    systemctl restart librenms-snmp-extension@myext.service
    ```

    Notes:

    - In systemd drop-ins, you must reset `ExecStart=` with an empty
      `ExecStart=` line before you set a replacement.
    - This changes only the `myext` instance; other extensions keep using the template.
    - Alternative: run `systemctl edit librenms-snmp-extension@myext.service`
      to create the drop-in.

Enable for an extension named `myext`:

    systemctl daemon-reload
    systemctl enable --now librenms-snmp-extension@myext.timer

??? info "Systemd templates (@) and unit keys"
    `@` indicates a *template unit*. Users enable an *instance* by providing the instance name after `@`.

    If your extension is named `myext`, users enable:

    ```bash
    systemctl daemon-reload
    systemctl enable --now librenms-snmp-extension@myext.timer
    ```

    systemd substitutes `%i` inside the unit files with the instance name (`myext`).

    Users can check status and logs:

    ```bash
    systemctl status librenms-snmp-extension@myext.service
    systemctl status librenms-snmp-extension@myext.timer
    journalctl -u librenms-snmp-extension@myext.service
    ```

    Service (`librenms-snmp-extension@.service`) key thinking:

    - `Type=oneshot`: runs the extension once per trigger and exits.
    - `User=`/`Group=`: run as the same user as `snmpd` to match
      permissions (often `snmp` on RPM-based distros, `Debian-snmp` on
      Debian/Ubuntu).
    - `RuntimeDirectory=snmp/extension`: creates `/run/snmp/extension`
      at service start (important because `/run` is cleared on reboot).
    - `ExecStart=...`: call the extension and write cache to
      `/run/snmp/extension/<name>.*`.
    - Hardening (`NoNewPrivileges=`, `PrivateTmp=`, `ProtectHome=`, `ProtectSystem=`): reduce blast radius if the extension is compromised.
    - `ReadWritePaths=/run/snmp/extension`: allow writes only where the cache lives.

    Timer (`librenms-snmp-extension@.timer`) key thinking:

    - Prefer `OnUnitActiveSec=` for simple "every N minutes".
    - Prefer `OnCalendar=` if you need cron-like calendars.
    - Do not use both unless you have a specific reason (it confuses users
      and makes schedules harder to reason about).
    - `Persistent=true`: runs missed activations after downtime.
    - `AccuracySec=`: allows coalescing timers to reduce wakeups.

!!! note
    Debian/Ubuntu: change `User=`/`Group=` to `Debian-snmp` if that is
    the user running snmpd on your system.
