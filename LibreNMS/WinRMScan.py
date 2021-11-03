#!/usr/bin/env python3
"""
Scan windows computers with WinRM using PyWinRM
Install PyWinRM - pip install pywinrm

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

@package    LibreNMS
@link       https://www.librenms.org
@copyright  2021 Thomas Ford
@author     Thomas Ford <tford@thomasaford.com>
"""

import logging
import json

from datetime import datetime
from pymysql import NULL
import LibreNMS

logger = logging.getLogger(__name__)

try:
    import winrm
    isInstall = True
except ModuleNotFoundError:
    isInstall = False
    pass


def process_polling(self, device_id):
    if isInstall == True:
        hostname = get_device_details(self, device_id)
        session = winrm.Session(hostname, auth=(self.config.winrm.username, self.config.winrm.password), transport="ntlm")
        exit_code, output = process_services(self, session, device_id)
        if exit_code == 0:
            exit_code, output = process_processes(self, session, device_id)
        return exit_code, output
    else:
        return -1, "Unable to load WinRM library. Please install pywinrm using - pip install pywinrm."

def process_discovery(self, device_id):
    if isInstall == True:
        hostname = get_device_details(self, device_id)
        session = winrm.Session(hostname, auth=(self.config.winrm.username, self.config.winrm.password), transport="ntlm")
        exit_code, output =  process_software(self, session, device_id)
        return exit_code, output
    else:
        return -1, "Unable to load WinRM library. Please install pywinrm using - pip install pywinrm."

def get_device_details(self, device_id):
    devices = self._db.query(
        "SELECT `hostname` FROM `devices` WHERE (`device_id` = %s)",
        (device_id)
    )
    for device in devices:
        return device[0]

def process_services(self, session, device_id):
    if isInstall == True:
        try:
            # self.config.winrm.username
            result = session.run_ps("Get-Service | Select-Object ServiceName,DisplayName,Status,ServiceType,StartType,CanPauseAndContinue,CanShutdown,CanStop | ConvertTo-Json")

            if result.status_code == 0:
                services = json.loads(result.std_out.decode("utf-8","ignore"))
                self._db.query(
                    "UPDATE `winrm_services` SET `disabled` = 1 WHERE (`device_id` = %s)",
                    (device_id)
                )
                for service in services:
                    self._db.query("INSERT INTO `winrm_services` (`device_id`, `service_name`, `display_name`, `status`, `service_type`, `start_type`, `can_pause_and_continue`, `can_shutdown`, `can_stop`, `disabled`) "
                        'values(%s, %s, %s, %s, %s, %s, %s, %s, %s, 0) ON DUPLICATE KEY UPDATE '
                        '`display_name`=%s, `status`=%s, `service_type`=%s, `start_type`=%s, `can_pause_and_continue`=%s, `can_shutdown`=%s, `can_stop`=%s, `disabled`=0;',
                        (device_id, 
                        service["ServiceName"], 
                        service["DisplayName"], 
                        service["Status"], 
                        service["ServiceType"], 
                        service["StartType"], 
                        service["CanPauseAndContinue"], 
                        service["CanShutdown"], 
                        service["CanStop"],
                        service["DisplayName"], 
                        service["Status"], 
                        service["ServiceType"], 
                        service["StartType"], 
                        service["CanPauseAndContinue"], 
                        service["CanShutdown"], 
                        service["CanStop"])
                    )
                return 0, NULL
            else:
                return result.status_code, result.std_err

        except Exception as e:
            print(e)
            return 1, e

def process_processes(self, session, device_id):
    if isInstall == True:
        try:
            # self.config.winrm.username
            result = session.run_ps("Get-Process -IncludeUserName | Select-Object Id,Name,ProcessName,UserName,NPM,PM,WS,VM,CPU | ConvertTo-Json")

            if result.status_code == 0:
                processes = json.loads(result.std_out.decode("utf-8","ignore"))
                self._db.query(
                    "DELETE FROM `winrm_processes` WHERE (`device_id` = %s)",
                    (device_id)
                )
                
                for processe in processes:
                    UserName = NULL
                    if processe["UserName"] is not None:
                        UserName = processe["UserName"].replace("/", "//")
                    self._db.query('INSERT INTO `winrm_processes` (`device_id`, `pid`, `name`, `process_name`, `username`, `npm`, `pm`, `ws`, `vm`, `cpu`) '
                        'values(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s);',
                        (device_id, 
                        processe["Id"], 
                        processe["Name"], 
                        processe["ProcessName"], 
                        UserName, 
                        processe["NPM"], 
                        processe["PM"],
                        processe["WS"], 
                        processe["VM"], 
                        processe["CPU"])
                    )
                return 0, NULL
            else:
                return result.status_code, result.std_err

        except Exception as e:
            print(e)
            return 1, e

def process_software(self, session, device_id):
    if isInstall == True:
        try:
            result = session.run_ps("Get-WmiObject -Class Win32_Product | Select-Object Name,Vendor,Version,InstallDate,Description | ConvertTo-Json")
            if result.status_code == 0:
                software = json.loads(result.std_out.decode("utf-8","ignore"))
                self._db.query(
                    "UPDATE `winrm_device_software` SET `disabled` = 1 WHERE (`device_id` = %s)",
                    (device_id)
                )
                for application in software:
                    if application["Name"] is not None:
                        self._db.query('INSERT INTO `winrm_software` (`name`, `vendor`, `description`) '
                            'values(%s, %s, %s) ON DUPLICATE KEY UPDATE '
                            '`description`=%s; ',
                            (application["Name"], 
                            application["Vendor"],
                            application["Description"],
                            application["Description"])
                        )

                        application_id = self._db.query('SELECT `id` FROM `winrm_software` '
                            'WHERE (`name` = %s) AND (`vendor` = %s);',
                            (application["Name"], 
                            application["Vendor"])
                        )
                        
                        install_date = NULL
                        if application["InstallDate"] is not None:
                            install_date = datetime.strptime(application["InstallDate"], '%Y%m%d').strftime('%Y/%m/%d')
                        
                        for software_id in application_id:
                            self._db.query("INSERT INTO `winrm_device_software` (`device_id`, `software_id`, `version`, `install_date`, `disabled`) "
                                'values(%s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE '
                                '`version`=%s, `install_date`=%s, `disabled`=%s;',
                                (device_id, 
                                software_id[0], 
                                application["Version"], 
                                install_date, 
                                0,
                                application["Version"], 
                                install_date,
                                0)
                            )
                return 0, NULL
            else:
                return result.status_code, result.std_err

        except Exception as e:
            print(e)
            return 1, e