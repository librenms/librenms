<?php
/*************************************************************************************
 * <scl.php>
 * ---------------------------------
 * Author: Leonhard Hösch (leonhard.hoesch@siemens.com)
 * Copyright: (c) 2008 by Leonhard Hösch (siemens.de)
 * Release Version: 1.0.8.12
 * Date Started: 2012/09/25
 *
 * SCL language file for GeSHi.
 *
 * A SCL langauge file.
 *
 * CHANGES
 * -------
 * <date-of-release> (<GeSHi release>)
 *  -  First Release
 *
 * TODO (updated <date-of-release>)
 * -------------------------
 * <things-to-do>
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
    'LANG_NAME' => 'SCL',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('(*' => '*)'),
    'CASE_KEYWORDS' => GESHI_CAPS_UPPER,
    'QUOTEMARKS' => array("'"),
    'ESCAPE_CHAR' => '$',
    'KEYWORDS' => array(
        1 => array(
            'AND','ANY','ARRAY','AT','BEGIN','BLOCK_DB','BLOCK_FB','BLOCK_FC','BLOCK_SDB',
            'BLOCK_SFB','BLOCK_SFC','BOOL','BY','BYTE','CASE','CHAR','CONST','CONTINUE','COUNTER',
            'DATA_BLOCK','DATE','DATE_AND_TIME','DINT','DIV','DO','DT','DWORD','ELSE','ELSIF',
            'EN','END_CASE','END_CONST','END_DATA_BLOCK','END_FOR','END_FUNCTION',
            'END_FUNCTION_BLOCK','END_IF','END_LABEL','END_TYPE','END_ORGANIZATION_BLOCK',
            'END_REPEAT','END_STRUCT','END_VAR','END_WHILE','ENO','EXIT','FALSE','FOR','FUNCTION',
            'FUNCTION_BLOCK','GOTO','IF','INT','LABEL','MOD','NIL','NOT','OF','OK','OR',
            'ORGANIZATION_BLOCK','POINTER','PROGRAM','REAL','REPEAT','RETURN','S5TIME','STRING',
            'STRUCT','THEN','TIME','TIMER','TIME_OF_DAY','TO','TOD','TRUE','TYPE','VAR',
            'VAR_TEMP','UNTIL','VAR_INPUT','VAR_IN_OUT','VAR_OUTPUT','VOID','WHILE','WORD','XOR'
            ),
        2 =>array(
            'UBLKMOV','FILL','CREAT_DB','DEL_DB','TEST_DB','COMPRESS','REPL_VAL','CREA_DBL','READ_DBL',
            'WRIT_DBL','CREA_DB','RE_TRIGR','STP','WAIT','MP_ALM','CiR','PROTECT','SET_CLK','READ_CLK',
            'SNC_RTCB','SET_CLKS','RTM','SET_RTM','CTRL_RTM','READ_RTM','TIME_TCK','RD_DPARM',
            'RD_DPARA','WR_PARM','WR_DPARM','PARM_MOD','WR_REC','RD_REC','RD_DPAR','RDREC','WRREC','RALRM',
            'SALRM','RCVREC','PRVREC','SET_TINT','CAN_TINT','ACT_TINT','QRY_TINT','SRT_DINT','QRY_DINT',
            'CAN_DINT','MSK_FLT','DMSK_FLT','READ_ERR','DIS_IRT','EN_IRT','DIS_AIRT','EN_AIRT','RD_SINFO',
            'RDSYSST','WR_USMSG','OB_RT','C_DIAG','DP_TOPOL','UPDAT_PI','UPDAT_PO','SYNC_PI','SYNC_PO',
            'SET','RSET','DRUM','GADR_LGC','LGC_GADR','RD_LGADR','GEO_LOG','LOG_GEO','DP_PRAL','DPSYC_FR',
            'D_ACT_DP','DPNRM_DG','DPRD_DAT','DPWR_DAT','PN_IN','PN_OUT','PN_DP','WWW','IP_CONF','GETIO',
            'SETIO','GETIO_PART','SETIO_PART','GD_SND','GD_RCV','USEND','URCV','BSEND','BRCV','PUT','GET',
            'PRINT','START','STOP','RESUME','STATUS','USTATUS','CONTROL','C_CNTRL','X_SEND','X_RCV',
            'X_GET','X_PUT','X_ABORT','I_GET','I_PUT','I_ABORT','TCON','TDISCON','TSEND','TRCV','TUSEND',
            'TURCV','NOTIFY','NOTIFY_8P','ALARM','ALARM_8P','ALARM_8','AR_SEND','DIS_MSG','EN_MSG',
            'ALARM_SQ','ALARM_S','ALARM_SC','ALARM_DQ','LARM_D','READ_SI','DEL_SI','TP','TON','TOF','CTU',
            'CTD','CTUD','CONT_C','CONT_S','PULSEGEN','Analog','DIGITAL','COUNT','FREQUENC','PULSE',
            'SEND_PTP','RECV_PTP','RES_RECV','SEND_RK','FETCH_RK','SERVE_RK','H_CTRL','state'
            ),
        ),
    'SYMBOLS' => array(
        '.', '"', '|', ';', ',', '=>', '>=', '<=', ':=', '=', '<', '>'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000ff;',
            2 => 'color: #ff6f00;',
            ),
        'COMMENTS' => array(
            1 => 'color: #009600; font-style: italic;',
            'MULTI' => 'color: #009600; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #66cc66;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            0 => 'color: #006600;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #66cc66;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            0 => '',
            1 => '',
            2 => '',
            3 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        2 => ''
        ),
    'NUMBERS' => GESHI_NUMBER_INT_BASIC,
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        1 => '',
        2 => ''
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        0 => array(
            '<?php11!!' => '!!11?>'
            ),
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => false,
        ),
    'TAB_WIDTH' => 4
);
