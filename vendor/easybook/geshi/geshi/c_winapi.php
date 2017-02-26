<?php
/*************************************************************************************
 * c_winapi.php
 * -----
 * Author: Benny Baumann (BenBE@geshi.org)
 * Contributors:
 *  - Jack Lloyd (lloyd@randombit.net)
 *  - Michael Mol (mikemol@gmail.com)
 * Copyright: (c) 2012 Benny Baumann (http://qbnz.com/highlighter/)
 * Release Version: 1.0.8.11
 * Date Started: 2012/08/12
 *
 * C (WinAPI) language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2009/01/22 (1.0.8.3)
 *   -  Made keywords case-sensitive.
 * 2008/05/23 (1.0.7.22)
 *   -  Added description of extra language features (SF#1970248)
 * 2004/XX/XX (1.0.4)
 *   -  Added a couple of new keywords (Jack Lloyd)
 * 2004/11/27 (1.0.3)
 *   -  Added support for multiple object splitters
 * 2004/10/27 (1.0.2)
 *   -  Added support for URLs
 * 2004/08/05 (1.0.1)
 *   -  Added support for symbols
 * 2004/07/14 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2009/02/08)
 * -------------------------
 *  -  Get a list of inbuilt functions to add (and explore C more
 *     to complete this rather bare language file
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'C (WinAPI)',
    'COMMENT_SINGLE' => array(1 => '//', 2 => '#'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(
        //Multiline-continued single-line comments
        1 => '/\/\/(?:\\\\\\\\|\\\\\\n|.)*$/m',
        //Multiline-continued preprocessor define
        2 => '/#(?:\\\\\\\\|\\\\\\n|.)*$/m'
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'ESCAPE_REGEXP' => array(
        //Simple Single Char Escapes
        1 => "#\\\\[\\\\abfnrtv\'\"?\n]#i",
        //Hexadecimal Char Specs
        2 => "#\\\\x[\da-fA-F]{2}#",
        //Hexadecimal Char Specs
        3 => "#\\\\u[\da-fA-F]{4}#",
        //Hexadecimal Char Specs
        4 => "#\\\\U[\da-fA-F]{8}#",
        //Octal Char Specs
        5 => "#\\\\[0-7]{1,3}#"
        ),
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE | GESHI_NUMBER_BIN_PREFIX_0B |
        GESHI_NUMBER_OCT_PREFIX | GESHI_NUMBER_HEX_PREFIX | GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(
        1 => array(
            'if', 'return', 'while', 'case', 'continue', 'default',
            'do', 'else', 'for', 'switch', 'goto'
            ),
        2 => array(
            'null', 'false', 'break', 'true', 'function', 'enum', 'extern', 'inline'
            ),
        3 => array(
            // assert.h
            'assert',

            //complex.h
            'cabs', 'cacos', 'cacosh', 'carg', 'casin', 'casinh', 'catan',
            'catanh', 'ccos', 'ccosh', 'cexp', 'cimag', 'cis', 'clog', 'conj',
            'cpow', 'cproj', 'creal', 'csin', 'csinh', 'csqrt', 'ctan', 'ctanh',

            //ctype.h
            'digittoint', 'isalnum', 'isalpha', 'isascii', 'isblank', 'iscntrl',
            'isdigit', 'isgraph', 'islower', 'isprint', 'ispunct', 'isspace',
            'isupper', 'isxdigit', 'toascii', 'tolower', 'toupper',

            //inttypes.h
            'imaxabs', 'imaxdiv', 'strtoimax', 'strtoumax', 'wcstoimax',
            'wcstoumax',

            //locale.h
            'localeconv', 'setlocale',

            //math.h
            'acos', 'asin', 'atan', 'atan2', 'ceil', 'cos', 'cosh', 'exp',
            'fabs', 'floor', 'frexp', 'ldexp', 'log', 'log10', 'modf', 'pow',
            'sin', 'sinh', 'sqrt', 'tan', 'tanh',

            //setjmp.h
            'longjmp', 'setjmp',

            //signal.h
            'raise',

            //stdarg.h
            'va_arg', 'va_copy', 'va_end', 'va_start',

            //stddef.h
            'offsetof',

            //stdio.h
            'clearerr', 'fclose', 'fdopen', 'feof', 'ferror', 'fflush', 'fgetc',
            'fgetpos', 'fgets', 'fopen', 'fprintf', 'fputc', 'fputchar',
            'fputs', 'fread', 'freopen', 'fscanf', 'fseek', 'fsetpos', 'ftell',
            'fwrite', 'getc', 'getch', 'getchar', 'gets', 'perror', 'printf',
            'putc', 'putchar', 'puts', 'remove', 'rename', 'rewind', 'scanf',
            'setbuf', 'setvbuf', 'snprintf', 'sprintf', 'sscanf', 'tmpfile',
            'tmpnam', 'ungetc', 'vfprintf', 'vfscanf', 'vprintf', 'vscanf',
            'vsprintf', 'vsscanf',

            //stdlib.h
            'abort', 'abs', 'atexit', 'atof', 'atoi', 'atol', 'bsearch',
            'calloc', 'div', 'exit', 'free', 'getenv', 'itoa', 'labs', 'ldiv',
            'ltoa', 'malloc', 'qsort', 'rand', 'realloc', 'srand', 'strtod',
            'strtol', 'strtoul', 'system',

            //string.h
            'memchr', 'memcmp', 'memcpy', 'memmove', 'memset', 'strcat',
            'strchr', 'strcmp', 'strcoll', 'strcpy', 'strcspn', 'strerror',
            'strlen', 'strncat', 'strncmp', 'strncpy', 'strpbrk', 'strrchr',
            'strspn', 'strstr', 'strtok', 'strxfrm',

            //time.h
            'asctime', 'clock', 'ctime', 'difftime', 'gmtime', 'localtime',
            'mktime', 'strftime', 'time',

            //wchar.h
            'btowc', 'fgetwc', 'fgetws', 'fputwc', 'fputws', 'fwide',
            'fwprintf', 'fwscanf', 'getwc', 'getwchar', 'mbrlen', 'mbrtowc',
            'mbsinit', 'mbsrtowcs', 'putwc', 'putwchar', 'swprintf', 'swscanf',
            'ungetwc', 'vfwprintf', 'vswprintf', 'vwprintf', 'wcrtomb',
            'wcscat', 'wcschr', 'wcscmp', 'wcscoll', 'wcscpy', 'wcscspn',
            'wcsftime', 'wcslen', 'wcsncat', 'wcsncmp', 'wcsncpy', 'wcspbrk',
            'wcsrchr', 'wcsrtombs', 'wcsspn', 'wcsstr', 'wcstod', 'wcstok',
            'wcstol', 'wcstoul', 'wcsxfrm', 'wctob', 'wmemchr', 'wmemcmp',
            'wmemcpy', 'wmemmove', 'wmemset', 'wprintf', 'wscanf',

            //wctype.h
            'iswalnum', 'iswalpha', 'iswcntrl', 'iswctype', 'iswdigit',
            'iswgraph', 'iswlower', 'iswprint', 'iswpunct', 'iswspace',
            'iswupper', 'iswxdigit', 'towctrans', 'towlower', 'towupper',
            'wctrans', 'wctype'
            ),
        4 => array(
            'auto', 'char', 'const', 'double',  'float', 'int', 'long',
            'register', 'short', 'signed', 'sizeof', 'static', 'struct',
            'typedef', 'union', 'unsigned', 'void', 'volatile', 'wchar_t',

            'int8', 'int16', 'int32', 'int64',
            'uint8', 'uint16', 'uint32', 'uint64',

            'int_fast8_t', 'int_fast16_t', 'int_fast32_t', 'int_fast64_t',
            'uint_fast8_t', 'uint_fast16_t', 'uint_fast32_t', 'uint_fast64_t',

            'int_least8_t', 'int_least16_t', 'int_least32_t', 'int_least64_t',
            'uint_least8_t', 'uint_least16_t', 'uint_least32_t', 'uint_least64_t',

            'int8_t', 'int16_t', 'int32_t', 'int64_t',
            'uint8_t', 'uint16_t', 'uint32_t', 'uint64_t',

            'intmax_t', 'uintmax_t', 'intptr_t', 'uintptr_t',
            'size_t', 'off_t'
            ),
        // Public API
        5 => array(
            'AssignProcessToJobObject', 'CommandLineToArgvW', 'ConvertThreadToFiber',
            'CreateFiber', 'CreateJobObjectA', 'CreateJobObjectW', 'CreateProcessA',
            'CreateProcessAsUserA', 'CreateProcessAsUserW', 'CreateProcessW',
            'CreateRemoteThread', 'CreateThread', 'DeleteFiber', 'ExitProcess',
            'ExitThread', 'FreeEnvironmentStringsA', 'FreeEnvironmentStringsW',
            'GetCommandLineA', 'GetCommandLineW', 'GetCurrentProcess',
            'GetCurrentProcessId', 'GetCurrentThread', 'GetCurrentThreadId',
            'GetEnvironmentStringsA', 'GetEnvironmentStringsW',
            'GetEnvironmentVariableA', 'GetEnvironmentVariableW', 'GetExitCodeProcess',
            'GetExitCodeThread', 'GetGuiResources', 'GetPriorityClass',
            'GetProcessAffinityMask', 'GetProcessPriorityBoost',
            'GetProcessShutdownParameters', 'GetProcessTimes', 'GetProcessVersion',
            'GetProcessWorkingSetSize', 'GetStartupInfoA', 'GetStartupInfoW',
            'GetThreadPriority', 'GetThreadPriorityBoost', 'GetThreadTimes',
            'OpenJobObjectA', 'OpenJobObjectW', 'OpenProcess',
            'QueryInformationJobObject', 'ResumeThread', 'SetEnvironmentVariableA',
            'SetEnvironmentVariableW', 'SetInformationJobObject', 'SetPriorityClass',
            'SetProcessAffinityMask', 'SetProcessPriorityBoost',
            'SetProcessShutdownParameters', 'SetProcessWorkingSetSize',
            'SetThreadAffinityMask', 'SetThreadIdealProcessor', 'SetThreadPriority',
            'SetThreadPriorityBoost', 'Sleep', 'SleepEx', 'SuspendThread',
            'SwitchToFiber', 'SwitchToThread', 'TerminateJobObject', 'TerminateProcess',
            'TerminateThread', 'WaitForInputIdle', 'WinExec',

            '_hread', '_hwrite', '_lclose', '_lcreat', '_llseek', '_lopen', '_lread',
            '_lwrite', 'AreFileApisANSI', 'CancelIo', 'CopyFileA', 'CopyFileW',
            'CreateDirectoryA', 'CreateDirectoryExA', 'CreateDirectoryExW',
            'CreateDirectoryW', 'CreateFileA', 'CreateFileW', 'DeleteFileA',
            'DeleteFileW', 'FindClose', 'FindCloseChangeNotification',
            'FindFirstChangeNotificationA', 'FindFirstChangeNotificationW',
            'FindFirstFileA', 'FindFirstFileW', 'FindNextFileA', 'FindNextFileW',
            'FlushFileBuffers', 'GetCurrentDirectoryA', 'GetCurrentDirectoryW',
            'GetDiskFreeSpaceA', 'GetDiskFreeSpaceExA', 'GetDiskFreeSpaceExW',
            'GetDiskFreeSpaceW', 'GetDriveTypeA', 'GetDriveTypeW', 'GetFileAttributesA',
            'GetFileAttributesExA', 'GetFileAttributesExW', 'GetFileAttributesW',
            'GetFileInformationByHandle', 'GetFileSize', 'GetFileType',
            'GetFullPathNameA', 'GetFullPathNameW', 'GetLogicalDrives',
            'GetLogicalDriveStringsA', 'GetLogicalDriveStringsW', 'GetLongPathNameA',
            'GetLongPathNameW', 'GetShortPathNameA', 'GetShortPathNameW',
            'GetTempFileNameA', 'GetTempFileNameW', 'GetTempPathA', 'GetTempPathW',
            'LockFile', 'MoveFileA', 'MoveFileW', 'MulDiv', 'OpenFile',
            'QueryDosDeviceA', 'QueryDosDeviceW', 'ReadFile', 'ReadFileEx',
            'RemoveDirectoryA', 'RemoveDirectoryW', 'SearchPathA', 'SearchPathW',
            'SetCurrentDirectoryA', 'SetCurrentDirectoryW', 'SetEndOfFile',
            'SetFileApisToANSI', 'SetFileApisToOEM', 'SetFileAttributesA',
            'SetFileAttributesW', 'SetFilePointer', 'SetHandleCount',
            'SetVolumeLabelA', 'SetVolumeLabelW', 'UnlockFile', 'WriteFile',
            'WriteFileEx',

            'DeviceIoControl',

            'GetModuleFileNameA', 'GetModuleFileNameW', 'GetProcAddress',
            'LoadLibraryA', 'LoadLibraryExA', 'LoadLibraryExW', 'LoadLibraryW',
            'LoadModule',

            'GetPrivateProfileIntA', 'GetPrivateProfileIntW',
            'GetPrivateProfileSectionA', 'GetPrivateProfileSectionNamesA',
            'GetPrivateProfileSectionNamesW', 'GetPrivateProfileSectionW',
            'GetPrivateProfileStringA', 'GetPrivateProfileStringW',
            'GetPrivateProfileStructA', 'GetPrivateProfileStructW',
            'GetProfileIntA', 'GetProfileIntW', 'GetProfileSectionA',
            'GetProfileSectionW', 'GetProfileStringA', 'GetProfileStringW',
            'RegCloseKey', 'RegConnectRegistryA', 'RegConnectRegistryW',
            'RegCreateKeyA', 'RegCreateKeyExA', 'RegCreateKeyExW',
            'RegCreateKeyW', 'RegDeleteKeyA', 'RegDeleteKeyW', 'RegDeleteValueA',
            'RegDeleteValueW', 'RegEnumKeyA', 'RegEnumKeyExA', 'RegEnumKeyExW',
            'RegEnumKeyW', 'RegEnumValueA', 'RegEnumValueW', 'RegFlushKey',
            'RegGetKeySecurity', 'RegLoadKeyA', 'RegLoadKeyW',
            'RegNotifyChangeKeyValue', 'RegOpenKeyA', 'RegOpenKeyExA', 'RegOpenKeyExW',
            'RegOpenKeyW', 'RegOverridePredefKey', 'RegQueryInfoKeyA',
            'RegQueryInfoKeyW', 'RegQueryMultipleValuesA', 'RegQueryMultipleValuesW',
            'RegQueryValueA', 'RegQueryValueExA', 'RegQueryValueExW', 'RegQueryValueW',
            'RegReplaceKeyA', 'RegReplaceKeyW', 'RegRestoreKeyA', 'RegRestoreKeyW',
            'RegSaveKeyA', 'RegSaveKeyW', 'RegSetKeySecurity', 'RegSetValueA',
            'RegSetValueExA', 'RegSetValueExW', 'RegSetValueW', 'RegUnLoadKeyA',
            'RegUnLoadKeyW', 'WritePrivateProfileSectionA', 'WritePrivateProfileSectionW',
            'WritePrivateProfileStringA', 'WritePrivateProfileStringW',
            'WritePrivateProfileStructA', 'WritePrivateProfileStructW',
            'WriteProfileSectionA', 'WriteProfileSectionW', 'WriteProfileStringA',
            'WriteProfileStringW',

            'AccessCheck', 'AccessCheckAndAuditAlarmA', 'AccessCheckAndAuditAlarmW',
            'AccessCheckByType', 'AccessCheckByTypeAndAuditAlarmA',
            'AccessCheckByTypeAndAuditAlarmW', 'AccessCheckByTypeResultList',
            'AccessCheckByTypeResultListAndAuditAlarmA', 'AccessCheckByTypeResultListAndAuditAlarmW',
            'AddAccessAllowedAce', 'AddAccessAllowedAceEx', 'AddAccessAllowedObjectAce',
            'AddAccessDeniedAce', 'AddAccessDeniedAceEx', 'AddAccessDeniedObjectAce',
            'AddAce', 'AddAuditAccessAce', 'AddAuditAccessAceEx', 'AddAuditAccessObjectAce',
            'AdjustTokenGroups', 'AdjustTokenPrivileges', 'AllocateAndInitializeSid',
            'AllocateLocallyUniqueId', 'AreAllAccessesGranted', 'AreAnyAccessesGranted',
            'BuildExplicitAccessWithNameA', 'BuildExplicitAccessWithNameW',
            'BuildImpersonateExplicitAccessWithNameA', 'BuildImpersonateExplicitAccessWithNameW',
            'BuildImpersonateTrusteeA', 'BuildImpersonateTrusteeW', 'BuildSecurityDescriptorA',
            'BuildSecurityDescriptorW', 'BuildTrusteeWithNameA', 'BuildTrusteeWithNameW',
            'BuildTrusteeWithSidA', 'BuildTrusteeWithSidW',
            'ConvertToAutoInheritPrivateObjectSecurity', 'CopySid', 'CreatePrivateObjectSecurity',
            'CreatePrivateObjectSecurityEx', 'CreateRestrictedToken', 'DeleteAce',
            'DestroyPrivateObjectSecurity', 'DuplicateToken', 'DuplicateTokenEx',
            'EqualPrefixSid', 'EqualSid', 'FindFirstFreeAce', 'FreeSid', 'GetAce',
            'GetAclInformation', 'GetAuditedPermissionsFromAclA', 'GetAuditedPermissionsFromAclW',
            'GetEffectiveRightsFromAclA', 'GetEffectiveRightsFromAclW',
            'GetExplicitEntriesFromAclA', 'GetExplicitEntriesFromAclW', 'GetFileSecurityA',
            'GetFileSecurityW', 'GetKernelObjectSecurity', 'GetLengthSid', 'GetMultipleTrusteeA',
            'GetMultipleTrusteeOperationA', 'GetMultipleTrusteeOperationW', 'GetMultipleTrusteeW',
            'GetNamedSecurityInfoA', 'GetNamedSecurityInfoW', 'GetPrivateObjectSecurity',
            'GetSecurityDescriptorControl', 'GetSecurityDescriptorDacl',
            'GetSecurityDescriptorGroup', 'GetSecurityDescriptorLength',
            'GetSecurityDescriptorOwner', 'GetSecurityDescriptorSacl', 'GetSecurityInfo',
            'GetSidIdentifierAuthority', 'GetSidLengthRequired', 'GetSidSubAuthority',
            'GetSidSubAuthorityCount', 'GetTokenInformation', 'GetTrusteeFormA',
            'GetTrusteeFormW', 'GetTrusteeNameA', 'GetTrusteeNameW', 'GetTrusteeTypeA',
            'GetTrusteeTypeW', 'GetUserObjectSecurity', 'ImpersonateLoggedOnUser',
            'ImpersonateNamedPipeClient', 'ImpersonateSelf', 'InitializeAcl',
            'InitializeSecurityDescriptor', 'InitializeSid', 'IsTokenRestricted', 'IsValidAcl',
            'IsValidSecurityDescriptor', 'IsValidSid', 'LogonUserA', 'LogonUserW',
            'LookupAccountNameA', 'LookupAccountNameW', 'LookupAccountSidA', 'LookupAccountSidW',
            'LookupPrivilegeDisplayNameA', 'LookupPrivilegeDisplayNameW', 'LookupPrivilegeNameA',
            'LookupPrivilegeNameW', 'LookupPrivilegeValueA', 'LookupPrivilegeValueW',
            'LookupSecurityDescriptorPartsA', 'LookupSecurityDescriptorPartsW', 'MakeAbsoluteSD',
            'MakeSelfRelativeSD', 'MapGenericMask', 'ObjectCloseAuditAlarmA',
            'ObjectCloseAuditAlarmW', 'ObjectDeleteAuditAlarmA', 'ObjectDeleteAuditAlarmW',
            'ObjectOpenAuditAlarmA', 'ObjectOpenAuditAlarmW', 'ObjectPrivilegeAuditAlarmA',
            'ObjectPrivilegeAuditAlarmW', 'OpenProcessToken', 'OpenThreadToken', 'PrivilegeCheck',
            'PrivilegedServiceAuditAlarmA', 'PrivilegedServiceAuditAlarmW', 'RevertToSelf',
            'SetAclInformation', 'SetEntriesInAclA', 'SetEntriesInAclW', 'SetFileSecurityA',
            'SetFileSecurityW', 'SetKernelObjectSecurity', 'SetNamedSecurityInfoA',
            'SetNamedSecurityInfoW', 'SetPrivateObjectSecurity', 'SetPrivateObjectSecurityEx',
            'SetSecurityDescriptorControl', 'SetSecurityDescriptorDacl',
            'SetSecurityDescriptorGroup', 'SetSecurityDescriptorOwner',
            'SetSecurityDescriptorSacl', 'SetSecurityInfo', 'SetThreadToken',
            'SetTokenInformation', 'SetUserObjectSecurity', 'ChangeServiceConfig2A',
            'ChangeServiceConfig2W', 'ChangeServiceConfigA', 'ChangeServiceConfigW',
            'CloseServiceHandle', 'ControlService', 'CreateServiceA', 'CreateServiceW',
            'DeleteService', 'EnumDependentServicesA', 'EnumDependentServicesW',
            'EnumServicesStatusA', 'EnumServicesStatusW', 'GetServiceDisplayNameA',
            'GetServiceDisplayNameW', 'GetServiceKeyNameA', 'GetServiceKeyNameW',
            'LockServiceDatabase', 'NotifyBootConfigStatus', 'OpenSCManagerA', 'OpenSCManagerW',
            'OpenServiceA', 'OpenServiceW', 'QueryServiceConfig2A', 'QueryServiceConfig2W',
            'QueryServiceConfigA', 'QueryServiceConfigW', 'QueryServiceLockStatusA',
            'QueryServiceLockStatusW', 'QueryServiceObjectSecurity', 'QueryServiceStatus',
            'RegisterServiceCtrlHandlerA', 'RegisterServiceCtrlHandlerW',
            'SetServiceObjectSecurity', 'SetServiceStatus', 'StartServiceA',
            'StartServiceCtrlDispatcherA', 'StartServiceCtrlDispatcherW', 'StartServiceW',
            'UnlockServiceDatabase',

            'MultinetGetConnectionPerformanceA', 'MultinetGetConnectionPerformanceW',
            'NetAlertRaise', 'NetAlertRaiseEx', 'NetApiBufferAllocate', 'NetApiBufferFree',
            'NetApiBufferReallocate', 'NetApiBufferSize', 'NetConnectionEnum', 'NetFileClose',
            'NetFileGetInfo', 'NetGetAnyDCName', 'NetGetDCName', 'NetGetDisplayInformationIndex',
            'NetGroupAdd', 'NetGroupAddUser', 'NetGroupDel', 'NetGroupDelUser', 'NetGroupEnum',
            'NetGroupGetInfo', 'NetGroupGetUsers', 'NetGroupSetInfo', 'NetGroupSetUsers',
            'NetLocalGroupAdd', 'NetLocalGroupAddMember', 'NetLocalGroupAddMembers',
            'NetLocalGroupDel', 'NetLocalGroupDelMember', 'NetLocalGroupDelMembers',
            'NetLocalGroupEnum', 'NetLocalGroupGetInfo', 'NetLocalGroupGetMembers',
            'NetLocalGroupSetInfo', 'NetLocalGroupSetMembers', 'NetMessageBufferSend',
            'NetMessageNameAdd', 'NetMessageNameDel', 'NetMessageNameEnum',
            'NetMessageNameGetInfo', 'NetQueryDisplayInformation', 'NetRemoteComputerSupports',
            'NetRemoteTOd', 'NetReplExportDirAdd', 'NetReplExportDirDel', 'NetReplExportDirEnum',
            'NetReplExportDirGetInfo', 'NetReplExportDirLock', 'NetReplExportDirSetInfo',
            'NetReplExportDirUnlock', 'NetReplGetInfo', 'NetReplImportDirAdd',
            'NetReplImportDirDel', 'NetReplImportDirEnum', 'NetReplImportDirGetInfo',
            'NetReplImportDirLock', 'NetReplImportDirUnlock', 'NetReplSetInfo',
            'NetScheduleJobAdd', 'NetScheduleJobDel', 'NetScheduleJobEnum',
            'NetScheduleJobGetInfo', 'NetServerComputerNameAdd', 'NetServerComputerNameDel',
            'NetServerDiskEnum', 'NetServerEnum', 'NetServerEnumEx', 'NetServerGetInfo',
            'NetServerSetInfo', 'NetServerTransportAdd', 'NetServerTransportAddEx',
            'NetServerTransportDel', 'NetServerTransportEnum', 'NetSessionDel', 'NetSessionEnum',
            'NetSessionGetInfo', 'NetShareAdd', 'NetShareCheck', 'NetShareDel', 'NetShareEnum',
            'NetShareGetInfo', 'NetShareSetInfo', 'NetStatisticsGet', 'NetUseAdd', 'NetUseDel',
            'NetUseEnum', 'NetUseGetInfo', 'NetUserAdd', 'NetUserChangePassword', 'NetUserDel',
            'NetUserEnum', 'NetUserGetGroups', 'NetUserGetInfo', 'NetUserGetLocalGroups',
            'NetUserModalsGet', 'NetUserModalsSet', 'NetUserSetGroups', 'NetUserSetInfo',
            'NetWkstaGetInfo', 'NetWkstaSetInfo', 'NetWkstaTransportAdd', 'NetWkstaTransportDel',
            'NetWkstaTransportEnum', 'NetWkstaUserEnum', 'NetWkstaUserGetInfo',
            'NetWkstaUserSetInfo', 'WNetAddConnection2A', 'WNetAddConnection2W',
            'WNetAddConnection3A', 'WNetAddConnection3W', 'WNetAddConnectionA',
            'WNetAddConnectionW', 'WNetCancelConnection2A', 'WNetCancelConnection2W',
            'WNetCancelConnectionA', 'WNetCancelConnectionW', 'WNetCloseEnum',
            'WNetConnectionDialog', 'WNetConnectionDialog1A', 'WNetConnectionDialog1W',
            'WNetDisconnectDialog', 'WNetDisconnectDialog1A', 'WNetDisconnectDialog1W',
            'WNetEnumResourceA', 'WNetEnumResourceW', 'WNetGetConnectionA', 'WNetGetConnectionW',
            'WNetGetLastErrorA', 'WNetGetLastErrorW', 'WNetGetNetworkInformationA',
            'WNetGetNetworkInformationW', 'WNetGetProviderNameA', 'WNetGetProviderNameW',
            'WNetGetResourceInformationA', 'WNetGetResourceInformationW',
            'WNetGetResourceParentA', 'WNetGetResourceParentW', 'WNetGetUniversalNameA',
            'WNetGetUniversalNameW', 'WNetGetUserA', 'WNetGetUserW', 'WNetOpenEnumA',
            'WNetOpenEnumW', 'WNetUseConnectionA', 'WnetUseConnectionW',

            'accept', 'bind', 'closesocket', 'connect', 'gethostbyaddr', 'gethostbyname',
            'gethostname', 'getpeername', 'getprotobyname', 'getprotobynumber', 'getservbyname',
            'getservbyport', 'getsockname', 'getsockopt', 'htonl', 'htons', 'inet_addr',
            'inet_ntoa', 'ioctlsocket', 'listen', 'ntohl', 'ntohs', 'recv', 'recvfrom', 'select',
            'send', 'sendto', 'setsockopt', 'shutdown', 'socket', 'WSAAccept',
            'WSAAddressToStringA', 'WSAAddressToStringW', 'WSAAsyncGetHostByAddr',
            'WSAAsyncGetHostByName', 'WSAAsyncGetProtoByName', 'WSAAsyncGetProtoByNumber',
            'WSAAsyncGetServByName', 'WSAAsyncGetServByPort', 'WSAAsyncSelect',
            'WSACancelAsyncRequest', 'WSACancelBlockingCall', 'WSACleanup', 'WSACloseEvent',
            'WSAConnect', 'WSACreateEvent', 'WSADuplicateSocketA', 'WSADuplicateSocketW',
            'WSAEnumNameSpaceProvidersA', 'WSAEnumNameSpaceProvidersW', 'WSAEnumNetworkEvents',
            'WSAEnumProtocolsA', 'WSAEnumProtocolsW', 'WSAEventSelect', 'WSAGetLastError',
            'WSAGetOverlappedResult', 'WSAGetQOSByName', 'WSAGetServiceClassInfoA',
            'WSAGetServiceClassInfoW', 'WSAGetServiceClassNameByClassIdA',
            'WSAGetServiceClassNameByClassIdW', 'WSAHtonl', 'WSAHtons', 'WSAInstallServiceClassA',
            'WSAInstallServiceClassW', 'WSAIoctl', 'WSAIsBlocking', 'WSAJoinLeaf',
            'WSALookupServiceBeginA', 'WSALookupServiceBeginW', 'WSALookupServiceEnd',
            'WSALookupServiceNextA', 'WSALookupServiceNextW', 'WSANtohl', 'WSANtohs',
            'WSAProviderConfigChange', 'WSARecv', 'WSARecvDisconnect', 'WSARecvFrom',
            'WSARemoveServiceClass', 'WSAResetEvent', 'WSASend', 'WSASendDisconnect', 'WSASendTo',
            'WSASetBlockingHook', 'WSASetEvent', 'WSASetLastError', 'WSASetServiceA',
            'WSASetServiceW', 'WSASocketA', 'WSASocketW', 'WSAStartup', 'WSAStringToAddressA',
            'WSAStringToAddressW', 'WSAUnhookBlockingHook', 'WSAWaitForMultipleEvents',
            'WSCDeinstallProvider', 'WSCEnableNSProvider', 'WSCEnumProtocols',
            'WSCGetProviderPath', 'WSCInstallNameSpace', 'WSCInstallProvider',
            'WSCUnInstallNameSpace',

            'ContinueDebugEvent', 'DebugActiveProcess', 'DebugBreak', 'FatalExit',
            'FlushInstructionCache', 'GetThreadContext', 'GetThreadSelectorEntry',
            'IsDebuggerPresent', 'OutputDebugStringA', 'OutputDebugStringW', 'ReadProcessMemory',
            'SetDebugErrorLevel', 'SetThreadContext', 'WaitForDebugEvent', 'WriteProcessMemory',

            'CloseHandle', 'DuplicateHandle', 'GetHandleInformation', 'SetHandleInformation',

            'AdjustWindowRect', 'AdjustWindowRectEx', 'AllowSetForegroundWindow',
            'AnimateWindow', 'AnyPopup', 'ArrangeIconicWindows', 'BeginDeferWindowPos',
            'BringWindowToTop', 'CascadeWindows', 'ChildWindowFromPoint',
            'ChildWindowFromPointEx', 'CloseWindow', 'CreateWindowExA', 'CreateWindowExW',
            'DeferWindowPos', 'DestroyWindow', 'EndDeferWindowPos', 'EnumChildWindows',
            'EnumThreadWindows', 'EnumWindows', 'FindWindowA', 'FindWindowExA', 'FindWindowExW',
            'FindWindowW', 'GetAltTabInfoA', 'GetAltTabInfoW', 'GetAncestor', 'GetClientRect',
            'GetDesktopWindow', 'GetForegroundWindow', 'GetGUIThreadInfo', 'GetLastActivePopup',
            'GetLayout', 'GetParent', 'GetProcessDefaultLayout', 'GetTitleBarInf', 'GetTopWindow',
            'GetWindow', 'GetWindowInfo', 'GetWindowModuleFileNameA', 'GetWindowModuleFileNameW',
            'GetWindowPlacement', 'GetWindowRect', 'GetWindowTextA', 'GetWindowTextLengthA',
            'GetWindowTextLengthW', 'GetWindowTextW', 'GetWindowThreadProcessId', 'IsChild',
            'IsIconic', 'IsWindow', 'IsWindowUnicode', 'IsWindowVisible', 'IsZoomed',
            'LockSetForegroundWindow', 'MoveWindow', 'OpenIcon', 'RealChildWindowFromPoint',
            'RealGetWindowClassA', 'RealGetWindowClassW', 'SetForegroundWindow',
            'SetLayeredWindowAttributes', 'SetLayout', 'SetParent', 'SetProcessDefaultLayout',
            'SetWindowPlacement', 'SetWindowPos', 'SetWindowTextA', 'SetWindowTextW',
            'ShowOwnedPopups', 'ShowWindow', 'ShowWindowAsync', 'TileWindows',
            'UpdateLayeredWindow', 'WindowFromPoint',

            'CreateDialogIndirectParamA', 'CreateDialogIndirectParamW', 'CreateDialogParamA',
            'CreateDialogParamW', 'DefDlgProcA', 'DefDlgProcW', 'DialogBoxIndirectParamA',
            'DialogBoxIndirectParamW', 'DialogBoxParamA', 'DialogBoxParamW', 'EndDialog',
            'GetDialogBaseUnits', 'GetDlgCtrlID', 'GetDlgItem', 'GetDlgItemInt',
            'GetDlgItemTextA', 'GetDlgItemTextW', 'GetNextDlgGroupItem', 'GetNextDlgTabItem',
            'IsDialogMessageA', 'IsDialogMessageW', 'MapDialogRect', 'MessageBoxA',
            'MessageBoxExA', 'MessageBoxExW', 'MessageBoxIndirectA', 'MessageBoxIndirectW',
            'MessageBoxW', 'SendDlgItemMessageA', 'SendDlgItemMessageW', 'SetDlgItemInt',
            'SetDlgItemTextA', 'SetDlgItemTextW',

            'GetWriteWatch', 'GlobalMemoryStatus', 'GlobalMemoryStatusEx', 'IsBadCodePtr',
            'IsBadReadPtr', 'IsBadStringPtrA', 'IsBadStringPtrW', 'IsBadWritePtr',
            'ResetWriteWatch', 'AllocateUserPhysicalPages', 'FreeUserPhysicalPages',
            'MapUserPhysicalPages', 'MapUserPhysicalPagesScatter', 'GlobalAlloc', 'GlobalFlags',
            'GlobalFree', 'GlobalHandle', 'GlobalLock', 'GlobalReAlloc', 'GlobalSize',
            'GlobalUnlock', 'LocalAlloc', 'LocalFlags', 'LocalFree', 'LocalHandle', 'LocalLock',
            'LocalReAlloc', 'LocalSize', 'LocalUnlock', 'GetProcessHeap', 'GetProcessHeaps',
            'HeapAlloc', 'HeapCompact', 'HeapCreate', 'HeapDestroy', 'HeapFree', 'HeapLock',
            'HeapReAlloc', 'HeapSize', 'HeapUnlock', 'HeapValidate', 'HeapWalk', 'VirtualAlloc',
            'VirtualAllocEx', 'VirtualFree', 'VirtualFreeEx', 'VirtualLock', 'VirtualProtect',
            'VirtualProtectEx', 'VirtualQuery', 'VirtualQueryEx', 'VirtualUnlock',
            'GetFreeSpace', 'GlobalCompact', 'GlobalFix', 'GlobalUnfix', 'GlobalUnWire',
            'GlobalWire', 'IsBadHugeReadPtr', 'IsBadHugeWritePtr', 'LocalCompact', 'LocalShrink',

            'GetClassInfoA', 'GetClassInfoW', 'GetClassInfoExA', 'GetClassInfoExW',
            'GetClassLongA', 'GetClassLongW', 'GetClassLongPtrA', 'GetClassLongPtrW',
            'RegisterClassA', 'RegisterClassW', 'RegisterClassExA', 'RegisterClassExW',
            'SetClassLongA', 'SetClassLongW', 'SetClassLongPtrA', 'SetClassLongPtrW',
            'SetWindowLongA', 'SetWindowLongW', 'SetWindowLongPtrA', 'SetWindowLongPtrW',
            'UnregisterClassA', 'UnregisterClassW', 'GetClassWord', 'GetWindowWord',
            'SetClassWord', 'SetWindowWord'
            ),
        // Native API
        6 => array(
            'CsrAllocateCaptureBuffer', 'CsrAllocateCapturePointer', 'CsrAllocateMessagePointer',
            'CsrCaptureMessageBuffer', 'CsrCaptureMessageString', 'CsrCaptureTimeout',
            'CsrClientCallServer', 'CsrClientConnectToServer', 'CsrFreeCaptureBuffer',
            'CsrIdentifyAlertableThread', 'CsrNewThread', 'CsrProbeForRead', 'CsrProbeForWrite',
            'CsrSetPriorityClass',

            'LdrAccessResource', 'LdrDisableThreadCalloutsForDll', 'LdrEnumResources',
            'LdrFindEntryForAddress', 'LdrFindResource_U', 'LdrFindResourceDirectory_U',
            'LdrGetDllHandle', 'LdrGetProcedureAddress', 'LdrInitializeThunk', 'LdrLoadDll',
            'LdrProcessRelocationBlock', 'LdrQueryImageFileExecutionOptions',
            'LdrQueryProcessModuleInformation', 'LdrShutdownProcess', 'LdrShutdownThread',
            'LdrUnloadDll', 'LdrVerifyImageMatchesChecksum',

            'NtAcceptConnectPort', 'ZwAcceptConnectPort', 'NtCompleteConnectPort',
            'ZwCompleteConnectPort', 'NtConnectPort', 'ZwConnectPort', 'NtCreatePort',
            'ZwCreatePort', 'NtImpersonateClientOfPort', 'ZwImpersonateClientOfPort',
            'NtListenPort', 'ZwListenPort', 'NtQueryInformationPort', 'ZwQueryInformationPort',
            'NtReadRequestData', 'ZwReadRequestData', 'NtReplyPort', 'ZwReplyPort',
            'NtReplyWaitReceivePort', 'ZwReplyWaitReceivePort', 'NtReplyWaitReplyPort',
            'ZwReplyWaitReplyPort', 'NtRequestPort', 'ZwRequestPort', 'NtRequestWaitReplyPort',
            'ZwRequestWaitReplyPort', 'NtSecureConnectPort', 'ZwSecureConnectPort',
            'NtWriteRequestData', 'ZwWriteRequestData',

            'NtAccessCheck', 'ZwAccessCheck', 'NtAccessCheckAndAuditAlarm',
            'ZwAccessCheckAndAuditAlarm', 'NtAccessCheckByType', 'ZwAccessCheckByType',
            'NtAccessCheckByTypeAndAuditAlarm', 'ZwAccessCheckByTypeAndAuditAlarm',
            'NtAccessCheckByTypeResultList', 'ZwAccessCheckByTypeResultList',
            'NtAdjustGroupsToken', 'ZwAdjustGroupsToken', 'NtAdjustPrivilegesToken',
            'ZwAdjustPrivilegesToken', 'NtCloseObjectAuditAlarm', 'ZwCloseObjectAuditAlarm',
            'NtCreateToken', 'ZwCreateToken', 'NtDeleteObjectAuditAlarm',
            'ZwDeleteObjectAuditAlarm', 'NtDuplicateToken', 'ZwDuplicateToken',
            'NtFilterToken', 'ZwFilterToken', 'NtImpersonateThread', 'ZwImpersonateThread',
            'NtOpenObjectAuditAlarm', 'ZwOpenObjectAuditAlarm', 'NtOpenProcessToken',
            'ZwOpenProcessToken', 'NtOpenThreadToken', 'ZwOpenThreadToken', 'NtPrivilegeCheck',
            'ZwPrivilegeCheck', 'NtPrivilegedServiceAuditAlarm', 'ZwPrivilegedServiceAuditAlarm',
            'NtPrivilegeObjectAuditAlarm', 'ZwPrivilegeObjectAuditAlarm',
            'NtQueryInformationToken', 'ZwQueryInformationToken', 'NtQuerySecurityObject',
            'ZwQuerySecurityObject', 'NtSetInformationToken', 'ZwSetInformationToken',
            'NtSetSecurityObject', 'ZwSetSecurityObject',

            'NtAddAtom', 'ZwAddAtom', 'NtDeleteAtom', 'ZwDeleteAtom', 'NtFindAtom', 'ZwFindAtom',
            'NtQueryInformationAtom', 'ZwQueryInformationAtom',

            'NtAlertResumeThread', 'ZwAlertResumeThread', 'NtAlertThread', 'ZwAlertThread',
            'NtCreateProcess', 'ZwCreateProcess', 'NtCreateThread', 'ZwCreateThread',
            'NtCurrentTeb', 'NtDelayExecution', 'ZwDelayExecution', 'NtGetContextThread',
            'ZwGetContextThread', 'NtOpenProcess', 'ZwOpenProcess', 'NtOpenThread',
            'ZwOpenThread', 'NtQueryInformationProcess', 'ZwQueryInformationProcess',
            'NtQueryInformationThread', 'ZwQueryInformationThread', 'NtQueueApcThread',
            'ZwQueueApcThread', 'NtResumeThread', 'ZwResumeThread', 'NtSetContextThread',
            'ZwSetContextThread', 'NtSetHighWaitLowThread', 'ZwSetHighWaitLowThread',
            'NtSetInformationProcess', 'ZwSetInformationProcess', 'NtSetInformationThread',
            'ZwSetInformationThread', 'NtSetLowWaitHighThread', 'ZwSetLowWaitHighThread',
            'NtSuspendThread', 'ZwSuspendThread', 'NtTerminateProcess', 'ZwTerminateProcess',
            'NtTerminateThread', 'ZwTerminateThread', 'NtTestAlert', 'ZwTestAlert',
            'NtYieldExecution', 'ZwYieldExecution',

            'NtAllocateVirtualMemory', 'ZwAllocateVirtualMemory', 'NtAllocateVirtualMemory64',
            'ZwAllocateVirtualMemory64', 'NtAreMappedFilesTheSame', 'ZwAreMappedFilesTheSame',
            'NtCreateSection', 'ZwCreateSection', 'NtExtendSection', 'ZwExtendSection',
            'NtFlushVirtualMemory', 'ZwFlushVirtualMemory', 'NtFreeVirtualMemory',
            'ZwFreeVirtualMemory', 'NtFreeVirtualMemory64', 'ZwFreeVirtualMemory64',
            'NtLockVirtualMemory', 'ZwLockVirtualMemory', 'NtMapViewOfSection',
            'ZwMapViewOfSection', 'NtMapViewOfVlmSection', 'ZwMapViewOfVlmSection',
            'NtOpenSection', 'ZwOpenSection', 'NtProtectVirtualMemory', 'ZwProtectVirtualMemory',
            'NtProtectVirtualMemory64', 'ZwProtectVirtualMemory64', 'NtQueryVirtualMemory',
            'ZwQueryVirtualMemory', 'NtQueryVirtualMemory64', 'ZwQueryVirtualMemory64',
            'NtReadVirtualMemory', 'ZwReadVirtualMemory', 'NtReadVirtualMemory64',
            'ZwReadVirtualMemory64', 'NtUnlockVirtualMemory', 'ZwUnlockVirtualMemory',
            'NtUnmapViewOfSection', 'ZwUnmapViewOfSection', 'NtUnmapViewOfVlmSection',
            'ZwUnmapViewOfVlmSection', 'NtWriteVirtualMemory', 'ZwWriteVirtualMemory',
            'NtWriteVirtualMemory64', 'ZwWriteVirtualMemory64',

            'NtAssignProcessToJobObject', 'ZwAssignProcessToJobObject', 'NtCreateJobObject',
            'ZwCreateJobObject', 'NtOpenJobObject', 'ZwOpenJobObject',
            'NtQueryInformationJobObject', 'ZwQueryInformationJobObject',
            'NtSetInformationJobObject', 'ZwSetInformationJobObject', 'NtTerminateJobObject',
            'ZwTerminateJobObject',

            'NtCancelIoFile', 'ZwCancelIoFile', 'NtCreateFile', 'ZwCreateFile',
            'NtCreateIoCompletion', 'ZwCreateIoCompletion', 'NtDeleteFile', 'ZwDeleteFile',
            'NtDeviceIoControlFile', 'ZwDeviceIoControlFile', 'NtFlushBuffersFile',
            'ZwFlushBuffersFile', 'NtFsControlFile', 'ZwFsControlFile', 'NtLockFile', 'ZwLockFile',
            'NtNotifyChangeDirectoryFile', 'ZwNotifyChangeDirectoryFile', 'NtOpenFile',
            'ZwOpenFile', 'NtOpenIoCompletion', 'ZwOpenIoCompletion', 'NtQueryAttributesFile',
            'ZwQueryAttributesFile', 'NtQueryDirectoryFile', 'ZwQueryDirectoryFile',
            'NtQueryEaFile', 'ZwQueryEaFile', 'NtQueryIoCompletion', 'ZwQueryIoCompletion',
            'NtQueryQuotaInformationFile', 'ZwQueryQuotaInformationFile',
            'NtQueryVolumeInformationFile', 'ZwQueryVolumeInformationFile', 'NtReadFile',
            'ZwReadFile', 'NtReadFile64', 'ZwReadFile64', 'NtReadFileScatter', 'ZwReadFileScatter',
            'NtRemoveIoCompletion', 'ZwRemoveIoCompletion', 'NtSetEaFile', 'ZwSetEaFile',
            'NtSetInformationFile', 'ZwSetInformationFile', 'NtSetIoCompletion',
            'ZwSetIoCompletion', 'NtSetQuotaInformationFile', 'ZwSetQuotaInformationFile',
            'NtSetVolumeInformationFile', 'ZwSetVolumeInformationFile', 'NtUnlockFile',
            'ZwUnlockFile', 'NtWriteFile', 'ZwWriteFile', 'NtWriteFile64','ZwWriteFile64',
            'NtWriteFileGather', 'ZwWriteFileGather', 'NtQueryFullAttributesFile',
            'ZwQueryFullAttributesFile', 'NtQueryInformationFile', 'ZwQueryInformationFile',

            'RtlAbortRXact', 'RtlAbsoluteToSelfRelativeSD', 'RtlAcquirePebLock',
            'RtlAcquireResourceExclusive', 'RtlAcquireResourceShared', 'RtlAddAccessAllowedAce',
            'RtlAddAccessDeniedAce', 'RtlAddAce', 'RtlAddActionToRXact', 'RtlAddAtomToAtomTable',
            'RtlAddAttributeActionToRXact', 'RtlAddAuditAccessAce', 'RtlAddCompoundAce',
            'RtlAdjustPrivilege', 'RtlAllocateAndInitializeSid', 'RtlAllocateHandle',
            'RtlAllocateHeap', 'RtlAnsiCharToUnicodeChar', 'RtlAnsiStringToUnicodeSize',
            'RtlAnsiStringToUnicodeString', 'RtlAppendAsciizToString', 'RtlAppendStringToString',
            'RtlAppendUnicodeStringToString', 'RtlAppendUnicodeToString', 'RtlApplyRXact',
            'RtlApplyRXactNoFlush', 'RtlAreAllAccessesGranted', 'RtlAreAnyAccessesGranted',
            'RtlAreBitsClear', 'RtlAreBitsSet', 'RtlAssert', 'RtlCaptureStackBackTrace',
            'RtlCharToInteger', 'RtlCheckRegistryKey', 'RtlClearAllBits', 'RtlClearBits',
            'RtlClosePropertySet', 'RtlCompactHeap', 'RtlCompareMemory', 'RtlCompareMemoryUlong',
            'RtlCompareString', 'RtlCompareUnicodeString', 'RtlCompareVariants',
            'RtlCompressBuffer', 'RtlConsoleMultiByteToUnicodeN', 'RtlConvertExclusiveToShared',
            'RtlConvertLongToLargeInteger', 'RtlConvertPropertyToVariant',
            'RtlConvertSharedToExclusive', 'RtlConvertSidToUnicodeString',
            'RtlConvertUiListToApiList', 'RtlConvertUlongToLargeInteger',
            'RtlConvertVariantToProperty', 'RtlCopyLuid', 'RtlCopyLuidAndAttributesArray',
            'RtlCopySecurityDescriptor', 'RtlCopySid', 'RtlCopySidAndAttributesArray',
            'RtlCopyString', 'RtlCopyUnicodeString', 'RtlCreateAcl', 'RtlCreateAndSetSD',
            'RtlCreateAtomTable', 'RtlCreateEnvironment', 'RtlCreateHeap',
            'RtlCreateProcessParameters', 'RtlCreatePropertySet', 'RtlCreateQueryDebugBuffer',
            'RtlCreateRegistryKey', 'RtlCreateSecurityDescriptor', 'RtlCreateTagHeap',
            'RtlCreateUnicodeString', 'RtlCreateUnicodeStringFromAsciiz', 'RtlCreateUserProcess',
            'RtlCreateUserSecurityObject', 'RtlCreateUserThread', 'RtlCustomCPToUnicodeN',
            'RtlCutoverTimeToSystemTime', 'RtlDecompressBuffer', 'RtlDecompressFragment',
            'RtlDelete', 'RtlDeleteAce', 'RtlDeleteAtomFromAtomTable', 'RtlDeleteCriticalSection',
            'RtlDeleteElementGenericTable', 'RtlDeleteNoSplay', 'RtlDeleteRegistryValue',
            'RtlDeleteResource', 'RtlDeleteSecurityObject', 'RtlDeNormalizeProcessParams',
            'RtlDestroyAtomTable', 'RtlDestroyEnvironment', 'RtlDestroyHandleTable',
            'RtlDestroyHeap', 'RtlDestroyProcessParameters', 'RtlDestroyQueryDebugBuffer',
            'RtlDetermineDosPathNameType_U', 'RtlDoesFileExists_U', 'RtlDosPathNameToNtPathName_U',
            'RtlDosSearchPath_U', 'RtlDowncaseUnicodeString', 'RtlDumpResource',
            'RtlEmptyAtomTable', 'RtlEnlargedIntegerMultiply', 'RtlEnlargedUnsignedDivide',
            'RtlEnlargedUnsignedMultiply', 'RtlEnterCriticalSection', 'RtlEnumerateGenericTable',
            'RtlEnumerateGenericTableWithoutSplaying', 'RtlEnumerateProperties',
            'RtlEnumProcessHeaps', 'RtlEqualComputerName', 'RtlEqualDomainName', 'RtlEqualLuid',
            'RtlEqualPrefixSid', 'RtlEqualSid', 'RtlEqualString', 'RtlEqualUnicodeString',
            'RtlEraseUnicodeString', 'RtlExpandEnvironmentStrings_U', 'RtlExtendedIntegerMultiply',
            'RtlExtendedLargeIntegerDivide', 'RtlExtendedMagicDivide', 'RtlExtendHeap',
            'RtlFillMemory', 'RtlFillMemoryUlong', 'RtlFindClearBits', 'RtlFindClearBitsAndSet',
            'RtlFindLongestRunClear', 'RtlFindLongestRunSet', 'RtlFindMessage', 'RtlFindSetBits',
            'RtlFindSetBitsAndClear', 'RtlFirstFreeAce', 'RtlFlushPropertySet',
            'RtlFormatCurrentUserKeyPath', 'RtlFormatMessage', 'RtlFreeAnsiString',
            'RtlFreeHandle', 'RtlFreeHeap', 'RtlFreeOemString', 'RtlFreeSid',
            'RtlFreeUnicodeString', 'RtlFreeUserThreadStack', 'RtlGenerate8dot3Name', 'RtlGetAce',
            'RtlGetCallersAddress', 'RtlGetCompressionWorkSpaceSize',
            'RtlGetControlSecurityDescriptor', 'RtlGetCurrentDirectory_U',
            'RtlGetDaclSecurityDescriptor', 'RtlGetElementGenericTable', 'RtlGetFullPathName_U',
            'RtlGetGroupSecurityDescriptor', 'RtlGetLongestNtPathLength', 'RtlGetNtGlobalFlags',
            'RtlGetNtProductType', 'RtlGetOwnerSecurityDescriptor', 'RtlGetProcessHeaps',
            'RtlGetSaclSecurityDescriptor', 'RtlGetUserInfoHeap', 'RtlGuidToPropertySetName',
            'RtlIdentifierAuthoritySid', 'RtlImageDirectoryEntryToData', 'RtlImageNtHeader',
            'RtlImageRvaToSection', 'RtlImageRvaToVa', 'RtlImpersonateSelf', 'RtlInitAnsiString',
            'RtlInitCodePageTable', 'RtlInitializeAtomPackage', 'RtlInitializeBitMap',
            'RtlInitializeContext', 'RtlInitializeCriticalSection',
            'RtlInitializeCriticalSectionAndSpinCount', 'RtlInitializeGenericTable',
            'RtlInitializeHandleTable', 'RtlInitializeResource', 'RtlInitializeRXact',
            'RtlInitializeSid', 'RtlInitNlsTables', 'RtlInitString', 'RtlInitUnicodeString',
            'RtlInsertElementGenericTable', 'RtlIntegerToChar', 'RtlIntegerToUnicodeString',
            'RtlIsDosDeviceName_U', 'RtlIsGenericTableEmpty', 'RtlIsNameLegalDOS8Dot3',
            'RtlIsTextUnicode', 'RtlIsValidHandle', 'RtlIsValidIndexHandle', 'RtlLargeIntegerAdd',
            'RtlLargeIntegerArithmeticShift', 'RtlLargeIntegerDivide', 'RtlLargeIntegerNegate',
            'RtlLargeIntegerShiftLeft', 'RtlLargeIntegerShiftRight', 'RtlLargeIntegerSubtract',
            'RtlLargeIntegerToChar', 'RtlLeaveCriticalSection', 'RtlLengthRequiredSid',
            'RtlLengthSecurityDescriptor', 'RtlLengthSid', 'RtlLocalTimeToSystemTime',
            'RtlLockHeap', 'RtlLookupAtomInAtomTable', 'RtlLookupElementGenericTable',
            'RtlMakeSelfRelativeSD', 'RtlMapGenericMask', 'RtlMoveMemory',
            'RtlMultiByteToUnicodeN', 'RtlMultiByteToUnicodeSize', 'RtlNewInstanceSecurityObject',
            'RtlNewSecurityGrantedAccess', 'RtlNewSecurityObject', 'RtlNormalizeProcessParams',
            'RtlNtStatusToDosError', 'RtlNumberGenericTableElements', 'RtlNumberOfClearBits',
            'RtlNumberOfSetBits', 'RtlOemStringToUnicodeSize', 'RtlOemStringToUnicodeString',
            'RtlOemToUnicodeN', 'RtlOnMappedStreamEvent', 'RtlOpenCurrentUser',
            'RtlPcToFileHeader', 'RtlPinAtomInAtomTable', 'RtlpNtCreateKey',
            'RtlpNtEnumerateSubKey', 'RtlpNtMakeTemporaryKey', 'RtlpNtOpenKey',
            'RtlpNtQueryValueKey', 'RtlpNtSetValueKey', 'RtlPrefixString',
            'RtlPrefixUnicodeString', 'RtlPropertySetNameToGuid', 'RtlProtectHeap',
            'RtlpUnWaitCriticalSection', 'RtlpWaitForCriticalSection', 'RtlQueryAtomInAtomTable',
            'RtlQueryEnvironmentVariable_U', 'RtlQueryInformationAcl',
            'RtlQueryProcessBackTraceInformation', 'RtlQueryProcessDebugInformation',
            'RtlQueryProcessHeapInformation', 'RtlQueryProcessLockInformation',
            'RtlQueryProperties', 'RtlQueryPropertyNames', 'RtlQueryPropertySet',
            'RtlQueryRegistryValues', 'RtlQuerySecurityObject', 'RtlQueryTagHeap',
            'RtlQueryTimeZoneInformation', 'RtlRaiseException', 'RtlRaiseStatus', 'RtlRandom',
            'RtlReAllocateHeap', 'RtlRealPredecessor', 'RtlRealSuccessor', 'RtlReleasePebLock',
            'RtlReleaseResource', 'RtlRemoteCall', 'RtlResetRtlTranslations',
            'RtlRunDecodeUnicodeString', 'RtlRunEncodeUnicodeString', 'RtlSecondsSince1970ToTime',
            'RtlSecondsSince1980ToTime', 'RtlSelfRelativeToAbsoluteSD', 'RtlSetAllBits',
            'RtlSetAttributesSecurityDescriptor', 'RtlSetBits', 'RtlSetCriticalSectionSpinCount',
            'RtlSetCurrentDirectory_U', 'RtlSetCurrentEnvironment', 'RtlSetDaclSecurityDescriptor',
            'RtlSetEnvironmentVariable', 'RtlSetGroupSecurityDescriptor', 'RtlSetInformationAcl',
            'RtlSetOwnerSecurityDescriptor', 'RtlSetProperties', 'RtlSetPropertyNames',
            'RtlSetPropertySetClassId', 'RtlSetSaclSecurityDescriptor', 'RtlSetSecurityObject',
            'RtlSetTimeZoneInformation', 'RtlSetUnicodeCallouts', 'RtlSetUserFlagsHeap',
            'RtlSetUserValueHeap', 'RtlSizeHeap', 'RtlSplay', 'RtlStartRXact',
            'RtlSubAuthorityCountSid', 'RtlSubAuthoritySid', 'RtlSubtreePredecessor',
            'RtlSubtreeSuccessor', 'RtlSystemTimeToLocalTime', 'RtlTimeFieldsToTime',
            'RtlTimeToElapsedTimeFields', 'RtlTimeToSecondsSince1970', 'RtlTimeToSecondsSince1980',
            'RtlTimeToTimeFields', 'RtlTryEnterCriticalSection', 'RtlUnicodeStringToAnsiSize',
            'RtlUnicodeStringToAnsiString', 'RtlUnicodeStringToCountedOemString',
            'RtlUnicodeStringToInteger', 'RtlUnicodeStringToOemSize',
            'RtlUnicodeStringToOemString', 'RtlUnicodeToCustomCPN', 'RtlUnicodeToMultiByteN',
            'RtlUnicodeToMultiByteSize', 'RtlUnicodeToOemN', 'RtlUniform', 'RtlUnlockHeap',
            'RtlUnwind', 'RtlUpcaseUnicodeChar', 'RtlUpcaseUnicodeString',
            'RtlUpcaseUnicodeStringToAnsiString', 'RtlUpcaseUnicodeStringToCountedOemString',
            'RtlUpcaseUnicodeStringToOemString', 'RtlUpcaseUnicodeToCustomCPN',
            'RtlUpcaseUnicodeToMultiByteN', 'RtlUpcaseUnicodeToOemN', 'RtlUpperChar',
            'RtlUpperString', 'RtlUsageHeap', 'RtlValidAcl', 'RtlValidateHeap',
            'RtlValidateProcessHeaps', 'RtlValidSecurityDescriptor', 'RtlValidSid', 'RtlWalkHeap',
            'RtlWriteRegistryValue', 'RtlxAnsiStringToUnicodeSize', 'RtlxOemStringToUnicodeSize',
            'RtlxUnicodeStringToAnsiSize', 'RtlxUnicodeStringToOemSize', 'RtlZeroHeap',
            'RtlZeroMemory',

            'NtCancelTimer', 'ZwCancelTimer', 'NtCreateTimer', 'ZwCreateTimer', 'NtGetTickCount',
            'ZwGetTickCount', 'NtOpenTimer', 'ZwOpenTimer', 'NtQueryPerformanceCounter',
            'ZwQueryPerformanceCounter', 'NtQuerySystemTime', 'ZwQuerySystemTime', 'NtQueryTimer',
            'ZwQueryTimer', 'NtQueryTimerResolution', 'ZwQueryTimerResolution', 'NtSetSystemTime',
            'ZwSetSystemTime', 'NtSetTimer', 'ZwSetTimer', 'NtSetTimerResolution',
            'ZwSetTimerResolution',

            'NtClearEvent', 'ZwClearEvent', 'NtCreateEvent', 'ZwCreateEvent', 'NtCreateEventPair',
            'ZwCreateEventPair', 'NtCreateMutant', 'ZwCreateMutant', 'NtCreateSemaphore',
            'ZwCreateSemaphore', 'NtOpenEvent', 'ZwOpenEvent', 'NtOpenEventPair',
            'ZwOpenEventPair', 'NtOpenMutant', 'ZwOpenMutant', 'NtOpenSemaphore',
            'ZwOpenSemaphore', 'NtPulseEvent', 'ZwPulseEvent', 'NtQueryEvent', 'ZwQueryEvent',
            'NtQueryMutant', 'ZwQueryMutant', 'NtQuerySemaphore', 'ZwQuerySemaphore',
            'NtReleaseMutant', 'ZwReleaseMutant', 'NtReleaseProcessMutant',
            'ZwReleaseProcessMutant', 'NtReleaseSemaphore', 'ZwReleaseSemaphore',
            'NtReleaseThreadMutant', 'ZwReleaseThreadMutant', 'NtResetEvent', 'ZwResetEvent',
            'NtSetEvent', 'ZwSetEvent', 'NtSetHighEventPair', 'ZwSetHighEventPair',
            'NtSetHighWaitLowEventPair', 'ZwSetHighWaitLowEventPair', 'NtSetLowEventPair',
            'ZwSetLowEventPair', 'NtSetLowWaitHighEventPair', 'ZwSetLowWaitHighEventPair',
            'NtSignalAndWaitForSingleObject', 'ZwSignalAndWaitForSingleObject',
            'NtWaitForMultipleObjects', 'ZwWaitForMultipleObjects', 'NtWaitForSingleObject',
            'ZwWaitForSingleObject', 'NtWaitHighEventPair', 'ZwWaitHighEventPair',
            'NtWaitLowEventPair', 'ZwWaitLowEventPair',

            'NtClose', 'ZwClose', 'NtCreateDirectoryObject', 'ZwCreateDirectoryObject',
            'NtCreateSymbolicLinkObject', 'ZwCreateSymbolicLinkObject',
            'NtDuplicateObject', 'ZwDuplicateObject', 'NtMakeTemporaryObject',
            'ZwMakeTemporaryObject', 'NtOpenDirectoryObject', 'ZwOpenDirectoryObject',
            'NtOpenSymbolicLinkObject', 'ZwOpenSymbolicLinkObject', 'NtQueryDirectoryObject',
            'ZwQueryDirectoryObject', 'NtQueryObject', 'ZwQueryObject',
            'NtQuerySymbolicLinkObject', 'ZwQuerySymbolicLinkObject', 'NtSetInformationObject',
            'ZwSetInformationObject',

            'NtContinue', 'ZwContinue', 'NtRaiseException', 'ZwRaiseException',
            'NtRaiseHardError', 'ZwRaiseHardError', 'NtSetDefaultHardErrorPort',
            'ZwSetDefaultHardErrorPort',

            'NtCreateChannel', 'ZwCreateChannel', 'NtListenChannel', 'ZwListenChannel',
            'NtOpenChannel', 'ZwOpenChannel', 'NtReplyWaitSendChannel', 'ZwReplyWaitSendChannel',
            'NtSendWaitReplyChannel', 'ZwSendWaitReplyChannel', 'NtSetContextChannel',
            'ZwSetContextChannel',

            'NtCreateKey', 'ZwCreateKey', 'NtDeleteKey', 'ZwDeleteKey', 'NtDeleteValueKey',
            'ZwDeleteValueKey', 'NtEnumerateKey', 'ZwEnumerateKey', 'NtEnumerateValueKey',
            'ZwEnumerateValueKey', 'NtFlushKey', 'ZwFlushKey', 'NtInitializeRegistry',
            'ZwInitializeRegistry', 'NtLoadKey', 'ZwLoadKey', 'NtLoadKey2', 'ZwLoadKey2',
            'NtNotifyChangeKey', 'ZwNotifyChangeKey', 'NtOpenKey', 'ZwOpenKey', 'NtQueryKey',
            'ZwQueryKey', 'NtQueryMultipleValueKey', 'ZwQueryMultipleValueKey',
            'NtQueryMultiplValueKey', 'ZwQueryMultiplValueKey', 'NtQueryValueKey',
            'ZwQueryValueKey', 'NtReplaceKey', 'ZwReplaceKey', 'NtRestoreKey', 'ZwRestoreKey',
            'NtSaveKey', 'ZwSaveKey', 'NtSetInformationKey', 'ZwSetInformationKey',
            'NtSetValueKey', 'ZwSetValueKey', 'NtUnloadKey', 'ZwUnloadKey',

            'NtCreateMailslotFile', 'ZwCreateMailslotFile', 'NtCreateNamedPipeFile',
            'ZwCreateNamedPipeFile', 'NtCreatePagingFile', 'ZwCreatePagingFile',

            'NtCreateProfile', 'ZwCreateProfile', 'NtQueryIntervalProfile',
            'ZwQueryIntervalProfile', 'NtRegisterThreadTerminatePort',
            'ZwRegisterThreadTerminatePort', 'NtSetIntervalProfile', 'ZwSetIntervalProfile',
            'NtStartProfile', 'ZwStartProfile', 'NtStopProfile', 'ZwStopProfile',
            'NtSystemDebugControl', 'ZwSystemDebugControl',

            'NtEnumerateBus', 'ZwEnumerateBus', 'NtFlushInstructionCache',
            'ZwFlushInstructionCache', 'NtFlushWriteBuffer', 'ZwFlushWriteBuffer',
            'NtSetLdtEntries', 'ZwSetLdtEntries',

            'NtGetPlugPlayEvent', 'ZwGetPlugPlayEvent', 'NtPlugPlayControl', 'ZwPlugPlayControl',

            'NtInitiatePowerAction', 'ZwInitiatePowerAction', 'NtPowerInformation',
            'ZwPowerInformation', 'NtRequestWakeupLatency', 'ZwRequestWakeupLatency',
            'NtSetSystemPowerState', 'ZwSetSystemPowerState', 'NtSetThreadExecutionState',
            'ZwSetThreadExecutionState',

            'NtLoadDriver', 'ZwLoadDriver', 'NtRegisterNewDevice', 'ZwRegisterNewDevice',
            'NtUnloadDriver', 'ZwUnloadDriver',

            'NtQueryDefaultLocale', 'ZwQueryDefaultLocale', 'NtQueryDefaultUILanguage',
            'ZwQueryDefaultUILanguage', 'NtQuerySystemEnvironmentValue',
            'ZwQuerySystemEnvironmentValue', 'NtSetDefaultLocale', 'ZwSetDefaultLocale',
            'NtSetDefaultUILanguage', 'ZwSetDefaultUILanguage', 'NtSetSystemEnvironmentValue',
            'ZwSetSystemEnvironmentValue',

            'DbgBreakPoint', 'DbgPrint', 'DbgPrompt', 'DbgSsHandleKmApiMsg', 'DbgSsInitialize',
            'DbgUiConnectToDbg', 'DbgUiContinue', 'DbgUiWaitStateChange', 'DbgUserBreakPoint',
            'KiRaiseUserExceptionDispatcher', 'KiUserApcDispatcher', 'KiUserCallbackDispatcher',
            'KiUserExceptionDispatcher', 'NlsAnsiCodePage', 'NlsMbCodePageTag',
            'NlsMbOemCodePageTag', 'NtAllocateLocallyUniqueId', 'ZwAllocateLocallyUniqueId',
            'NtAllocateUuids', 'ZwAllocateUuids', 'NtCallbackReturn', 'ZwCallbackReturn',
            'NtDisplayString', 'ZwDisplayString', 'NtQueryOleDirectoryFile',
            'ZwQueryOleDirectoryFile', 'NtQuerySection', 'ZwQuerySection',
            'NtQuerySystemInformation', 'ZwQuerySystemInformation', 'NtSetSystemInformation',
            'ZwSetSystemInformation', 'NtShutdownSystem', 'ZwShutdownSystem', 'NtVdmControl',
            'ZwVdmControl', 'NtW32Call', 'ZwW32Call', 'PfxFindPrefix', 'PfxInitialize',
            'PfxInsertPrefix', 'PfxRemovePrefix', 'PropertyLengthAsVariant', 'RestoreEm87Context',
            'SaveEm87Context'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '{', '}', '[', ']',
        '+', '-', '*', '/', '%',
        '=', '<', '>',
        '!', '^', '&', '|',
        '?', ':',
        ';', ','
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true,
        5 => true,
        6 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #b1b100;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #000066;',
            4 => 'color: #993333;',
            5 => 'color: #4000dd;',
            6 => 'color: #4000dd;'
            ),
        'COMMENTS' => array(
            1 => 'color: #666666; font-style: italic;',
            2 => 'color: #339933;',
            'MULTI' => 'color: #808080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;',
            1 => 'color: #000099; font-weight: bold;',
            2 => 'color: #660099; font-weight: bold;',
            3 => 'color: #660099; font-weight: bold;',
            4 => 'color: #660099; font-weight: bold;',
            5 => 'color: #006699; font-weight: bold;',
            'HARD' => '',
            ),
        'BRACKETS' => array(
            0 => 'color: #009900;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #0000dd;',
            GESHI_NUMBER_BIN_PREFIX_0B => 'color: #208080;',
            GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
            ),
        'METHODS' => array(
            1 => 'color: #202020;',
            2 => 'color: #202020;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #339933;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => 'http://www.opengroup.org/onlinepubs/009695399/functions/{FNAMEL}.html',
        4 => '',
        5 => 'http://www.google.com/search?q={FNAMEL}+msdn.microsoft.com',
        6 => 'http://www.google.com/search?q={FNAMEL}+msdn.microsoft.com'
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.',
        2 => '::'
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4
);
