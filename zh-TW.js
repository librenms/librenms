(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.vuei18nLocales = factory());
}(this, (function () { 'use strict';
    return {
    "zh-TW": {
        "The device group could not be deleted": "無法刪除裝置群組",
        "Operating System": "作業系統",
        "Vendor": "廠商",
        "Ungrouped Devices": "尚無群組裝置",
        "Enabled": "啟用",
        "Priority": "重要性",
        "Program": "程式",
        "Authlog": "驗證歷程",
        "User": "使用者",
        "IP Address": "IP 位址",
        "Result": "結果",
        "Translation not fully supported": "多國語系功能尚未完備",
        "Warning {service_count}": "警告 {service_count}",
        "Critical {service_count}": "嚴重 {service_count}",
        "Disabled {port_count}": "已停用 {port_count}",
        "Errored {port_count}": "發生錯誤 {port_count}",
        "Ignored {port_count}": "已忽略 {port_count}",
        "Down {port_count}": "已關閉 {port_count}",
        "Deleted {port_count}": "已刪除 {port_count}",
        "About {project_name}": "關於 {project_name}",
        "See the <a href=\"{url}\">list of contributors</a> on GitHub.": "至 GitHub 觀看<a href=\"{url}\">貢獻者名單</a>。",
        "Docs": "文件",
        "Close": "關閉",
        "LibreNMS is an autodiscovering PHP/MySQL-based network monitoring system": "LibreNMS 是個以 PHP/MySQL 為基底的自動探索網路監控系統",
        "Packages": "軟體包",
        "Version": "版本",
        "Database Schema": "資料庫綱要",
        "Web Server": "Web 伺服器",
        "LibreNMS is a community-based project": "LibreNMS 是建基於社群開發的專案",
        "Please feel free to join us and contribute code, documentation, and bug reports:": "您隨時都可以加入社群貢獻您的程式碼、文件以及問題回報：",
        "Web site": "官方網站",
        "Bug tracker": "問題追蹤",
        "Community Forum": "社群論壇",
        "Changelog": "變更記錄",
        "Local git log": "本機 Git 記錄",
        "Contributors": "貢獻者",
        "Acknowledgements": "特別感謝",
        "Opt in to send anonymous usage statistics to LibreNMS?": "您要選擇以匿名方式提供使用統計資料給 LibreNMS 嗎？",
        "Online stats:": "線上統計資料",
        "Clear remote stats": "清除遠端統計資料",
        "IPv4 Addresses": "IPv4 位址",
        "IPv4 Networks": "IPv4 網路",
        "IPv6 Addresses": "IPv6 位址",
        "IPv6 Networks": "IPv6 網路",
        "Processors": "處理器",
        "Applications": "應用",
        "Disk I/O": "磁碟 I/O",
        "Syslog Entries": "Syslog 項目",
        "Eventlog Entries": "事件記錄項目",
        "Sensors": "感測器",
        "Wireless Sensors": "無線感測器",
        "Toner": "碳粉",
        "License": "授權",
        "Shutdown": "關機",
        "Select Devices": "選擇裝置",
        "Dynamic": "動態",
        "Static": "靜態",
        "Define Rules": "預設規則",
        "Create Device Group": "建立裝置群組",
        "Edit Device Group": "編輯裝置群組",
        "New Device Group": "新增裝置群組",
        "Pattern": "模式",
        "Type": "類型",
        "Name": "名稱",
        "User Preferences": "使用者喜好設定",
        "Global Administrative Access": "全域管理存取",
        "Device Permissions": "裝置權限",
        "Preferences": "喜好設定",
        "Language": "語言",
        "Change Password": "變更密碼",
        "Verify New Password": "確認新密碼",
        "Peering + Transit": "互連 + 轉送",
        "FDB Tables": "FDB 對照表",
        "ARP Tables": "ARP 對照表",
        "MAC Address": "MAC 位址",
        "IPv6 Address": "IPv6 位址",
        "IPv4 Address": "IPv4 位址",
        "Package": "軟體包",
        "Virtual Machines": "虛擬機器",
        "Device Groups": "裝置群組",
        "Register": "註冊",
        "Overview": "概觀",
        "Maps": "地圖",
        "Availability": "可用性",
        "Device Groups Maps": "裝置群組地圖",
        "Geographical": "地理",
        "Plugins": "外掛",
        "Plugin Admin": "外掛管理",
        "Tools": "工具",
        "Eventlog": "事件記錄",
        "Inventory": "設備",
        "MIB definitions": "MIB 定義",
        "No devices": "沒有裝置",
        "MIB associations": "MIB 關聯",
        "Manage Groups": "群組管理",
        "Device Dependencies": "裝置相依性",
        "Add Device": "新增裝置",
        "Delete Device": "刪除裝置",
        "All Services": "所有服務",
        "Add Service": "新增服務",
        "Traffic Bills": "流量帳單",
        "Pseudowires": "虛擬線路",
        "Customers": "客戶",
        "Transit": "轉送",
        "Core": "核心",
        "Alerts": "警報",
        "Deleted": "已刪除",
        "Health": "健康情況",
        "Memory": "記憶體",
        "Processor": "處理器",
        "Storage": "儲存",
        "Wireless": "無線",
        "Apps": "應用程式",
        "Routing": "路由",
        "Alerted": "已警報",
        "Notifications": "通知",
        "Alert History": "警報歷程",
        "Statistics": "統計資料",
        "Alert Rules": "警報規則",
        "Scheduled Maintenance": "定期維護",
        "Alert Templates": "警報範本",
        "Alert Transports": "警報傳送",
        "My Settings": "我的設定",
        "Settings": "設定",
        "Global Settings": "全域設定",
        "Global Search": "全域搜尋",
        "Validate Config": "組態驗證",
        "Auth History": "驗證歷程",
        "Peering": "互連",
        "API Settings": "API 設定",
        "API Docs": "API 文件",
        "The {attribute} must a valid IP address/network or hostname.": "這個 {attribute} 必需為有效的 IP 位址/網路或主機名稱。",
        "Never polled": "從未輪詢",
        "This indicates the most likely endpoint switchport": "這將表示為最有可能的交換器連接埠端點",
        "Two-Factor unlocked.": "雙因素驗證已解鎖。",
        "Failed to unlock Two-Factor.": "雙因素驗證解鎖失敗。",
        "Two-Factor removed.": "雙因素驗證已移除。",
        "Failed to remove Two-Factor.": "雙因素驗證移除失敗。",
        "TwoFactor auth removed.": "雙因素驗證已移除。",
        "Too many two-factor failures, please contact administrator.": "雙因素驗證失敗次數太多，請年繫您的系統管理員，",
        "Too many two-factor failures, please wait {time} seconds": "雙因素驗證失敗次數太多，請等候 {time} 秒再試",
        "No Two-Factor Token entered.": "沒有輸入雙因素驗證權仗。",
        "No Two-Factor settings, how did you get here?": "沒有設定雙因素驗證，您要如何使用呢？",
        "Wrong Two-Factor Token.": "錯誤的雙因素驗證權仗。",
        "TwoFactor auth added.": "雙因素驗證已加入。",
        "User {username} created": "使用者 {username} 已建立",
        "Failed to create user": "建立使用者失敗",
        "Updated dashboard for {username}": "已更新 {username} 的資訊看版",
        "User {username} updated": "使用者 {username} 已更新",
        "Failed to update user {username}": "更新使用者 {username} 失敗",
        "User {username} deleted.": "使用者 {username} 已刪除。",
        "Device does not exist": "裝置不存在",
        "Port does not exist": "連接埠不存在",
        "App does not exist": "應用不存在",
        "Bill does not exist": "帳單不存在",
        "Munin plugin does not exist": "Munin 外掛程式不存在",
        "Ok": "確認",
        "Warning": "警告",
        "Critical": "嚴重",
        "Existing password did not match": "與既有的密碼不符",
        "The {attribute} field is required.": "{attribute} 是必要欄位。",
        "Edit User": "編輯使用者",
        "Unlock": "解除鎖定",
        "User exceeded failures": "使用者達到失敗上限",
        "Disable TwoFactor": "取消雙因素驗證",
        "No TwoFactor key generated for this user, Nothing to do.": "沒有為這個使用者產生雙因素驗證金鑰，暫不動作。",
        "Save": "儲存",
        "Cancel": "取消",
        "Unlocked Two Factor.": "解儲雙因素驗證鎖定。",
        "Failed to unlock Two Factor": "雙因素驗證解除鎖定失敗",
        "Removed Two Factor.": "雙因素驗證已經移除。",
        "Failed to remove Two Factor": "移除雙因素驗證失敗",
        "Real Name": "真實姓名",
        "Email": "郵件",
        "Description": "描述",
        "Level": "等級",
        "Normal": "正常",
        "Global Read": "全域讀取",
        "Admin": "Admin",
        "Demo": "Demo",
        "Dashboard": "資訊看版",
        "Password": "密碼",
        "Current Password": "目前密碼",
        "New Password": "新密碼",
        "Confirm Password": "確認新密碼",
        "Can Modify Password": "允許修改密碼",
        "Create User": "建立使用者",
        "Username": "使用者名稱",
        "Manage Users": "管理使用者",
        "ID": "ID",
        "Access": "存取權限",
        "Auth": "驗證",
        "Actions": "動作",
        "Edit": "編輯",
        "Delete": "刪除",
        "Manage Access": "管理存取權限",
        "Add User": "新增使用者",
        "Are you sure you want to delete ": "您確定要刪除 ",
        "The user could not be deleted": "這個使用者無法刪除",
        "Whoops, the web server could not write required files to the filesystem.": "噢，Web Server 無法寫入檔案到檔案系統。",
        "Running the following commands will fix the issue most of the time:": "Running the following commands will fix the issue most of the time:",
        "Whoops, looks like something went wrong. Check your librenms.log.": "噢，看起來發生了一些錯誤。請您查閱 librenms.log。",
        "Public Devices": "公開裝置",
        "System Status": "系統裝態",
        "Logon": "登入",
        "Device": "裝置",
        "Platform": "平台",
        "Uptime": "運作時間",
        "Location": "位置",
        "Status": "狀態",
        "Remember Me": "記住我",
        "Login": "登入",
        "Please enter auth token": "請輸入驗證權仗",
        "Submit": "提交",
        "Logout": "登出",
        "Locations": "位置",
        "Coordinates": "座標",
        "Devices": "裝置",
        "Network": "網路",
        "Servers": "伺服器",
        "Firewalls": "防火牆",
        "Down": "離線",
        "Save changes": "儲存變更",
        "N/A": "無",
        "Location must have devices to show graphs": "此位置必需有裝置才能顯示圖表",
        "Traffic": "流量",
        "Cannot delete locations used by devices": "無法刪除已有裝置使用的位置",
        "Location deleted": "位置已刪除",
        "Failed to delete location": "刪除位置失敗",
        "Timestamp": "時間戳記",
        "Source": "來源",
        "Message": "訊息",
        "Facility": "設備",
        "Total hosts": "所有主機",
        "ignored": "已忽略",
        "disabled": "已取消",
        "up": "上線",
        "warn": "警告",
        "down": "離線",
        "Total services": "所有服務",
        "Widget title": "小工具標題",
        "Default Title": "預設標題",
        "Columns": "欄位",
        "Markers": "標記",
        "Ports": "連接埠",
        "Resolution": "解析度",
        "Countries": "國家",
        "Provinces": "省份",
        "Metros": "Metros",
        "Region": "地區",
        "Help": "說明",
        "Stream": "串流",
        "All Messages": "所有訊息",
        "All Devices": "所有裝置",
        "Page Size": "頁面大小",
        "Time Range": "時間範圍",
        "Search all time": "搜尋所有時間",
        "Search last 5 minutes": "搜尋最近 5 分鐘內",
        "Search last 15 minutes": "搜尋最近 15 分鐘內",
        "Search last 30 minutes": "搜尋最近 30 分鐘內",
        "Search last 1 hour": "搜尋最近 1 小時內",
        "Search last 2 hours": "搜尋最近 2 小時內",
        "Search last 8 hours": "搜尋最近 8 小時內",
        "Search last 1 day": "搜尋最近 1 天內",
        "Search last 2 days": "搜尋最近 2 天內",
        "Search last 5 days": "搜尋最近 5 天內",
        "Search last 7 days": "搜尋最近 7 天內",
        "Search last 14 days": "搜尋最近 14 天內",
        "Search last 30 days": "搜尋最近 30 天內",
        "Custom title": "自訂標題",
        "Initial Latitude": "初始緯度",
        "ie. 51.4800 for Greenwich": "例如 51.4800 為格林威治",
        "Initial Longitude": "初始經度",
        "ie. 0 for Greenwich": "例如 0 為格林威治",
        "Initial Zoom": "初始 Zoom 縮放等級",
        "Grouping radius": "Grouping radius",
        "default 80": "預設 80",
        "Show devices": "顯示裝置",
        "Up + Down": "上線 + 離線",
        "Up": "上線",
        "Show Services": "顯示服務",
        "no": "否",
        "yes": "是",
        "Show Port Errors": "顯示連接埠錯誤",
        "Notes": "附註",
        "Custom title for widget": "自訂小工具標題",
        "Display type": "顯示類型",
        "boxes": "區塊",
        "compact": "精簡",
        "Uniform Tiles": "Uniform Tiles",
        "Tile size": "Tile size",
        "Disabled/ignored": "Disabled/ignored",
        "Show": "顯示",
        "Hide": "隱藏",
        "Mode select": "模式選擇",
        "only devices": "僅裝置",
        "only services": "僅服務",
        "devices and services": "裝置與服務",
        "Order By": "排序",
        "Hostname": "主機名稱",
        "Device group": "裝置群組",
        "Automatic Title": "自動產生標題",
        "Graph type": "圖表類型",
        "Select a graph": "選擇圖表",
        "Show legend": "顯示圖例",
        "Date range": "日期範圍",
        "One Hour": "1 小時",
        "Four Hours": "4 小時",
        "Six Hours": "6 小時",
        "Twelve Hours": "12 小時",
        "One Day": "1 天",
        "One Week": "1 週",
        "Two Weeks": "2 週",
        "One Month": "1 個月",
        "Two Months": "2 個月",
        "Three Months": "3 個月",
        "One Year": "1 年",
        "Two Years": "2 年",
        "Select a device": "選擇裝置",
        "Port": "連接埠",
        "Select a port": "選擇連接埠",
        "Application": "應用",
        "Select an application": "選擇應用",
        "Munin plugin": "Munin 外掛程式",
        "Select a Munin plugin": "選擇 Munin 外掛程式",
        "Bill": "帳單",
        "Select a bill": "選擇帳單",
        "Custom Aggregator(s)": "Custom Aggregator(s)",
        "Select or add one or more": "選擇或加入一或多個",
        "Select one or more": "選擇一或多個",
        "Top query": "排行榜查詢",
        "Response time": "回應時間",
        "Poller duration": "輪詢器花費時間",
        "Processor load": "處理器負載",
        "Memory usage": "記憶體使用量",
        "Disk usage": "磁碟使用量",
        "Sort order": "排序",
        "Ascending": "升冪",
        "Descending": "降冪",
        "Number of Devices": "裝置數量",
        "Last Polled (minutes)": "最後一次輪詢 (分鐘)",
        "Image URL": "圖片 URL",
        "Target URL": "連結目標 URL",
        "Show acknowledged": "顯示已通知",
        "not filtered": "不要篩選",
        "show only acknowledged": "僅顯示已通知",
        "hide acknowledged": "隱藏已通知",
        "Show only fired": "僅顯示已觸發",
        "show only fired alerts": "僅顯示已觸發警報",
        "Displayed severity": "顯示嚴重程度",
        "any severity": "任何嚴重程度",
        "or higher": "或更高者",
        "State": "狀態",
        "any state": "任何狀態",
        "All alerts": "所有警報",
        "Show Procedure field": "顯示處理欄位",
        "show": "顯示",
        "hide": "隱藏",
        "Sort alerts by": "排序警報依據",
        "timestamp, descending": "時間戳記、降冪",
        "severity, descending": "嚴重程度、降冪",
        "All devices": "所有裝置",
        "Event type": "事件類型",
        "All types": "所有類型",
        "Number of interfaces": "介面數量",
        "Last polled (minutes)": "最後一次輪詢 (分鐘)",
        "Interface type": "介面類型",
        "All Ports": "所有連接埠",
        "Total": "總計",
        "Ignored": "已忽略",
        "Disabled": "已停用",
        "Errored": "已錯誤",
        "Services": "服務",
        "No devices found within interval.": "在最近一次輪詢間隔內尚未找到裝置。",
        "Summary": "摘要",
        "Interface": "介面",
        "Total traffic": "流量總計",
        "Check your log for more details.": "查閱您的記錄檔以取得更詳細的資訊。",
        "If you need additional help, you can find how to get help at": "若您需要更多的說明，您可以在這裡找到更多相關資訊。",
        "Geo Locations": "地理位置",
        "All Locations": "所有位置",
        "Pollers": "輪詢器",
        "Groups": "群組",
        "Performance": "效能",
        "History": "歷程",
        "pagination": {
            "previous": "&laquo; 往前",
            "next": "往後 &raquo;"
        },
        "auth": {
            "failed": "這些憑證與我們的記錄不符。",
            "throttle": "嘗試登入次數過多。請稍候 {seconds} 秒再試。"
        },
        "validation": {
            "accepted": "{attribute} 須同意。",
            "active_url": "{attribute} 不是有效的 URL。",
            "after": "{attribute} 須為 {date} 之後的日期。",
            "after_or_equal": "{attribute} 須等於 {date} 或之後的日期。",
            "alpha": "The {attribute} may only contain letters.",
            "alpha_dash": "The {attribute} may only contain letters, numbers, dashes and underscores.",
            "alpha_num": "The {attribute} may only contain letters and numbers.",
            "array": "{attribute} 需為陣列。",
            "before": "{attribute} 須為 {date} 之前的日期。",
            "before_or_equal": "{attribute} 須等於 {date} 或之前的日期。",
            "between": {
                "numeric": "The {attribute} must be between {min} and {max}.",
                "file": "The {attribute} must be between {min} and {max} kilobytes.",
                "string": "The {attribute} must be between {min} and {max} characters.",
                "array": "The {attribute} must have between {min} and {max} items."
            },
            "boolean": "The {attribute} field must be true or false.",
            "confirmed": "The {attribute} confirmation does not match.",
            "date": "The {attribute} is not a valid date.",
            "date_equals": "The {attribute} must be a date equal to {date}.",
            "date_format": "The {attribute} does not match the format {format}.",
            "different": "The {attribute} and {other} must be different.",
            "digits": "The {attribute} must be {digits} digits.",
            "digits_between": "The {attribute} must be between {min} and {max} digits.",
            "dimensions": "The {attribute} has invalid image dimensions.",
            "distinct": "The {attribute} field has a duplicate value.",
            "email": "The {attribute} must be a valid email address.",
            "exists": "The selected {attribute} is invalid.",
            "file": "The {attribute} must be a file.",
            "filled": "The {attribute} field must have a value.",
            "gt": {
                "numeric": "The {attribute} must be greater than {value}.",
                "file": "The {attribute} must be greater than {value} kilobytes.",
                "string": "The {attribute} must be greater than {value} characters.",
                "array": "The {attribute} must have more than {value} items."
            },
            "gte": {
                "numeric": "The {attribute} must be greater than or equal {value}.",
                "file": "The {attribute} must be greater than or equal {value} kilobytes.",
                "string": "The {attribute} must be greater than or equal {value} characters.",
                "array": "The {attribute} must have {value} items or more."
            },
            "image": "The {attribute} must be an image.",
            "in": "The selected {attribute} is invalid.",
            "in_array": "The {attribute} field does not exist in {other}.",
            "integer": "The {attribute} must be an integer.",
            "ip": "The {attribute} must be a valid IP address.",
            "ipv4": "The {attribute} must be a valid IPv4 address.",
            "ipv6": "The {attribute} must be a valid IPv6 address.",
            "json": "The {attribute} must be a valid JSON string.",
            "lt": {
                "numeric": "The {attribute} must be less than {value}.",
                "file": "The {attribute} must be less than {value} kilobytes.",
                "string": "The {attribute} must be less than {value} characters.",
                "array": "The {attribute} must have less than {value} items."
            },
            "lte": {
                "numeric": "The {attribute} must be less than or equal {value}.",
                "file": "The {attribute} must be less than or equal {value} kilobytes.",
                "string": "The {attribute} must be less than or equal {value} characters.",
                "array": "The {attribute} must not have more than {value} items."
            },
            "max": {
                "numeric": "The {attribute} may not be greater than {max}.",
                "file": "The {attribute} may not be greater than {max} kilobytes.",
                "string": "The {attribute} may not be greater than {max} characters.",
                "array": "The {attribute} may not have more than {max} items."
            },
            "mimes": "The {attribute} must be a file of type: {values}.",
            "mimetypes": "The {attribute} must be a file of type: {values}.",
            "min": {
                "numeric": "The {attribute} must be at least {min}.",
                "file": "The {attribute} must be at least {min} kilobytes.",
                "string": "The {attribute} must be at least {min} characters.",
                "array": "The {attribute} must have at least {min} items."
            },
            "not_in": "The selected {attribute} is invalid.",
            "not_regex": "The {attribute} format is invalid.",
            "numeric": "The {attribute} must be a number.",
            "present": "The {attribute} field must be present.",
            "regex": "The {attribute} format is invalid.",
            "required": "The {attribute} field is required.",
            "required_if": "The {attribute} field is required when {other} is {value}.",
            "required_unless": "The {attribute} field is required unless {other} is in {values}.",
            "required_with": "The {attribute} field is required when {values} is present.",
            "required_with_all": "The {attribute} field is required when {values} are present.",
            "required_without": "The {attribute} field is required when {values} is not present.",
            "required_without_all": "The {attribute} field is required when none of {values} are present.",
            "same": "The {attribute} and {other} must match.",
            "size": {
                "numeric": "The {attribute} must be {size}.",
                "file": "The {attribute} must be {size} kilobytes.",
                "string": "The {attribute} must be {size} characters.",
                "array": "The {attribute} must contain {size} items."
            },
            "starts_with": "The {attribute} must start with one of the following: {values}",
            "string": "{attribute} 須是字串。",
            "timezone": "The {attribute} must be a valid zone.",
            "unique": "The {attribute} has already been taken.",
            "uploaded": "{attribute} 上傳失敗。",
            "url": "{attribute} 格式無效。",
            "uuid": "{attribute} 須是有效的 UUID。",
            "custom": {
                "attribute-name": {
                    "rule-name": "custom-message"
                }
            },
            "attributes": []
        },
        "sensors": {
            "airflow": {
                "short": "氣流",
                "long": "氣流",
                "unit": "cfm",
                "unit_long": "每分鐘標準立方呎"
            },
            "ber": {
                "short": "BER",
                "long": "位元錯誤率",
                "unit": "",
                "unit_long": ""
            },
            "charge": {
                "short": "電量",
                "long": "電量百分比",
                "unit": "%",
                "unit_long": "百分比"
            },
            "chromatic_dispersion": {
                "short": "色散",
                "long": "色散",
                "unit": "ps/nm/km",
                "unit_long": "Picoseconds per Nanometer per Kilometer"
            },
            "cooling": {
                "short": "Cooling",
                "long": "",
                "unit": "W",
                "unit_long": "瓦特"
            },
            "count": {
                "short": "Count",
                "long": "Count",
                "unit": "",
                "unit_long": ""
            },
            "current": {
                "short": "電流",
                "long": "電流",
                "unit": "A",
                "unit_long": "安培"
            },
            "dbm": {
                "short": "dBm",
                "long": "dBm",
                "unit": "dBm",
                "unit_long": "毫瓦分貝"
            },
            "delay": {
                "short": "延遲",
                "long": "延遲",
                "unit": "s",
                "unit_long": "秒"
            },
            "eer": {
                "short": "EER",
                "long": "能效比",
                "unit": "",
                "unit_long": ""
            },
            "fanspeed": {
                "short": "風扇轉速",
                "long": "風扇轉速",
                "unit": "RPM",
                "unit_long": "每分鐘旋轉次數"
            },
            "frequency": {
                "short": "頻率",
                "long": "頻率",
                "unit": "Hz",
                "unit_long": "赫茲"
            },
            "humidity": {
                "short": "濕度",
                "long": "濕度百分比",
                "unit": "%",
                "unit_long": "百分比"
            },
            "load": {
                "short": "負載",
                "long": "負載百分比",
                "unit": "%",
                "unit_long": "百分比"
            },
            "power": {
                "short": "電力",
                "long": "電力",
                "unit": "W",
                "unit_long": "瓦特"
            },
            "power_consumed": {
                "short": "消耗功率",
                "long": "消耗功率",
                "unit": "kWh",
                "unit_long": "千瓦小時"
            },
            "power_factor": {
                "short": "功率因數",
                "long": "功率因數",
                "unit": "",
                "unit_long": ""
            },
            "pressure": {
                "short": "壓力",
                "long": "壓力",
                "unit": "kPa",
                "unit_long": "千帕"
            },
            "quality_factor": {
                "short": "品質因子",
                "long": "品質因子",
                "unit": "",
                "unit_long": ""
            },
            "runtime": {
                "short": "Runtime",
                "long": "Runtime",
                "unit": "分",
                "unit_long": "分鐘"
            },
            "signal": {
                "short": "訊號",
                "long": "訊號",
                "unit": "dBm",
                "unit_long": "毫瓦分貝"
            },
            "snr": {
                "short": "SNR",
                "long": "訊號雜訊比",
                "unit": "dB",
                "unit_long": "分貝"
            },
            "state": {
                "short": "狀態",
                "long": "狀態",
                "unit": ""
            },
            "temperature": {
                "short": "溫度",
                "long": "溫度",
                "unit": "°C",
                "unit_long": "° 攝氏"
            },
            "voltage": {
                "short": "電壓",
                "long": "電壓",
                "unit": "V",
                "unit_long": "伏特"
            },
            "waterflow": {
                "short": "水流",
                "long": "水流",
                "unit": "l/m",
                "unit_long": "升每分鐘"
            }
        },
        "commands": {
            "user{add}": {
                "description": "新增一個本機使用者，只有在設定驗證使用 mysql 時才可以使用此使用者帳號登入",
                "arguments": {
                    "username": "使用者用來登入的名稱"
                },
                "options": {
                    "descr": "使用者描述",
                    "email": "使用者的郵件",
                    "password": "使用者的密碼，如果沒有提供，您將會收到提示",
                    "full-name": "使用者的全名",
                    "role": "將使用者指派至角色 {roles}"
                },
                "password-request": "請輸入使用者的密碼",
                "success": "已成功新增使用者: {username}",
                "wrong-auth": "警告，您將無法以這個使用者登入，因為您沒有使用 MySQL 驗證"
            }
        },
        "syslog": {
            "severity": [
                "緊急",
                "警報",
                "重大",
                "錯誤",
                "警告",
                "通知",
                "資訊",
                "除錯"
            ],
            "facility": [
                "kernel messages",
                "user-level messages",
                "mail-system",
                "system daemons",
                "security/authorization messages",
                "messages generated internally by syslogd",
                "line printer subsystem",
                "network news subsystem",
                "UUCP subsystem",
                "clock daemon",
                "security/authorization messages",
                "FTP daemon",
                "NTP subsystem",
                "log audit",
                "log alert",
                "clock daemon (note 2)",
                "local use 0  (local0)",
                "local use 1  (local1)",
                "local use 2  (local2)",
                "local use 3  (local3)",
                "local use 4  (local4)",
                "local use 5  (local5)",
                "local use 6  (local6)",
                "local use 7  (local7)"
            ]
        },
        "preferences": {
            "lang": "繁體中文"
        },
        "settings": {
            "readonly": "在 config.php 裡被設定成唯讀，請由 config.php 移除它來啟用。",
            "groups": {
                "alerting": "警報",
                "auth": "驗證",
                "external": "外部整合",
                "global": "全域",
                "os": "作業系統",
                "discovery": "探索",
                "poller": "輪詢器",
                "system": "系統",
                "webui": "Web UI"
            },
            "sections": {
                "alerting": {
                    "general": "一般警報設定",
                    "email": "電子郵件設定"
                },
                "auth": {
                    "general": "一般驗證設定",
                    "ad": "Active Directory 設定",
                    "ldap": "LDAP 設定"
                },
                "discovery": {
                    "general": "一般探索設定"
                },
                "external": {
                    "binaries": "執行檔位置",
                    "location": "位置資訊設定",
                    "graylog": "Graylog 整合",
                    "oxidized": "Oxidized 整合",
                    "peeringdb": "PeeringDB 整合",
                    "nfsen": "NfSen 整合",
                    "unix-agent": "Unix-Agent 整合"
                },
                "poller": {
                    "distributed": "分散式輪詢器",
                    "ping": "Ping",
                    "rrdtool": "RRDTool 設定",
                    "snmp": "SNMP"
                },
                "system": {
                    "cleanup": "清理",
                    "proxy": "Proxy",
                    "updates": "更新",
                    "server": "伺服器"
                },
                "webui": {
                    "availability-map": "可用性地圖設定",
                    "graph": "圖表設定",
                    "dashboard": "資訊看板設定",
                    "search": "搜尋設定",
                    "style": "樣式"
                }
            },
            "settings": {
                "active_directory": {
                    "users_purge": {
                        "description": "保留未登入使用者於",
                        "help": "設定使用者超過幾天沒有登入後，將會被 LibreNMS 自動刪除。設為 0 表示不會刪除，若使用者重新登入，將會重新建立帳戶。"
                    }
                },
                "addhost_alwayscheckip": {
                    "description": "新增裝置時檢察是否 IP 重複",
                    "help": "以 IP 加入主機時，會先檢查此 IP 是否已存在於系統上，若有則不予加入。若是以主機名稱方式加入時，則不會做此檢查。若設定為 True 時，則以主機名稱方式加入時亦做此檢查，以避免加入重複主機的意外發生。"
                },
                "alert": {
                    "ack_until_clear": {
                        "description": "預設認可值到警報解除選項",
                        "help": "預設認可值到警報解除"
                    },
                    "admins": {
                        "description": "向管理員發送警報",
                        "help": "管理員警報"
                    },
                    "default_copy": {
                        "description": "複製所有的郵件警報給預設連絡人",
                        "help": "複製所有的郵件警報給預設連絡人"
                    },
                    "default_if_none": {
                        "description": "無法在 WebUI 設定？",
                        "help": "如果沒有找到其它連絡人，請把郵件發送到預設連絡人"
                    },
                    "default_mail": {
                        "description": "預設連絡人",
                        "help": "預設連絡人郵件位址"
                    },
                    "default_only": {
                        "description": "只發送警報給預設連絡人",
                        "help": "只發送警報給預設郵件連絡人"
                    },
                    "disable": {
                        "description": "停用警報",
                        "help": "停止產生警報"
                    },
                    "fixed-contacts": {
                        "description": "Updates to contact email addresses not honored",
                        "help": "If TRUE any changes to sysContact or users emails will not be honoured whilst alert is active"
                    },
                    "globals": {
                        "description": "只發送警報給唯讀使用者",
                        "help": "只發送警報給唯讀管理員"
                    },
                    "syscontact": {
                        "description": "發送警報給 sysContact",
                        "help": "發送警報郵件給 SNMP 中的 sysContact"
                    },
                    "transports": {
                        "mail": {
                            "description": "啟用郵件警報",
                            "help": "啟用以郵件傳輸警報"
                        }
                    },
                    "tolerance_window": {
                        "description": "cron 容錯範圍",
                        "help": "Tolerance window in seconds"
                    },
                    "users": {
                        "description": "發送警報給一般使用者",
                        "help": "警報通知一般使用者"
                    }
                },
                "alert_log_purge": {
                    "description": "警報記錄項目大於",
                    "help": "Cleanup done by daily.sh"
                },
                "allow_duplicate_sysName": {
                    "description": "允許重複 sysName",
                    "help": "By default duplicate sysNames are disabled from being added to prevent a device with multiple interfaces from being added multiple times"
                },
                "allow_unauth_graphs": {
                    "description": "允許未登入存取圖表",
                    "help": "Allows any one to access graphs without login"
                },
                "allow_unauth_graphs_cidr": {
                    "description": "Allow the given networks graph access",
                    "help": "Allow the given networks unauthenticated graph access (does not apply when unauthenticated graphs is enabled)"
                },
                "api_demo": {
                    "description": "這是展示"
                },
                "apps": {
                    "powerdns-recursor": {
                        "api-key": {
                            "description": "API key for PowerDNS Recursor",
                            "help": "API key for the PowerDNS Recursor app when connecting directly"
                        },
                        "https": {
                            "description": "PowerDNS Recursor use HTTPS?",
                            "help": "Use HTTPS instead of HTTP for the PowerDNS Recursor app when connecting directly"
                        },
                        "port": {
                            "description": "PowerDNS Recursor port",
                            "help": "TCP port to use for the PowerDNS Recursor app when connecting directly"
                        }
                    }
                },
                "astext": {
                    "description": "Key to hold cache of autonomous systems descriptions"
                },
                "auth_ad_base_dn": {
                    "description": "基礎 DN",
                    "help": "groups and users must be under this dn. Example: dc=example,dc=com"
                },
                "auth_ad_check_certificates": {
                    "description": "檢查憑證",
                    "help": "Check certificates for validity. Some servers use self signed certificates, disabling this allows those."
                },
                "auth_ad_group_filter": {
                    "description": "LDAP 群組篩選器",
                    "help": "Active Directory LDAP filter for selecting groups"
                },
                "auth_ad_groups": {
                    "description": "群組存取權限",
                    "help": "定義群組具有的存取權限與等級"
                },
                "auth_ad_user_filter": {
                    "description": "LDAP 使用者篩選",
                    "help": "Active Directory LDAP filter for selecting users"
                },
                "auth_ldap_attr": {
                    "uid": {
                        "description": "Attribute to check username against",
                        "help": "Attribute used to identify users by username"
                    }
                },
                "auth_ldap_binddn": {
                    "description": "繫結 DN (覆寫繫結使用者名稱)",
                    "help": "Full DN of bind user"
                },
                "auth_ldap_bindpassword": {
                    "description": "繫結密碼",
                    "help": "Password for bind user"
                },
                "auth_ldap_binduser": {
                    "description": "繫結使用者",
                    "help": "Used to query the LDAP server when no user is logged in (alerts, API, etc)"
                },
                "auth_ad_binddn": {
                    "description": "繫結 DN (覆寫繫結使用者名稱)",
                    "help": "Full DN of bind user"
                },
                "auth_ad_bindpassword": {
                    "description": "繫結密碼",
                    "help": "Password for bind user"
                },
                "auth_ad_binduser": {
                    "description": "繫結使用者名稱",
                    "help": "Used to query the AD server when no user is logged in (alerts, API, etc)"
                },
                "auth_ldap_cache_ttl": {
                    "description": "LDAP 快取有效期",
                    "help": "Temporarily stores LDAP query results.  Improves speeds, but the data may be stale."
                },
                "auth_ldap_debug": {
                    "description": "顯示偵錯資訊",
                    "help": "Shows debug information.  May expose private information, do not leave enabled."
                },
                "auth_ldap_emailattr": {
                    "description": "郵件屬性"
                },
                "auth_ldap_group": {
                    "description": "存取群組 DN",
                    "help": "Distinguished name for a group to give normal level access. Example: cn=groupname,ou=groups,dc=example,dc=com"
                },
                "auth_ldap_groupbase": {
                    "description": "群組基礎 DN",
                    "help": "Distinguished name to search for groups Example: ou=group,dc=example,dc=com"
                },
                "auth_ldap_groupmemberattr": {
                    "description": "Group member attribute"
                },
                "auth_ldap_groupmembertype": {
                    "description": "Find group members by",
                    "options": {
                        "username": "使用者名稱",
                        "fulldn": "Full DN (using prefix and suffix)",
                        "puredn": "DN 搜尋 (使用 uid 屬性搜尋)"
                    }
                },
                "auth_ldap_groups": {
                    "description": "Group access",
                    "help": "Define groups that have access and level"
                },
                "auth_ldap_port": {
                    "description": "LDAP 連接埠",
                    "help": "Port to connect to servers on. For LDAP it should be 389, for LDAPS it should be 636"
                },
                "auth_ldap_prefix": {
                    "description": "User prefix",
                    "help": "Used to turn a username into a distinguished name"
                },
                "auth_ldap_server": {
                    "description": "LDAP 伺服器",
                    "help": "Set server(s), space separated. Prefix with ldaps:// for ssl"
                },
                "auth_ldap_starttls": {
                    "description": "使用 STARTTLS",
                    "help": "Use STARTTLS to secure the connection.  Alternative to LDAPS.",
                    "options": {
                        "disabled": "停用",
                        "optional": "選用",
                        "required": "必要"
                    }
                },
                "auth_ldap_suffix": {
                    "description": "User suffix",
                    "help": "Used to turn a username into a distinguished name"
                },
                "auth_ldap_timeout": {
                    "description": "連線逾時",
                    "help": "If one or more servers are unresponsive, higher timeouts will cause slow access. To low may cause connection failures in some cases"
                },
                "auth_ldap_uid_attribute": {
                    "description": "唯一 ID 屬性",
                    "help": "LDAP attribute to use to identify users, must be numeric"
                },
                "auth_ldap_userdn": {
                    "description": "使用全名 DN",
                    "help": "Uses a user's full DN as the value of the member attribute in a group instead of member: username using the prefix and suffix. (it’s member: uid=username,ou=groups,dc=domain,dc=com)"
                },
                "auth_ldap_version": {
                    "description": "LDAP 版本",
                    "help": "用來與 LDAP Server 進行連接的版本，通常應是 v3",
                    "options": {
                        "2": "2",
                        "3": "3"
                    }
                },
                "auth_mechanism": {
                    "description": "授權方法 (慎選!)",
                    "help": "授權方法。注意，若設定錯誤將導致您無法登入系統。若真的發生，您可以手動將 config.php 的設定改回 $config['auth_mechanism'] = 'mysql';",
                    "options": {
                        "mysql": "MySQL (預設)",
                        "active_directory": "Active Directory",
                        "ldap": "LDAP",
                        "radius": "Radius",
                        "http-auth": "HTTP 驗證",
                        "ad-authorization": "外部 AD 驗證",
                        "ldap-authorization": "外部 LDAP 驗證",
                        "sso": "單一簽入 SSO"
                    }
                },
                "auth_remember": {
                    "description": "記住我的期限",
                    "help": "Number of days to keep a user logged in when checking the remember me checkbox at log in."
                },
                "authlog_purge": {
                    "description": "驗證記錄項目大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "base_url": {
                    "description": "指定 URL",
                    "help": "This should *only* be set if you want to *force* a particular hostname/port. It will prevent the web interface being usable form any other hostname"
                },
                "device_perf_purge": {
                    "description": "裝置效能項目大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "distributed_poller": {
                    "description": "啟用分散式輪詢 (需要額外設定)",
                    "help": "Enable distributed polling system wide. This is intended for load sharing, not remote polling. You must read the documentation for steps to enable: https://docs.librenms.org/Extensions/Distributed-Poller/"
                },
                "distributed_poller_group": {
                    "description": "預設輪詢器群組",
                    "help": "The default poller group all pollers should poll if none is set in config.php"
                },
                "distributed_poller_memcached_host": {
                    "description": "Memcached 主機",
                    "help": "The hostname or ip for the memcached server. This is required for poller_wrapper.py and daily.sh locking."
                },
                "distributed_poller_memcached_port": {
                    "description": "Memcached 連接埠",
                    "help": "The port for the memcached server. Default is 11211"
                },
                "email_auto_tls": {
                    "description": "啟用 / 停用自動 TLS 支援",
                    "options": {
                        "true": "是",
                        "false": "否"
                    }
                },
                "email_backend": {
                    "description": "寄送郵件方式",
                    "help": "The backend to use for sending email, can be mail, sendmail or SMTP",
                    "options": {
                        "mail": "mail",
                        "sendmail": "sendmail",
                        "smtp": "SMTP"
                    }
                },
                "email_from": {
                    "description": "寄件者信箱位址",
                    "help": "Email address used for sending emails (from)"
                },
                "email_html": {
                    "description": "使用 HTML 格式",
                    "help": "寄送 HTML 格式的郵件"
                },
                "email_sendmail_path": {
                    "description": "若啟用此選項，sendmail 所在的位置"
                },
                "email_smtp_auth": {
                    "description": "啟用 / 停用 SMTP 驗證"
                },
                "email_smtp_host": {
                    "description": "指定寄信用的 SMTP 主機"
                },
                "email_smtp_password": {
                    "description": "SMTP 驗證密碼"
                },
                "email_smtp_port": {
                    "description": "SMTP 連接埠設定"
                },
                "email_smtp_secure": {
                    "description": "啟用 / 停用加密 (使用 TLS 或 SSL)",
                    "options": {
                        "": "停用",
                        "tls": "TLS",
                        "ssl": "SSL"
                    }
                },
                "email_smtp_timeout": {
                    "description": "SMTP 逾時設定"
                },
                "email_smtp_username": {
                    "description": "SMTP 驗證使用者名稱"
                },
                "email_user": {
                    "description": "寄件者名稱",
                    "help": "Name used as part of the from address"
                },
                "eventlog_purge": {
                    "description": "事件記錄大於 (天)",
                    "help": "由 daily.sh 進行清理作業"
                },
                "favicon": {
                    "description": "Favicon",
                    "help": "取代預設 Favicon."
                },
                "fping": {
                    "description": "fping 路徑"
                },
                "fping6": {
                    "description": "fping6 路徑"
                },
                "fping_options": {
                    "count": {
                        "description": "fping 次數",
                        "help": "The number of pings to send when checking if a host is up or down via icmp"
                    },
                    "interval": {
                        "description": "fping 間隔",
                        "help": "The amount of milliseconds to wait between pings"
                    },
                    "timeout": {
                        "description": "fping 逾時",
                        "help": "The amount of milliseconds to wait for an echo response before giving up"
                    }
                },
                "geoloc": {
                    "api_key": {
                        "description": "地理編碼 API 金鑰",
                        "help": "Geocoding API Key (Required to function)"
                    },
                    "engine": {
                        "description": "地理編碼引擎",
                        "options": {
                            "google": "Google Maps",
                            "openstreetmap": "OpenStreetMap",
                            "mapquest": "MapQuest",
                            "bing": "Bing Maps"
                        }
                    }
                },
                "graylog": {
                    "base_uri": {
                        "description": "Base URI",
                        "help": "Override the base uri in the case you have modified the Graylog default."
                    },
                    "device-page": {
                        "loglevel": {
                            "description": "Device Overview Log Level",
                            "help": "Sets the maximum log level shown on the device overview page."
                        },
                        "rowCount": {
                            "description": "Device Overview Row Count",
                            "help": "Sets the number of rows show on the device overview page."
                        }
                    },
                    "password": {
                        "description": "密碼",
                        "help": "Password for accessing Graylog API."
                    },
                    "port": {
                        "description": "連接埠",
                        "help": "The port used to access the Graylog API. If none give, it will be 80 for http and 443 for https."
                    },
                    "server": {
                        "description": "伺服器",
                        "help": "The ip or hostname of the Graylog server API endpoint."
                    },
                    "timezone": {
                        "description": "顯示時區",
                        "help": "Graylog times are stored in GMT, this setting will change the displayed timezone. The value must be a valid PHP timezone."
                    },
                    "username": {
                        "description": "使用者名稱",
                        "help": "Username for accessing the Graylog API."
                    },
                    "version": {
                        "description": "版本",
                        "help": "This is used to automatically create the base_uri for the Graylog API. If you have modified the API uri from the default, set this to other and specify your base_uri."
                    }
                },
                "http_proxy": {
                    "description": "HTTP(S) 代理",
                    "help": "Set this as a fallback if http_proxy or https_proxy environment variable is not available."
                },
                "ipmitool": {
                    "description": "ipmtool 路徑"
                },
                "login_message": {
                    "description": "登入訊息",
                    "help": "顯示於登入頁面"
                },
                "mono_font": {
                    "description": "Monospaced 字型"
                },
                "mtr": {
                    "description": "mtr 路徑"
                },
                "mydomain": {
                    "description": "主要網域",
                    "help": "This domain is used for network auto-discovery and other processes. LibreNMS will attempt to append it to unqualified hostnames."
                },
                "nfsen_enable": {
                    "description": "啟用 NfSen",
                    "help": "啟用 NfSen 整合"
                },
                "nfsen_rrds": {
                    "description": "NfSen RRD 目錄",
                    "help": "This value specifies where your NFSen RRD files are located."
                },
                "nfsen_subdirlayout": {
                    "description": "設定 NfSen 子目錄配置",
                    "help": "This must match the subdir layout you have set in NfSen. 1 is the default."
                },
                "nfsen_last_max": {
                    "description": "Last Max"
                },
                "nfsen_top_max": {
                    "description": "Top Max",
                    "help": "Max topN value for stats"
                },
                "nfsen_top_N": {
                    "description": "Top N"
                },
                "nfsen_top_default": {
                    "description": "Default Top N"
                },
                "nfsen_stat_default": {
                    "description": "Default Stat"
                },
                "nfsen_order_default": {
                    "description": "Default Order"
                },
                "nfsen_last_default": {
                    "description": "Default Last"
                },
                "nfsen_lasts": {
                    "description": "Default Last Options"
                },
                "nfsen_split_char": {
                    "description": "分隔字元",
                    "help": "This value tells us what to replace the full stops `.` in the devices hostname with. Usually: `_`"
                },
                "nfsen_suffix": {
                    "description": "檔案名稱首碼",
                    "help": "This is a very important bit as device names in NfSen are limited to 21 characters. This means full domain names for devices can be very problematic to squeeze in, so therefor this chunk is usually removed."
                },
                "nmap": {
                    "description": "nmap 路徑"
                },
                "own_hostname": {
                    "description": "LibreNMS 主機名稱",
                    "help": "Should be set to the hostname/ip the librenms server is added as"
                },
                "oxidized": {
                    "default_group": {
                        "description": "Set the default group returned"
                    },
                    "enabled": {
                        "description": "啟用 Oxidized 支援"
                    },
                    "features": {
                        "versioning": {
                            "description": "啟用組態版本存取",
                            "help": "Enable Oxidized config versioning (requires git backend)"
                        }
                    },
                    "group_support": {
                        "description": "Enable the return of groups to Oxidized"
                    },
                    "reload_nodes": {
                        "description": "在每次新增裝置後，重新載入 Oxidized 節點清單"
                    },
                    "url": {
                        "description": "您的 Oxidized API URL",
                        "help": "Oxidized API url (For example: http://127.0.0.1{8888})"
                    }
                },
                "peeringdb": {
                    "enabled": {
                        "description": "啟用 PeeringDB 反查",
                        "help": "起用 PeeringDB lookup (資料將於由 daily.sh 進行下載)"
                    }
                },
                "perf_times_purge": {
                    "description": "輪詢器效能記錄項目大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "ping": {
                    "description": "ping 路徑"
                },
                "ports_fdb_purge": {
                    "description": "連接埠 FDB 項目大於",
                    "help": "Cleanup done by daily.sh"
                },
                "ports_purge": {
                    "description": "連接埠大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "public_status": {
                    "description": "公開狀態顯示",
                    "help": "允許不登入的情況下，顯示裝置的狀態資訊。"
                },
                "rrd": {
                    "heartbeat": {
                        "description": "變更 rrd 活動訊號值 (預設 600)"
                    },
                    "step": {
                        "description": "變更 rrd 間距值 (預設 300)"
                    }
                },
                "rrd_dir": {
                    "description": "RRD 位置",
                    "help": "Location of rrd files.  Default is rrd inside the LibreNMS directory.  Changing this setting does not move the rrd files."
                },
                "rrd_purge": {
                    "description": "RRD 檔案項目大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "rrd_rra": {
                    "description": "RRD 格式設定",
                    "help": "These cannot be changed without deleting your existing RRD files. Though one could conceivably increase or decrease the size of each RRA if one had performance problems or if one had a very fast I/O subsystem with no performance worries."
                },
                "rrdcached": {
                    "description": "啟用 rrdcached (socket)",
                    "help": "Enables rrdcached by setting the location of the rrdcached socket. Can be unix or network socket (unix:/run/rrdcached.sock or localhost{42217})"
                },
                "rrdtool": {
                    "description": "rrdtool 路徑"
                },
                "rrdtool_tune": {
                    "description": "調整所有 rrd 連接埠檔案使用最大值",
                    "help": "自動調整 rrd 連接埠檔案的最大值"
                },
                "sfdp": {
                    "description": "sfdp 路徑"
                },
                "shorthost_target_length": {
                    "description": "Shortened hostname maximum length",
                    "help": "Shrinks hostname to maximum length, but always complete subdomain parts"
                },
                "site_style": {
                    "description": "設定站台 css 樣式",
                    "options": {
                        "blue": "Blue",
                        "dark": "Dark",
                        "light": "Light",
                        "mono": "Mono"
                    }
                },
                "snmp": {
                    "transports": {
                        "description": "傳輸 (優先順序)",
                        "help": "Select enabled transports and order them as you want them to be tried."
                    },
                    "version": {
                        "description": "版本 (優先順序)",
                        "help": "Select enabled versions and order them as you want them to be tried."
                    },
                    "community": {
                        "description": "社群 (優先順序)",
                        "help": "Enter community strings for v1 and v2c and order them as you want them to be tried"
                    },
                    "max_repeaters": {
                        "description": "重複擷取最多次數",
                        "help": "Set repeaters to use for SNMP bulk requests"
                    },
                    "port": {
                        "description": "連接埠",
                        "help": "Set the tcp/udp port to be used for SNMP"
                    },
                    "v3": {
                        "description": "SNMP v3 驗證 (優先順序)",
                        "help": "Set up v3 authentication variables and order them as you want them to be tried",
                        "auth": "驗證",
                        "crypto": "加密",
                        "fields": {
                            "authalgo": "演算法",
                            "authlevel": "鄧級",
                            "authname": "使用者名稱",
                            "authpass": "密碼",
                            "cryptoalgo": "演算法",
                            "cryptopass": "演算法密碼"
                        },
                        "level": {
                            "noAuthNoPriv": "No Authentication, No Privacy",
                            "authNoPriv": "Authentication, No Privacy",
                            "authPriv": "Authentication and Privacy"
                        }
                    }
                },
                "snmpbulkwalk": {
                    "description": "snmpbulkwalk 路徑"
                },
                "snmpget": {
                    "description": "snmpget 路徑"
                },
                "snmpgetnext": {
                    "description": "snmpgetnext 路徑"
                },
                "snmptranslate": {
                    "description": "snmptranslate 路徑"
                },
                "snmpwalk": {
                    "description": "snmpwalk 路徑"
                },
                "syslog_filter": {
                    "description": "Filter syslog messages containing"
                },
                "syslog_purge": {
                    "description": "Syslog 項目大於 (天)",
                    "help": "Cleanup done by daily.sh"
                },
                "title_image": {
                    "description": "標題圖片",
                    "help": "Overrides the default Title Image."
                },
                "traceroute": {
                    "description": "traceroute 路徑"
                },
                "traceroute6": {
                    "description": "traceroute6 路徑"
                },
                "unix-agent": {
                    "connection-timeout": {
                        "description": "Unix-agent 連線逾時"
                    },
                    "port": {
                        "description": "預設 unix-agent 連接埠",
                        "help": "unix-agent (check_mk) 預設連接埠號碼"
                    },
                    "read-timeout": {
                        "description": "Unix-agent 讀取逾時"
                    }
                },
                "update": {
                    "description": "啟用更新 ./daily.sh"
                },
                "update_channel": {
                    "description": "設定更新頻道",
                    "options": {
                        "master": "master",
                        "release": "release"
                    }
                },
                "virsh": {
                    "description": "virsh 路徑"
                },
                "webui": {
                    "availability_map_box_size": {
                        "description": "可用性區塊寬度",
                        "help": "Input desired tile width in pixels for box size in full view"
                    },
                    "availability_map_compact": {
                        "description": "可用性地圖精簡模式",
                        "help": "Availability map view with small indicators"
                    },
                    "availability_map_sort_status": {
                        "description": "依狀態排序",
                        "help": "以狀態做為裝置與服務的排序"
                    },
                    "availability_map_use_device_groups": {
                        "description": "使用裝置群組篩選器",
                        "help": "啟用裝置群組篩選器"
                    },
                    "default_dashboard_id": {
                        "description": "預設資訊看板",
                        "help": "對於沒有設定預設資訊看板的使用者，所要顯示的預設資訊看板"
                    },
                    "dynamic_graphs": {
                        "description": "啟用動態群組",
                        "help": "Enable dynamic graphs, enables zooming and panning on graphs"
                    },
                    "global_search_result_limit": {
                        "description": "設定搜尋結果筆數上限",
                        "help": "全域搜尋結果限制"
                    },
                    "graph_stacked": {
                        "description": "使用堆疊圖表",
                        "help": "Display stacked graphs instead of inverted graphs"
                    },
                    "graph_type": {
                        "description": "設定圖表類型",
                        "help": "設定預設圖表類型",
                        "options": {
                            "png": "PNG",
                            "svg": "SVG"
                        }
                    },
                    "min_graph_height": {
                        "description": "設定圖表最小高度",
                        "help": "圖表最小高度 (預設: 300)"
                    }
                },
                "whois": {
                    "description": "whois 路徑"
                }
            },
            "twofactor": {
                "description": "啟用雙因素驗證",
                "help": "Enables the built in Two-Factor authentication. You must set up each account to make it active."
            },
            "units": {
                "days": "日",
                "ms": "微秒",
                "seconds": "秒"
            },
            "validate": {
                "boolean": "{value} is not a valid boolean",
                "email": "{value} is not a valid email",
                "integer": "{value} is not an integer",
                "password": "The password is incorrect",
                "select": "{value} is not an allowed value",
                "text": "{value} is not allowed",
                "array": "Invalid format"
            }
        },
        "passwords": {
            "password": "密碼至少需要六個字元，並且要確認兩者相符。",
            "reset": "您的密碼已重置。",
            "sent": "已經寄送密碼重置連結至您的電子郵件信箱。",
            "token": "此密碼重置權仗無效。",
            "user": "找不到使用者的電子郵件位址。"
        },
        "wireless": {
            "ap-count": {
                "short": "AP 數量",
                "long": "AP 數量",
                "unit": ""
            },
            "clients": {
                "short": "用戶端",
                "long": "用戶端數量",
                "unit": ""
            },
            "capacity": {
                "short": "容量",
                "long": "容量",
                "unit": "%"
            },
            "ccq": {
                "short": "CCQ",
                "long": "客戶端連線品質",
                "unit": "%"
            },
            "errors": {
                "short": "錯誤",
                "long": "錯誤數量",
                "unit": ""
            },
            "error-ratio": {
                "short": "錯誤率",
                "long": "位元/封包錯誤率",
                "unit": "%"
            },
            "error-rate": {
                "short": "BER",
                "long": "位元錯誤率",
                "unit": "bps"
            },
            "frequency": {
                "short": "頻率",
                "long": "頻率",
                "unit": "MHz"
            },
            "distance": {
                "short": "距離",
                "long": "距離",
                "unit": "km"
            },
            "mse": {
                "short": "MSE",
                "long": "平均誤差",
                "unit": "dB"
            },
            "noise-floor": {
                "short": "背景雜訊",
                "long": "背景雜訊",
                "unit": "dBm/Hz"
            },
            "power": {
                "short": "電力/訊號",
                "long": "TX/RX 電力或訊號",
                "unit": "dBm"
            },
            "quality": {
                "short": "品質",
                "long": "品質",
                "unit": "%"
            },
            "rate": {
                "short": "傳送率",
                "long": "TX/RX 傳送率",
                "unit": "bps"
            },
            "rssi": {
                "short": "RSSI",
                "long": "接收訊號強度指標",
                "unit": "dBm"
            },
            "snr": {
                "short": "SNR",
                "long": "訊號噪訊比",
                "unit": "dB"
            },
            "ssr": {
                "short": "SSR",
                "long": "訊號強度比",
                "unit": "dB"
            },
            "utilization": {
                "short": "使用率",
                "long": "使用率",
                "unit": "%"
            },
            "xpi": {
                "short": "XPI",
                "long": "交互極化干擾",
                "unit": "dB"
            }
        }
    }
}

})));