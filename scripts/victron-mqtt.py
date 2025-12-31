#!/usr/bin/env python3
"""
Victron MQTT data collector for LibreNMS

Connects to a Victron GX device's MQTT broker, fetches current values,
and outputs JSON for LibreNMS application polling.

Usage: victron-mqtt.py <host> [port]

Requires: paho-mqtt (pip install paho-mqtt)
"""

import sys
import json
import time

try:
    import paho.mqtt.client as mqtt
except ImportError:
    print(json.dumps({"error": "paho-mqtt not installed. Run: pip install paho-mqtt"}))
    sys.exit(1)

# Topics to subscribe to (Victron GX MQTT structure)
TOPICS = [
    # System
    ("N/+/system/0/Ac/ConsumptionOnInput/L1/Power", "ac_consumption_l1_power"),
    ("N/+/system/0/Ac/ConsumptionOnOutput/L1/Power", "ac_consumption_out_l1_power"),
    ("N/+/system/0/Ac/Grid/L1/Power", "grid_l1_power"),
    ("N/+/system/0/Dc/Battery/Power", "battery_power"),
    ("N/+/system/0/Dc/Battery/Voltage", "battery_voltage"),
    ("N/+/system/0/Dc/Battery/Current", "battery_current"),
    ("N/+/system/0/Dc/Battery/Soc", "battery_soc"),
    ("N/+/system/0/Dc/Battery/State", "battery_state"),
    ("N/+/system/0/Dc/Battery/TimeToGo", "battery_time_to_go"),
    ("N/+/system/0/Dc/Pv/Power", "pv_power"),
    ("N/+/system/0/Dc/Pv/Current", "pv_current"),

    # AC System (acsystem) - AC Input
    ("N/+/acsystem/0/Ac/In/1/L1/V", "ac_in_l1_voltage"),
    ("N/+/acsystem/0/Ac/In/1/L1/I", "ac_in_l1_current"),
    ("N/+/acsystem/0/Ac/In/1/L1/F", "ac_in_l1_frequency"),
    ("N/+/acsystem/0/Ac/In/1/L1/P", "ac_in_l1_power"),
    # AC System - AC Output
    ("N/+/acsystem/0/Ac/Out/L1/V", "ac_out_l1_voltage"),
    ("N/+/acsystem/0/Ac/Out/L1/I", "ac_out_l1_current"),
    ("N/+/acsystem/0/Ac/Out/L1/F", "ac_out_l1_frequency"),
    ("N/+/acsystem/0/Ac/Out/L1/P", "ac_out_l1_power"),
    # AC System - DC
    ("N/+/acsystem/0/Dc/0/Voltage", "inverter_dc_voltage"),
    ("N/+/acsystem/0/Dc/0/Current", "inverter_dc_current"),
    ("N/+/acsystem/0/Dc/0/Power", "inverter_dc_power"),
    # AC System - Alarms
    ("N/+/acsystem/0/Alarms/GridLost", "alarm_grid_lost"),
    ("N/+/acsystem/0/Alarms/HighTemperature", "alarm_high_temp"),
    ("N/+/acsystem/0/Alarms/Overload", "alarm_overload"),

    # Multi RS / MPPT PV Strings
    ("N/+/multi/+/Pv/0/P", "pv_string_0_power"),
    ("N/+/multi/+/Pv/0/V", "pv_string_0_voltage"),
    ("N/+/multi/+/Pv/1/P", "pv_string_1_power"),
    ("N/+/multi/+/Pv/1/V", "pv_string_1_voltage"),
    ("N/+/multi/+/Pv/2/P", "pv_string_2_power"),
    ("N/+/multi/+/Pv/2/V", "pv_string_2_voltage"),
    ("N/+/multi/+/Pv/3/P", "pv_string_3_power"),
    ("N/+/multi/+/Pv/3/V", "pv_string_3_voltage"),
    ("N/+/multi/+/Yield/Power", "pv_yield_power"),

    # Solar Chargers (standalone MPPT)
    ("N/+/solarcharger/+/Yield/Power", "pv_charger_power"),
    ("N/+/solarcharger/+/Pv/V", "pv_charger_voltage"),
    ("N/+/solarcharger/+/Pv/I", "pv_charger_current"),
]

class VictronMQTTCollector:
    def __init__(self, host, port=1883, timeout=5):
        self.host = host
        self.port = port
        self.timeout = timeout
        self.data = {}
        self.connected = False
        self.done = False

    def on_connect(self, client, userdata, flags, reason_code, properties):
        if reason_code == 0:
            self.connected = True
            # Subscribe to all topics
            for topic, _ in TOPICS:
                client.subscribe(topic)
        else:
            self.data["error"] = f"Connection failed: {reason_code}"
            self.done = True

    def on_message(self, client, userdata, msg):
        try:
            payload = json.loads(msg.payload.decode())
            value = payload.get("value")

            # Find matching topic pattern
            for pattern, key in TOPICS:
                if self._topic_matches(pattern, msg.topic):
                    # Handle multiple instances (e.g., solarcharger/0, solarcharger/1)
                    parts = msg.topic.split("/")
                    if "solarcharger" in msg.topic:
                        instance = parts[3] if len(parts) > 3 else "0"
                        key = f"{key}_{instance}"
                    elif "vebus" in msg.topic:
                        instance = parts[3] if len(parts) > 3 else "0"
                        if instance != "0":
                            key = f"{key}_{instance}"

                    self.data[key] = value
                    break
        except (json.JSONDecodeError, KeyError):
            pass

    def _topic_matches(self, pattern, topic):
        """Simple MQTT wildcard matching"""
        pattern_parts = pattern.split("/")
        topic_parts = topic.split("/")

        if len(pattern_parts) != len(topic_parts):
            return False

        for p, t in zip(pattern_parts, topic_parts):
            if p == "+":
                continue
            if p != t:
                return False
        return True

    def collect(self):
        client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION2)
        client.on_connect = self.on_connect
        client.on_message = self.on_message

        try:
            client.connect(self.host, self.port, 60)

            # Run loop for timeout seconds
            start = time.time()
            while time.time() - start < self.timeout and not self.done:
                client.loop(timeout=0.1)

            client.disconnect()

        except Exception as e:
            self.data["error"] = str(e)

        return self.data


def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "Usage: victron-mqtt.py <host> [port]"}))
        sys.exit(1)

    host = sys.argv[1]
    port = int(sys.argv[2]) if len(sys.argv) > 2 else 1883

    collector = VictronMQTTCollector(host, port)
    data = collector.collect()

    print(json.dumps(data))


if __name__ == "__main__":
    main()
