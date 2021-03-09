<?php

// vim:fenc=utf-8:filetype=php:ts=4
/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 *
 * Most RRD Graph definitions copied from collection.cgi
 */
$GraphDefs = [];
$MetaGraphDefs = [];

if (is_file('definitions.local.php')) {
    require_once 'definitions.local.php';
}

function load_graph_definitions($logarithmic = false, $tinylegend = false)
{
    global $GraphDefs, $MetaGraphDefs;

    $Canvas = 'FFFFFF';

    $FullRed = 'FF0000';
    $FullGreen = '00E000';
    $FullBlue = '0000FF';
    $FullYellow = 'F0A000';
    $FullCyan = '00A0FF';
    $FullMagenta = 'A000FF';

    $HalfRed = 'F7B7B7';
    $HalfGreen = 'B7EFB7';
    $HalfBlue = 'B7B7F7';
    $HalfYellow = 'F3DFB7';
    $HalfCyan = 'B7DFF7';
    $HalfMagenta = 'DFB7F7';

    $HalfBlueGreen = '89B3C9';

    $GraphDefs = [];
    $GraphDefs['apache_bytes'] = [
        'DEF:min_raw={file}:value:MIN',
        'DEF:avg_raw={file}:value:AVERAGE',
        'DEF:max_raw={file}:value:MAX',
        'CDEF:min=min_raw,8,*',
        'CDEF:avg=avg_raw,8,*',
        'CDEF:max=max_raw,8,*',
        'CDEF:mytime=avg_raw,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:avg_sample=avg_raw,UN,0,avg_raw,IF,sample_len,*',
        'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
        'COMMENT:           Cur     Avg      Min     Max\l',
        "AREA:avg#$HalfBlue",
        "LINE1:avg#$FullBlue:Bit/s",
        'GPRINT:avg:LAST:%5.1lf%s',
        'GPRINT:avg:AVERAGE:%5.1lf%s',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:min:MIN:%5.1lf%s\l',
        'GPRINT:avg_sum:LAST:           (ca. %5.1lf%sB Total)', ];
    $GraphDefs['apache_requests'] = [
        'DEF:min={file}:value:MIN',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:max={file}:value:MAX',
        'COMMENT:           Cur     Avg      Min     Max\l',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Req/s",
        'GPRINT:avg:LAST:%5.2lf%s',
        'GPRINT:avg:AVERAGE:%5.2lf%s',
        'GPRINT:min:MIN:%5.2lf%s',
        'GPRINT:max:MAX:%5.2lf%s\l', ];
    $GraphDefs['apache_scoreboard'] = [
        'DEF:min={file}:value:MIN',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:max={file}:value:MAX',
        'COMMENT:           Cur     Min      Ave     Max\l',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Processes",
        'GPRINT:min:MIN:%6.2lf',
        'GPRINT:avg:AVERAGE:%6.2lf',
        'GPRINT:max:MAX:%6.2lf',
        'GPRINT:avg:LAST:%6.2lf', ];
    $GraphDefs['bitrate'] = [
        //'-v', 'Bits/s',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Bits/s",
        'GPRINT:min:MIN:%5.1lf%s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s Average,',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['charge'] = [
        //'-v', 'Ah',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Charge",
        'GPRINT:min:MIN:%5.1lf%sAh ',
        'GPRINT:avg:AVERAGE:%5.1lf%sAh ',
        'GPRINT:max:MAX:%5.1lf%sAh',
        'GPRINT:avg:LAST:%5.1lf%sAh\l', ];
    $GraphDefs['counter'] = [
        //'-v', 'Events',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Percent",
        'GPRINT:min:MIN:%6.2lf%% ',
        'GPRINT:avg:AVERAGE:%6.2lf%% ',
        'GPRINT:max:MAX:%6.2lf%%',
        'GPRINT:avg:LAST:%6.2lf%%\l', ];
    $GraphDefs['cpu'] = [
        //'-v', 'CPU load',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Percent",
        'GPRINT:min:MIN:%6.2lf%% ',
        'GPRINT:avg:AVERAGE:%6.2lf%% ',
        'GPRINT:max:MAX:%6.2lf%%',
        'GPRINT:avg:LAST:%6.2lf%%\l', ];
    $GraphDefs['current'] = [
        //'-v', 'Ampere',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Current",
        'GPRINT:min:MIN:%5.1lf%sA ',
        'GPRINT:avg:AVERAGE:%5.1lf%sA ',
        'GPRINT:max:MAX:%5.1lf%sA',
        'GPRINT:avg:LAST:%5.1lf%sA\l', ];
    $GraphDefs['df'] = [
        //'-v', 'Percent',
        '-l', '0',
        'DEF:free_avg={file}:free:AVERAGE',
        'DEF:free_min={file}:free:MIN',
        'DEF:free_max={file}:free:MAX',
        'DEF:used_avg={file}:used:AVERAGE',
        'DEF:used_min={file}:used:MIN',
        'DEF:used_max={file}:used:MAX',
        'CDEF:total=free_avg,used_avg,+',
        'CDEF:free_pct=100,free_avg,*,total,/',
        'CDEF:used_pct=100,used_avg,*,total,/',
        'CDEF:free_acc=free_pct,used_pct,+',
        'CDEF:used_acc=used_pct',
        "AREA:free_acc#$HalfGreen",
        "AREA:used_acc#$HalfRed",
        "LINE1:free_acc#$FullGreen:Free",
        'GPRINT:free_min:MIN:%5.1lf%sB ',
        'GPRINT:free_avg:AVERAGE:%5.1lf%sB ',
        'GPRINT:free_max:MAX:%5.1lf%sB',
        'GPRINT:free_avg:LAST:%5.1lf%sB\l',
        "LINE1:used_acc#$FullRed:Used",
        'GPRINT:used_min:MIN:%5.1lf%sB ',
        'GPRINT:used_avg:AVERAGE:%5.1lf%sB ',
        'GPRINT:used_max:MAX:%5.1lf%sB',
        'GPRINT:used_avg:LAST:%5.1lf%sB\l', ];
    $GraphDefs['disk'] = [
        'DEF:rtime_avg={file}:rtime:AVERAGE',
        'DEF:rtime_min={file}:rtime:MIN',
        'DEF:rtime_max={file}:rtime:MAX',
        'DEF:wtime_avg={file}:wtime:AVERAGE',
        'DEF:wtime_min={file}:wtime:MIN',
        'DEF:wtime_max={file}:wtime:MAX',
        'CDEF:rtime_avg_ms=rtime_avg,1000,/',
        'CDEF:rtime_min_ms=rtime_min,1000,/',
        'CDEF:rtime_max_ms=rtime_max,1000,/',
        'CDEF:wtime_avg_ms=wtime_avg,1000,/',
        'CDEF:wtime_min_ms=wtime_min,1000,/',
        'CDEF:wtime_max_ms=wtime_max,1000,/',
        'CDEF:total_avg_ms=rtime_avg_ms,wtime_avg_ms,+',
        'CDEF:total_min_ms=rtime_min_ms,wtime_min_ms,+',
        'CDEF:total_max_ms=rtime_max_ms,wtime_max_ms,+',
        "AREA:total_max_ms#$HalfRed",
        "AREA:total_min_ms#$Canvas",
        "LINE1:wtime_avg_ms#$FullGreen:Write",
        'GPRINT:wtime_min_ms:MIN:%5.1lf%s ',
        'GPRINT:wtime_avg_ms:AVERAGE:%5.1lf%s ',
        'GPRINT:wtime_max_ms:MAX:%5.1lf%s',
        'GPRINT:wtime_avg_ms:LAST:%5.1lf%s Last\n',
        "LINE1:rtime_avg_ms#$FullBlue:Read ",
        'GPRINT:rtime_min_ms:MIN:%5.1lf%s ',
        'GPRINT:rtime_avg_ms:AVERAGE:%5.1lf%s ',
        'GPRINT:rtime_max_ms:MAX:%5.1lf%s',
        'GPRINT:rtime_avg_ms:LAST:%5.1lf%s Last\n',
        "LINE1:total_avg_ms#$FullRed:Total",
        'GPRINT:total_min_ms:MIN:%5.1lf%s ',
        'GPRINT:total_avg_ms:AVERAGE:%5.1lf%s ',
        'GPRINT:total_max_ms:MAX:%5.1lf%s',
        'GPRINT:total_avg_ms:LAST:%5.1lf%s Last', ];
    $GraphDefs['disk_octets'] = [
        //'-v', 'Bytes/s',
        '--units=si',
        'DEF:out_min={file}:write:MIN',
        'DEF:out_avg={file}:write:AVERAGE',
        'DEF:out_max={file}:write:MAX',
        'DEF:inc_min={file}:read:MIN',
        'DEF:inc_avg={file}:read:AVERAGE',
        'DEF:inc_max={file}:read:MAX',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'CDEF:mytime=out_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
        'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
        'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
        'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
        'COMMENT:            Total      Avg      Max     Cur\l',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Write",
        'GPRINT:out_avg_sum:LAST:(%5.1lf%sB)',
        'GPRINT:out_avg:AVERAGE:%5.1lf%s',
        'GPRINT:out_max:MAX:%5.1lf%s',
        'GPRINT:out_avg:LAST:%5.1lf%s\l',
        "LINE1:inc_avg#$FullBlue:Read ",
        'GPRINT:inc_avg_sum:LAST:(%5.1lf%sB)',
        'GPRINT:inc_avg:AVERAGE:%5.1lf%s',
        'GPRINT:inc_max:MAX:%5.1lf%s',
        'GPRINT:inc_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['disk_merged'] = [
        // Merged Ops/sec
        '--units=si',
        'DEF:out_min={file}:write:MIN',
        'DEF:out_avg={file}:write:AVERAGE',
        'DEF:out_max={file}:write:MAX',
        'DEF:inc_min={file}:read:MIN',
        'DEF:inc_avg={file}:read:AVERAGE',
        'DEF:inc_max={file}:read:MAX',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'COMMENT:Ops/sec         Avg      Max    Cur\l',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Written   ",
        'GPRINT:out_avg:AVERAGE:%5.2lf%s',
        'GPRINT:out_max:MAX:%5.2lf%s',
        'GPRINT:out_avg:LAST:%5.2lf%s\l',
        "LINE1:inc_avg#$FullBlue:Read     ",
        'GPRINT:inc_avg:AVERAGE:%5.2lf%s',
        'GPRINT:inc_max:MAX:%5.2lf%s',
        'GPRINT:inc_avg:LAST:%5.2lf%s\l', ];
    $GraphDefs['disk_ops'] = [
        //'-v', 'Ops/s',
        '--units=si',
        'DEF:out_min={file}:write:MIN',
        'DEF:out_avg={file}:write:AVERAGE',
        'DEF:out_max={file}:write:MAX',
        'DEF:inc_min={file}:read:MIN',
        'DEF:inc_avg={file}:read:AVERAGE',
        'DEF:inc_max={file}:read:MAX',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'COMMENT:                   Avg      Max       Cur\l',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Written     ",
        'GPRINT:out_avg:AVERAGE:%6.2lf ',
        'GPRINT:out_max:MAX:%6.2lf ',
        'GPRINT:out_avg:LAST:%6.2lf\l',
        "LINE1:inc_avg#$FullBlue:Read        ",
        'GPRINT:inc_avg:AVERAGE:%6.2lf ',
        'GPRINT:inc_max:MAX:%6.2lf ',
        'GPRINT:inc_avg:LAST:%6.2lf\l', ];
    $GraphDefs['disk_time'] = [
        //'-v', 'Seconds/s',
        'DEF:out_min_raw={file}:write:MIN',
        'DEF:out_avg_raw={file}:write:AVERAGE',
        'DEF:out_max_raw={file}:write:MAX',
        'DEF:inc_min_raw={file}:read:MIN',
        'DEF:inc_avg_raw={file}:read:AVERAGE',
        'DEF:inc_max_raw={file}:read:MAX',
        'CDEF:out_min=out_min_raw,1000,/',
        'CDEF:out_avg=out_avg_raw,1000,/',
        'CDEF:out_max=out_max_raw,1000,/',
        'CDEF:inc_min=inc_min_raw,1000,/',
        'CDEF:inc_avg=inc_avg_raw,1000,/',
        'CDEF:inc_max=inc_max_raw,1000,/',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'COMMENT:                 Avg       Max       Cur\l',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Written   ",
        'GPRINT:out_avg:AVERAGE:%5.1lf%ss ',
        'GPRINT:out_max:MAX:%5.1lf%ss ',
        'GPRINT:out_avg:LAST:%5.1lf%ss\l',
        "LINE1:inc_avg#$FullBlue:Read      ",
        'GPRINT:inc_avg:AVERAGE:%5.1lf%ss ',
        'GPRINT:inc_max:MAX:%5.1lf%ss ',
        'GPRINT:inc_avg:LAST:%5.1lf%ss\l', ];
    $GraphDefs['dns_traffic'] = [
        'DEF:rsp_min_raw={file}:responses:MIN',
        'DEF:rsp_avg_raw={file}:responses:AVERAGE',
        'DEF:rsp_max_raw={file}:responses:MAX',
        'DEF:qry_min_raw={file}:queries:MIN',
        'DEF:qry_avg_raw={file}:queries:AVERAGE',
        'DEF:qry_max_raw={file}:queries:MAX',
        'CDEF:rsp_min=rsp_min_raw,8,*',
        'CDEF:rsp_avg=rsp_avg_raw,8,*',
        'CDEF:rsp_max=rsp_max_raw,8,*',
        'CDEF:qry_min=qry_min_raw,8,*',
        'CDEF:qry_avg=qry_avg_raw,8,*',
        'CDEF:qry_max=qry_max_raw,8,*',
        'CDEF:overlap=rsp_avg,qry_avg,GT,qry_avg,rsp_avg,IF',
        'CDEF:mytime=rsp_avg_raw,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:rsp_avg_sample=rsp_avg_raw,UN,0,rsp_avg_raw,IF,sample_len,*',
        'CDEF:rsp_avg_sum=PREV,UN,0,PREV,IF,rsp_avg_sample,+',
        'CDEF:qry_avg_sample=qry_avg_raw,UN,0,qry_avg_raw,IF,sample_len,*',
        'CDEF:qry_avg_sum=PREV,UN,0,PREV,IF,qry_avg_sample,+',
        "AREA:rsp_avg#$HalfGreen",
        "AREA:qry_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:rsp_avg#$FullGreen:Responses",
        'GPRINT:rsp_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:rsp_max:MAX:%5.1lf%s',
        'GPRINT:rsp_avg:LAST:%5.1lf%s Last',
        'GPRINT:rsp_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
        "LINE1:qry_avg#$FullBlue:Queries  ",
        //          'GPRINT:qry_min:MIN:%5.1lf %s ',
        'GPRINT:qry_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:qry_max:MAX:%5.1lf%s',
        'GPRINT:qry_avg:LAST:%5.1lf%s Last',
        'GPRINT:qry_avg_sum:LAST:(ca. %5.1lf%sB Total)\l', ];
    $GraphDefs['email_count'] = [
        //'-v', 'Mails',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfMagenta",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullMagenta:Count ",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['files'] = $GraphDefs['email_count'];
    $GraphDefs['email_size'] = [
        //'-v', 'Bytes',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfMagenta",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullMagenta:Count ",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['bytes'] = $GraphDefs['email_size'];
    $GraphDefs['spam_score'] = [
        //'-v', 'Score',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Score ",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['spam_check'] = [
        'DEF:avg={file}:hits:AVERAGE',
        'DEF:min={file}:hits:MIN',
        'DEF:max={file}:hits:MAX',
        "AREA:max#$HalfMagenta",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullMagenta:Count ",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['entropy'] = [
        //'-v', 'Bits',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        'COMMENT:         Min       Avg       Max       Cur\l',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:   ",
        'GPRINT:min:MIN:%4.0lfbit ',
        'GPRINT:avg:AVERAGE:%4.0lfbit ',
        'GPRINT:max:MAX:%4.0lfbit',
        'GPRINT:avg:LAST:%4.0lfbit\l', ];
    $GraphDefs['fanspeed'] = [
        //'-v', 'RPM',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfMagenta",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullMagenta:RPM",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['frequency'] = [
        //'-v', 'Hertz',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        'AREA:max#b5b5b5',
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Frequency [Hz]",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['time_dispersion'] = [ // NTPd
        'DEF:ppm_avg={file}:seconds:AVERAGE',
        'DEF:ppm_min={file}:seconds:MIN',
        'DEF:ppm_max={file}:seconds:MAX',
        'COMMENT:Seconds        Min    Avg     Max    Cur\l',
        'AREA:ppm_max#b5b5b5',
        "AREA:ppm_min#$Canvas",
        "LINE1:ppm_avg#$FullBlue:Delay    ",
        'GPRINT:ppm_min:MIN:%5.2lf',
        'GPRINT:ppm_avg:AVERAGE:%5.2lf',
        'GPRINT:ppm_max:MAX:%5.2lf',
        'GPRINT:ppm_avg:LAST:%5.2lf', ];
    $GraphDefs['delay'] = [ // NTPd
        'DEF:ppm_avg={file}:seconds:AVERAGE',
        'DEF:ppm_min={file}:seconds:MIN',
        'DEF:ppm_max={file}:seconds:MAX',
        'COMMENT:Seconds        Min    Avg     Max    Cur\l',
        "AREA:ppm_max#$HalfBlue",
        "AREA:ppm_min#$Canvas",
        "LINE1:ppm_avg#$FullBlue:Delay    ",
        'GPRINT:ppm_min:MIN:%5.2lf',
        'GPRINT:ppm_avg:AVERAGE:%5.2lf',
        'GPRINT:ppm_max:MAX:%5.2lf',
        'GPRINT:ppm_avg:LAST:%5.2lf', ];
    $GraphDefs['frequency_offset'] = [ // NTPd
        'DEF:ppm_avg={file}:ppm:AVERAGE',
        'DEF:ppm_min={file}:ppm:MIN',
        'DEF:ppm_max={file}:ppm:MAX',
        'COMMENT:             Min     Avg      Max     Cur\l',
        "AREA:ppm_max#$HalfBlue",
        "AREA:ppm_min#$Canvas",
        "LINE1:ppm_avg#$FullBlue:Freq Hz",
        'GPRINT:ppm_min:MIN:%5.2lf ',
        'GPRINT:ppm_avg:AVERAGE:%5.2lf ',
        'GPRINT:ppm_max:MAX:%5.2lf ',
        'GPRINT:ppm_avg:LAST:%5.2lf', ];
    $GraphDefs['gauge'] = [
        //'-v', 'Exec value',
        'DEF:temp_avg={file}:value:AVERAGE',
        'DEF:temp_min={file}:value:MIN',
        'DEF:temp_max={file}:value:MAX',
        "AREA:temp_max#$HalfBlue",
        "AREA:temp_min#$Canvas",
        "LINE1:temp_avg#$FullBlue:Exec value",
        'GPRINT:temp_min:MIN:%6.2lf',
        'GPRINT:temp_avg:AVERAGE:%6.2lf',
        'GPRINT:temp_max:MAX:%6.2lf',
        'GPRINT:temp_avg:LAST:%6.2lf\l', ];
    $GraphDefs['hddtemp'] = [
        //'-v', '°C',
        'DEF:temp_avg={file}:value:AVERAGE',
        'DEF:temp_min={file}:value:MIN',
        'DEF:temp_max={file}:value:MAX',
        "AREA:temp_max#$HalfRed",
        "AREA:temp_min#$Canvas",
        "LINE1:temp_avg#$FullRed:Temperature",
        'GPRINT:temp_min:MIN:%4.1lf',
        'GPRINT:temp_avg:AVERAGE:%4.1lf',
        'GPRINT:temp_max:MAX:%4.1lf',
        'GPRINT:temp_avg:LAST:%4.1lf\l', ];
    $GraphDefs['humidity'] = [
        //'-v', 'Percent',
        'DEF:temp_avg={file}:value:AVERAGE',
        'DEF:temp_min={file}:value:MIN',
        'DEF:temp_max={file}:value:MAX',
        "AREA:temp_max#$HalfGreen",
        "AREA:temp_min#$Canvas",
        "LINE1:temp_avg#$FullGreen:Temperature",
        'GPRINT:temp_min:MIN:%4.1lf%% ',
        'GPRINT:temp_avg:AVERAGE:%4.1lf%% ',
        'GPRINT:temp_max:MAX:%4.1lf%%',
        'GPRINT:temp_avg:LAST:%4.1lf%%\l', ];
    $GraphDefs['if_errors'] = [
        //'-v', 'Errors/s',
        '--units=si',
        'DEF:tx_min={file}:tx:MIN',
        'DEF:tx_avg={file}:tx:AVERAGE',
        'DEF:tx_max={file}:tx:MAX',
        'DEF:rx_min={file}:rx:MIN',
        'DEF:rx_avg={file}:rx:AVERAGE',
        'DEF:rx_max={file}:rx:MAX',
        'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
        'CDEF:mytime=tx_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
        'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
        'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
        'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
        "AREA:tx_avg#$HalfGreen",
        "AREA:rx_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:tx_avg#$FullGreen:TX",
        'GPRINT:tx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:tx_max:MAX:%5.1lf%s',
        'GPRINT:tx_avg:LAST:%5.1lf%s Last',
        'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
        "LINE1:rx_avg#$FullBlue:RX",
        //          'GPRINT:rx_min:MIN:%5.1lf %s ',
        'GPRINT:rx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:rx_max:MAX:%5.1lf%s',
        'GPRINT:rx_avg:LAST:%5.1lf%s Last',
        'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l', ];
    $GraphDefs['if_collisions'] = [
        //'-v', 'Collisions/s', '--units=si',
        'DEF:min_raw={file}:value:MIN',
        'DEF:avg_raw={file}:value:AVERAGE',
        'DEF:max_raw={file}:value:MAX',
        'CDEF:min=min_raw,8,*',
        'CDEF:avg=avg_raw,8,*',
        'CDEF:max=max_raw,8,*',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Collisions/s",
        'GPRINT:min:MIN:%5.1lf %s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['if_dropped'] = [
        //'-v', 'Packets/s',
        '--units=si',
        'DEF:tx_min={file}:tx:MIN',
        'DEF:tx_avg={file}:tx:AVERAGE',
        'DEF:tx_max={file}:tx:MAX',
        'DEF:rx_min={file}:rx:MIN',
        'DEF:rx_avg={file}:rx:AVERAGE',
        'DEF:rx_max={file}:rx:MAX',
        'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
        'CDEF:mytime=tx_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
        'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
        'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
        'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
        "AREA:tx_avg#$HalfGreen",
        "AREA:rx_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:tx_avg#$FullGreen:TX",
        'GPRINT:tx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:tx_max:MAX:%5.1lf%s',
        'GPRINT:tx_avg:LAST:%5.1lf%s Last',
        'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
        "LINE1:rx_avg#$FullBlue:RX",
        //          'GPRINT:rx_min:MIN:%5.1lf %s ',
        'GPRINT:rx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:rx_max:MAX:%5.1lf%s',
        'GPRINT:rx_avg:LAST:%5.1lf%s Last',
        'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l', ];
    $GraphDefs['if_packets'] = [
        //'-v', 'Packets/s',
        '--units=si',
        'DEF:tx_min={file}:tx:MIN',
        'DEF:tx_avg={file}:tx:AVERAGE',
        'DEF:tx_max={file}:tx:MAX',
        'DEF:rx_min={file}:rx:MIN',
        'DEF:rx_avg={file}:rx:AVERAGE',
        'DEF:rx_max={file}:rx:MAX',
        'CDEF:overlap=tx_avg,rx_avg,GT,rx_avg,tx_avg,IF',
        'CDEF:mytime=tx_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:tx_avg_sample=tx_avg,UN,0,tx_avg,IF,sample_len,*',
        'CDEF:tx_avg_sum=PREV,UN,0,PREV,IF,tx_avg_sample,+',
        'CDEF:rx_avg_sample=rx_avg,UN,0,rx_avg,IF,sample_len,*',
        'CDEF:rx_avg_sum=PREV,UN,0,PREV,IF,rx_avg_sample,+',
        "AREA:tx_avg#$HalfGreen",
        "AREA:rx_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:tx_avg#$FullGreen:TX",
        'GPRINT:tx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:tx_max:MAX:%5.1lf%s',
        'GPRINT:tx_avg:LAST:%5.1lf%s Last',
        'GPRINT:tx_avg_sum:LAST:(ca. %4.0lf%s Total)\l',
        "LINE1:rx_avg#$FullBlue:RX",
        //          'GPRINT:rx_min:MIN:%5.1lf %s ',
        'GPRINT:rx_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:rx_max:MAX:%5.1lf%s',
        'GPRINT:rx_avg:LAST:%5.1lf%s Last',
        'GPRINT:rx_avg_sum:LAST:(ca. %4.0lf%s Total)\l', ];
    $GraphDefs['if_rx_errors'] = [
        '-v', 'Errors/s', '--units=si',
        'DEF:min={file}:value:MIN',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:max={file}:value:MAX',
        'CDEF:mytime=avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:avg_sample=avg,UN,0,avg,IF,sample_len,*',
        'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
        "AREA:avg#$HalfBlue",
        "LINE1:avg#$FullBlue:Errors/s",
        'GPRINT:avg:AVERAGE:%3.1lf%s ',
        'GPRINT:max:MAX:%3.1lf%s',
        'GPRINT:avg:LAST:%3.1lf%s Last',
        'GPRINT:avg_sum:LAST:(ca. %2.0lf%s Total)\l', ];
    $GraphDefs['ipt_bytes'] = [
        //'-v', 'Bits/s',
        'DEF:min_raw={file}:value:MIN',
        'DEF:avg_raw={file}:value:AVERAGE',
        'DEF:max_raw={file}:value:MAX',
        'CDEF:min=min_raw,8,*',
        'CDEF:avg=avg_raw,8,*',
        'CDEF:max=max_raw,8,*',
        'CDEF:mytime=avg_raw,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:avg_sample=avg_raw,UN,0,avg_raw,IF,sample_len,*',
        'CDEF:avg_sum=PREV,UN,0,PREV,IF,avg_sample,+',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Bits/s",
        //          'GPRINT:min:MIN:%5.1lf %s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s Last',
        'GPRINT:avg_sum:LAST:(ca. %5.1lf%sB Total)\l', ];
    $GraphDefs['ipt_packets'] = [
        //'-v', 'Packets/s',
        'DEF:min_raw={file}:value:MIN',
        'DEF:avg_raw={file}:value:AVERAGE',
        'DEF:max_raw={file}:value:MAX',
        'CDEF:min=min_raw,8,*',
        'CDEF:avg=avg_raw,8,*',
        'CDEF:max=max_raw,8,*',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Packets/s",
        'GPRINT:min:MIN:%5.1lf %s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['irq'] = [
        //'-v', 'Issues/s',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        'COMMENT:Seconds        Min     Avg      Max     Cur\l',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Issues/s",
        'GPRINT:min:MIN:%6.2lf',
        'GPRINT:avg:AVERAGE:%6.2lf',
        'GPRINT:max:MAX:%6.2lf',
        'GPRINT:avg:LAST:%6.2lf\l', ];
    $GraphDefs['load'] = [
        //'-v', 'System load',
        'DEF:s_avg={file}:shortterm:AVERAGE',
        'DEF:s_min={file}:shortterm:MIN',
        'DEF:s_max={file}:shortterm:MAX',
        'DEF:m_avg={file}:midterm:AVERAGE',
        'DEF:m_min={file}:midterm:MIN',
        'DEF:m_max={file}:midterm:MAX',
        'DEF:l_avg={file}:longterm:AVERAGE',
        'DEF:l_min={file}:longterm:MIN',
        'DEF:l_max={file}:longterm:MAX',
        "AREA:s_max#$HalfGreen",
        "AREA:s_min#$Canvas",
        "LINE1:s_avg#$FullGreen: 1m average ",
        'GPRINT:s_min:MIN:%5.2lf',
        'GPRINT:s_avg:AVERAGE:%5.2lf',
        'GPRINT:s_max:MAX:%5.2lf',
        'GPRINT:s_avg:LAST:%5.2lf\\j',
        "LINE1:m_avg#$FullBlue: 5m average ",
        'GPRINT:m_min:MIN:%5.2lf',
        'GPRINT:m_avg:AVERAGE:%5.2lf',
        'GPRINT:m_max:MAX:%5.2lf',
        'GPRINT:m_avg:LAST:%5.2lf\\j',
        "LINE1:l_avg#$FullRed:15m average",
        'GPRINT:l_min:MIN:%5.2lf',
        'GPRINT:l_avg:AVERAGE:%5.2lf',
        'GPRINT:l_max:MAX:%5.2lf',
        'GPRINT:l_avg:LAST:%5.2lf\\j', ];
    $GraphDefs['load_percent'] = [
        'DEF:avg={file}:percent:AVERAGE',
        'DEF:min={file}:percent:MIN',
        'DEF:max={file}:percent:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Load",
        'GPRINT:min:MIN:%5.1lf%s%% ',
        'GPRINT:avg:AVERAGE:%5.1lf%s%% ',
        'GPRINT:max:MAX:%5.1lf%s%%',
        'GPRINT:avg:LAST:%5.1lf%s%%\l', ];
    $GraphDefs['mails'] = [
        'DEF:rawgood={file}:good:AVERAGE',
        'DEF:rawspam={file}:spam:AVERAGE',
        'CDEF:good=rawgood,UN,0,rawgood,IF',
        'CDEF:spam=rawspam,UN,0,rawspam,IF',
        'CDEF:negspam=spam,-1,*',
        "AREA:good#$HalfGreen",
        "LINE1:good#$FullGreen:Good mails",
        'GPRINT:good:AVERAGE:%4.1lf',
        'GPRINT:good:MAX:%4.1lf',
        'GPRINT:good:LAST:%4.1lf Last\n',
        "AREA:negspam#$HalfRed",
        "LINE1:negspam#$FullRed:Spam mails",
        'GPRINT:spam:AVERAGE:%4.1lf',
        'GPRINT:spam:MAX:%4.1lf',
        'GPRINT:spam:LAST:%4.1lf',
        'HRULE:0#000000', ];
    $GraphDefs['memory'] = [
        '-b', '1024',
        //'-v', 'Bytes',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Memory",
        'GPRINT:min:MIN:%5.1lf%sbyte ',
        'GPRINT:avg:AVERAGE:%5.1lf%sbyte ',
        'GPRINT:max:MAX:%5.1lf%sbyte',
        'GPRINT:avg:LAST:%5.1lf%sbyte\l', ];
    $GraphDefs['old_memory'] = [
        'DEF:used_avg={file}:used:AVERAGE',
        'DEF:free_avg={file}:free:AVERAGE',
        'DEF:buffers_avg={file}:buffers:AVERAGE',
        'DEF:cached_avg={file}:cached:AVERAGE',
        'DEF:used_min={file}:used:MIN',
        'DEF:free_min={file}:free:MIN',
        'DEF:buffers_min={file}:buffers:MIN',
        'DEF:cached_min={file}:cached:MIN',
        'DEF:used_max={file}:used:MAX',
        'DEF:free_max={file}:free:MAX',
        'DEF:buffers_max={file}:buffers:MAX',
        'DEF:cached_max={file}:cached:MAX',
        'CDEF:cached_avg_nn=cached_avg,UN,0,cached_avg,IF',
        'CDEF:buffers_avg_nn=buffers_avg,UN,0,buffers_avg,IF',
        'CDEF:free_cached_buffers_used=free_avg,cached_avg_nn,+,buffers_avg_nn,+,used_avg,+',
        'CDEF:cached_buffers_used=cached_avg,buffers_avg_nn,+,used_avg,+',
        'CDEF:buffers_used=buffers_avg,used_avg,+',
        "AREA:free_cached_buffers_used#$HalfGreen",
        "AREA:cached_buffers_used#$HalfBlue",
        "AREA:buffers_used#$HalfYellow",
        "AREA:used_avg#$HalfRed",
        "LINE1:free_cached_buffers_used#$FullGreen:Free        ",
        'GPRINT:free_min:MIN:%5.1lf%s ',
        'GPRINT:free_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:free_max:MAX:%5.1lf%s',
        'GPRINT:free_avg:LAST:%5.1lf%s Last\n',
        "LINE1:cached_buffers_used#$FullBlue:Page cache  ",
        'GPRINT:cached_min:MIN:%5.1lf%s ',
        'GPRINT:cached_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:cached_max:MAX:%5.1lf%s',
        'GPRINT:cached_avg:LAST:%5.1lf%s Last\n',
        "LINE1:buffers_used#$FullYellow:Buffer cache",
        'GPRINT:buffers_min:MIN:%5.1lf%s ',
        'GPRINT:buffers_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:buffers_max:MAX:%5.1lf%s',
        'GPRINT:buffers_avg:LAST:%5.1lf%s Last\n',
        "LINE1:used_avg#$FullRed:Used        ",
        'GPRINT:used_min:MIN:%5.1lf%s ',
        'GPRINT:used_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:used_max:MAX:%5.1lf%s',
        'GPRINT:used_avg:LAST:%5.1lf%s Last', ];
    $GraphDefs['mysql_commands'] = [
        //'-v', 'Issues/s',
        'DEF:val_avg={file}:value:AVERAGE',
        'DEF:val_min={file}:value:MIN',
        'DEF:val_max={file}:value:MAX',
        "AREA:val_max#$HalfBlue",
        "AREA:val_min#$Canvas",
        "LINE1:val_avg#$FullBlue:Issues/s",
        'GPRINT:val_min:MIN:%5.2lf',
        'GPRINT:val_avg:AVERAGE:%5.2lf',
        'GPRINT:val_max:MAX:%5.2lf',
        'GPRINT:val_avg:LAST:%5.2lf', ];
    $GraphDefs['mysql_handler'] = [
        //'-v', 'Issues/s',
        'DEF:val_avg={file}:value:AVERAGE',
        'DEF:val_min={file}:value:MIN',
        'DEF:val_max={file}:value:MAX',
        "AREA:val_max#$HalfBlue",
        "AREA:val_min#$Canvas",
        "LINE1:val_avg#$FullBlue:Issues/s",
        'GPRINT:val_min:MIN:%5.2lf',
        'GPRINT:val_avg:AVERAGE:%5.2lf',
        'GPRINT:val_max:MAX:%5.2lf',
        'GPRINT:val_avg:LAST:%5.2lf', ];
    $GraphDefs['mysql_octets'] = [
        //'-v', 'Bits/s',
        'DEF:dout_min={file}:tx:MIN',
        'DEF:dout_avg={file}:tx:AVERAGE',
        'DEF:dout_max={file}:tx:MAX',
        'DEF:inc_min={file}:rx:MIN',
        'DEF:inc_avg={file}:rx:AVERAGE',
        'DEF:inc_max={file}:rx:MAX',
        'CDEF:out_min=dout_min,-1,*',
        'CDEF:out_avg=dout_avg,-1,*',
        'CDEF:out_max=dout_max,-1,*',
        'CDEF:mytime=out_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:out_avg_sample=dout_avg,UN,0,dout_avg,IF,sample_len,*',
        'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
        'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
        'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
        'CDEF:out_bit_min=dout_min,8,*',
        'CDEF:dout_bit_avg=dout_avg,8,*',
        'CDEF:out_bit_avg=out_avg,8,*',
        'CDEF:out_bit_max=dout_max,8,*',
        'CDEF:inc_bit_min=inc_min,8,*',
        'CDEF:inc_bit_avg=inc_avg,8,*',
        'CDEF:inc_bit_max=inc_max,8,*',
        "AREA:out_bit_avg#$HalfGreen",
        "AREA:inc_bit_avg#$HalfBlue",
        'COMMENT:Bytes         Cur     Ave      Max     Min\l',
        "LINE1:out_bit_avg#$FullGreen:Written",
        'GPRINT:dout_bit_avg:LAST:%5.1lf%s',
        'GPRINT:dout_bit_avg:AVERAGE:%5.1lf%s',
        'GPRINT:out_bit_max:MAX:%5.1lf%s',
        'GPRINT:out_bit_min:MIN:%5.1lf%s\l',
        'GPRINT:out_avg_sum:LAST:            (ca. %5.1lf%sB Total)\l',
        "LINE1:inc_bit_avg#$FullBlue:Read   ",
        'GPRINT:inc_bit_avg:LAST:%5.1lf%s',
        'GPRINT:inc_bit_avg:AVERAGE:%5.1lf%s',
        'GPRINT:inc_bit_max:MAX:%5.1lf%s',
        'GPRINT:inc_bit_min:MIN:%5.1lf%s\l',
        'GPRINT:inc_avg_sum:LAST:            (ca. %5.1lf%sB Total)\l', ];
    $GraphDefs['mysql_qcache'] = [
        //'-v', 'Queries/s',
        'DEF:hits_min={file}:hits:MIN',
        'DEF:hits_avg={file}:hits:AVERAGE',
        'DEF:hits_max={file}:hits:MAX',
        'DEF:inserts_min={file}:inserts:MIN',
        'DEF:inserts_avg={file}:inserts:AVERAGE',
        'DEF:inserts_max={file}:inserts:MAX',
        'DEF:not_cached_min={file}:not_cached:MIN',
        'DEF:not_cached_avg={file}:not_cached:AVERAGE',
        'DEF:not_cached_max={file}:not_cached:MAX',
        'DEF:lowmem_prunes_min={file}:lowmem_prunes:MIN',
        'DEF:lowmem_prunes_avg={file}:lowmem_prunes:AVERAGE',
        'DEF:lowmem_prunes_max={file}:lowmem_prunes:MAX',
        'DEF:queries_min={file}:queries_in_cache:MIN',
        'DEF:queries_avg={file}:queries_in_cache:AVERAGE',
        'DEF:queries_max={file}:queries_in_cache:MAX',
        'CDEF:unknown=queries_avg,UNKN,+',
        'CDEF:not_cached_agg=hits_avg,inserts_avg,+,not_cached_avg,+',
        'CDEF:inserts_agg=hits_avg,inserts_avg,+',
        'CDEF:hits_agg=hits_avg',
        'COMMENT:Threads       Min     Ave     Max     Cur\l',
        "AREA:not_cached_agg#$HalfYellow",
        "AREA:inserts_agg#$HalfBlue",
        "AREA:hits_agg#$HalfGreen",
        "LINE1:not_cached_agg#$FullYellow:Misses ",
        'GPRINT:not_cached_min:MIN:%5.1lf%s',
        'GPRINT:not_cached_avg:AVERAGE:%5.1lf%s',
        'GPRINT:not_cached_max:MAX:%5.1lf%s',
        'GPRINT:not_cached_avg:LAST:%5.1lf%s\l',
        "LINE1:inserts_agg#$FullBlue:Inserts",
        'GPRINT:inserts_min:MIN:%5.1lf%s',
        'GPRINT:inserts_avg:AVERAGE:%5.1lf%s',
        'GPRINT:inserts_max:MAX:%5.1lf%s',
        'GPRINT:inserts_avg:LAST:%5.1lf%s\l',
        "LINE1:hits_agg#$FullGreen:Hits   ",
        'GPRINT:hits_min:MIN:%5.1lf%s',
        'GPRINT:hits_avg:AVERAGE:%5.1lf%s',
        'GPRINT:hits_max:MAX:%5.1lf%s',
        'GPRINT:hits_avg:LAST:%5.1lf%s\l',
        "LINE1:lowmem_prunes_avg#$FullRed:Prunes ",
        'GPRINT:lowmem_prunes_min:MIN:%5.1lf%s',
        'GPRINT:lowmem_prunes_avg:AVERAGE:%5.1lf%s',
        'GPRINT:lowmem_prunes_max:MAX:%5.1lf%s',
        'GPRINT:lowmem_prunes_avg:LAST:%5.1lf%s\l',
        "LINE1:unknown#$Canvas:In Cache",
        'GPRINT:queries_min:MIN:%4.0lf%s ',
        'GPRINT:queries_avg:AVERAGE:%4.0lf%s ',
        'GPRINT:queries_max:MAX:%4.0lf%s ',
        'GPRINT:queries_avg:LAST:%4.0lf%s\l', ];
    $GraphDefs['mysql_threads'] = [
        //'-v', 'Threads',
        'DEF:running_min={file}:running:MIN',
        'DEF:running_avg={file}:running:AVERAGE',
        'DEF:running_max={file}:running:MAX',
        'DEF:connected_min={file}:connected:MIN',
        'DEF:connected_avg={file}:connected:AVERAGE',
        'DEF:connected_max={file}:connected:MAX',
        'DEF:cached_min={file}:cached:MIN',
        'DEF:cached_avg={file}:cached:AVERAGE',
        'DEF:cached_max={file}:cached:MAX',
        'DEF:created_min={file}:created:MIN',
        'DEF:created_avg={file}:created:AVERAGE',
        'DEF:created_max={file}:created:MAX',
        'CDEF:unknown=created_avg,UNKN,+',
        'CDEF:cached_agg=connected_avg,cached_avg,+',
        'COMMENT:Threads         Min    Ave    Max    Cur\l',
        "AREA:cached_agg#$HalfGreen",
        "AREA:connected_avg#$HalfBlue",
        "AREA:running_avg#$HalfRed",
        "LINE1:cached_agg#$FullGreen:Cached   ",
        'GPRINT:cached_min:MIN:%5.1lf',
        'GPRINT:cached_avg:AVERAGE:%5.1lf',
        'GPRINT:cached_max:MAX:%5.1lf',
        'GPRINT:cached_avg:LAST:%5.1lf\l',
        "LINE1:connected_avg#$FullBlue:Connected",
        'GPRINT:connected_min:MIN:%5.1lf',
        'GPRINT:connected_avg:AVERAGE:%5.1lf',
        'GPRINT:connected_max:MAX:%5.1lf',
        'GPRINT:connected_avg:LAST:%5.1lf\l',
        "LINE1:running_avg#$FullRed:Running  ",
        'GPRINT:running_min:MIN:%5.1lf',
        'GPRINT:running_avg:AVERAGE:%5.1lf',
        'GPRINT:running_max:MAX:%5.1lf',
        'GPRINT:running_avg:LAST:%5.1lf\l',
        "LINE1:unknown#$Canvas:Created  ",
        'GPRINT:created_min:MIN:%5.0lf',
        'GPRINT:created_avg:AVERAGE:%5.0lf',
        'GPRINT:created_max:MAX:%5.0lf',
        'GPRINT:created_avg:LAST:%5.0lf\l', ];
    $GraphDefs['nfs_procedure'] = [
        //'-v', 'Issues/s',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Issues/s",
        'GPRINT:min:MIN:%6.2lf',
        'GPRINT:avg:AVERAGE:%6.2lf',
        'GPRINT:max:MAX:%6.2lf',
        'GPRINT:avg:LAST:%6.2lf\l', ];
    $GraphDefs['nfs3_procedures'] = [
        'DEF:null_avg={file}:null:AVERAGE',
        'DEF:getattr_avg={file}:getattr:AVERAGE',
        'DEF:setattr_avg={file}:setattr:AVERAGE',
        'DEF:lookup_avg={file}:lookup:AVERAGE',
        'DEF:access_avg={file}:access:AVERAGE',
        'DEF:readlink_avg={file}:readlink:AVERAGE',
        'DEF:read_avg={file}:read:AVERAGE',
        'DEF:write_avg={file}:write:AVERAGE',
        'DEF:create_avg={file}:create:AVERAGE',
        'DEF:mkdir_avg={file}:mkdir:AVERAGE',
        'DEF:symlink_avg={file}:symlink:AVERAGE',
        'DEF:mknod_avg={file}:mknod:AVERAGE',
        'DEF:remove_avg={file}:remove:AVERAGE',
        'DEF:rmdir_avg={file}:rmdir:AVERAGE',
        'DEF:rename_avg={file}:rename:AVERAGE',
        'DEF:link_avg={file}:link:AVERAGE',
        'DEF:readdir_avg={file}:readdir:AVERAGE',
        'DEF:readdirplus_avg={file}:readdirplus:AVERAGE',
        'DEF:fsstat_avg={file}:fsstat:AVERAGE',
        'DEF:fsinfo_avg={file}:fsinfo:AVERAGE',
        'DEF:pathconf_avg={file}:pathconf:AVERAGE',
        'DEF:commit_avg={file}:commit:AVERAGE',
        'DEF:null_max={file}:null:MAX',
        'DEF:getattr_max={file}:getattr:MAX',
        'DEF:setattr_max={file}:setattr:MAX',
        'DEF:lookup_max={file}:lookup:MAX',
        'DEF:access_max={file}:access:MAX',
        'DEF:readlink_max={file}:readlink:MAX',
        'DEF:read_max={file}:read:MAX',
        'DEF:write_max={file}:write:MAX',
        'DEF:create_max={file}:create:MAX',
        'DEF:mkdir_max={file}:mkdir:MAX',
        'DEF:symlink_max={file}:symlink:MAX',
        'DEF:mknod_max={file}:mknod:MAX',
        'DEF:remove_max={file}:remove:MAX',
        'DEF:rmdir_max={file}:rmdir:MAX',
        'DEF:rename_max={file}:rename:MAX',
        'DEF:link_max={file}:link:MAX',
        'DEF:readdir_max={file}:readdir:MAX',
        'DEF:readdirplus_max={file}:readdirplus:MAX',
        'DEF:fsstat_max={file}:fsstat:MAX',
        'DEF:fsinfo_max={file}:fsinfo:MAX',
        'DEF:pathconf_max={file}:pathconf:MAX',
        'DEF:commit_max={file}:commit:MAX',
        'CDEF:other_avg=null_avg,readlink_avg,create_avg,mkdir_avg,symlink_avg,mknod_avg,remove_avg,rmdir_avg,rename_avg,link_avg,readdir_avg,readdirplus_avg,fsstat_avg,fsinfo_avg,pathconf_avg,+,+,+,+,+,+,+,+,+,+,+,+,+,+',
        'CDEF:other_max=null_max,readlink_max,create_max,mkdir_max,symlink_max,mknod_max,remove_max,rmdir_max,rename_max,link_max,readdir_max,readdirplus_max,fsstat_max,fsinfo_max,pathconf_max,+,+,+,+,+,+,+,+,+,+,+,+,+,+',
        'CDEF:stack_read=read_avg',
        'CDEF:stack_getattr=stack_read,getattr_avg,+',
        'CDEF:stack_access=stack_getattr,access_avg,+',
        'CDEF:stack_lookup=stack_access,lookup_avg,+',
        'CDEF:stack_write=stack_lookup,write_avg,+',
        'CDEF:stack_commit=stack_write,commit_avg,+',
        'CDEF:stack_setattr=stack_commit,setattr_avg,+',
        'CDEF:stack_other=stack_setattr,other_avg,+',
        "AREA:stack_other#$HalfRed",
        "AREA:stack_setattr#$HalfGreen",
        "AREA:stack_commit#$HalfYellow",
        "AREA:stack_write#$HalfGreen",
        "AREA:stack_lookup#$HalfBlue",
        "AREA:stack_access#$HalfMagenta",
        "AREA:stack_getattr#$HalfCyan",
        "AREA:stack_read#$HalfBlue",
        "LINE1:stack_other#$FullRed:Other  ",
        'GPRINT:other_max:MAX:%5.1lf',
        'GPRINT:other_avg:AVERAGE:%5.1lf',
        'GPRINT:other_avg:LAST:%5.1lf\l',
        "LINE1:stack_setattr#$FullGreen:setattr",
        'GPRINT:setattr_max:MAX:%5.1lf',
        'GPRINT:setattr_avg:AVERAGE:%5.1lf',
        'GPRINT:setattr_avg:LAST:%5.1lf\l',
        "LINE1:stack_commit#$FullYellow:commit ",
        'GPRINT:commit_max:MAX:%5.1lf',
        'GPRINT:commit_avg:AVERAGE:%5.1lf',
        'GPRINT:commit_avg:LAST:%5.1lf\l',
        "LINE1:stack_write#$FullGreen:write  ",
        'GPRINT:write_max:MAX:%5.1lf',
        'GPRINT:write_avg:AVERAGE:%5.1lf',
        'GPRINT:write_avg:LAST:%5.1lf\l',
        "LINE1:stack_lookup#$FullBlue:lookup ",
        'GPRINT:lookup_max:MAX:%5.1lf',
        'GPRINT:lookup_avg:AVERAGE:%5.1lf',
        'GPRINT:lookup_avg:LAST:%5.1lf\l',
        "LINE1:stack_access#$FullMagenta:access ",
        'GPRINT:access_max:MAX:%5.1lf',
        'GPRINT:access_avg:AVERAGE:%5.1lf',
        'GPRINT:access_avg:LAST:%5.1lf\l',
        "LINE1:stack_getattr#$FullCyan:getattr",
        'GPRINT:getattr_max:MAX:%5.1lf',
        'GPRINT:getattr_avg:AVERAGE:%5.1lf',
        'GPRINT:getattr_avg:LAST:%5.1lf\l',
        "LINE1:stack_read#$FullBlue:read   ",
        'GPRINT:read_max:MAX:%5.1lf',
        'GPRINT:read_avg:AVERAGE:%5.1lf',
        'GPRINT:read_avg:LAST:%5.1lf\l', ];
    $GraphDefs['opcode'] = [
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Queries/s",
        'GPRINT:min:MIN:%9.3lf',
        'GPRINT:avg:AVERAGE:%9.3lf Average,',
        'GPRINT:max:MAX:%9.3lf',
        'GPRINT:avg:LAST:%9.3lf\l', ];
    $GraphDefs['partition'] = [
        'DEF:rbyte_avg={file}:rbytes:AVERAGE',
        'DEF:rbyte_min={file}:rbytes:MIN',
        'DEF:rbyte_max={file}:rbytes:MAX',
        'DEF:wbyte_avg={file}:wbytes:AVERAGE',
        'DEF:wbyte_min={file}:wbytes:MIN',
        'DEF:wbyte_max={file}:wbytes:MAX',
        'CDEF:overlap=wbyte_avg,rbyte_avg,GT,rbyte_avg,wbyte_avg,IF',
        "AREA:wbyte_avg#$HalfGreen",
        "AREA:rbyte_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",    "LINE1:wbyte_avg#$FullGreen:Write",
        'GPRINT:wbyte_min:MIN:%5.1lf%s ',
        'GPRINT:wbyte_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:wbyte_max:MAX:%5.1lf%s',
        'GPRINT:wbyte_avg:LAST:%5.1lf%s\l',
        "LINE1:rbyte_avg#$FullBlue:Read ",
        'GPRINT:rbyte_min:MIN:%5.1lf%s ',
        'GPRINT:rbyte_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:rbyte_max:MAX:%5.1lf%s',
        'GPRINT:rbyte_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['percent'] = [
        //'-v', 'Percent',
        'DEF:avg={file}:percent:AVERAGE',
        'DEF:min={file}:percent:MIN',
        'DEF:max={file}:percent:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Percent",
        'GPRINT:min:MIN:%5.1lf%% ',
        'GPRINT:avg:AVERAGE:%5.1lf%% ',
        'GPRINT:max:MAX:%5.1lf%%',
        'GPRINT:avg:LAST:%5.1lf%%\l', ];
    $GraphDefs['ping'] = [
        'DEF:ping_avg={file}:ping:AVERAGE',
        'DEF:ping_min={file}:ping:MIN',
        'DEF:ping_max={file}:ping:MAX',
        "AREA:ping_max#$HalfBlue",
        "AREA:ping_min#$Canvas",
        "LINE1:ping_avg#$FullBlue:Ping",
        'GPRINT:ping_min:MIN:%4.1lf ms ',
        'GPRINT:ping_avg:AVERAGE:%4.1lf ms ',
        'GPRINT:ping_max:MAX:%4.1lf ms',
        'GPRINT:ping_avg:LAST:%4.1lf ms Last', ];
    $GraphDefs['power'] = [
        //'-v', 'Watt',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Watt",
        'GPRINT:min:MIN:%5.1lf%sW ',
        'GPRINT:avg:AVERAGE:%5.1lf%sW ',
        'GPRINT:max:MAX:%5.1lf%sW',
        'GPRINT:avg:LAST:%5.1lf%sW\l', ];
    $GraphDefs['processes'] = [
        'DEF:running_avg={file}:running:AVERAGE',
        'DEF:running_min={file}:running:MIN',
        'DEF:running_max={file}:running:MAX',
        'DEF:sleeping_avg={file}:sleeping:AVERAGE',
        'DEF:sleeping_min={file}:sleeping:MIN',
        'DEF:sleeping_max={file}:sleeping:MAX',
        'DEF:zombies_avg={file}:zombies:AVERAGE',
        'DEF:zombies_min={file}:zombies:MIN',
        'DEF:zombies_max={file}:zombies:MAX',
        'DEF:stopped_avg={file}:stopped:AVERAGE',
        'DEF:stopped_min={file}:stopped:MIN',
        'DEF:stopped_max={file}:stopped:MAX',
        'DEF:paging_avg={file}:paging:AVERAGE',
        'DEF:paging_min={file}:paging:MIN',
        'DEF:paging_max={file}:paging:MAX',
        'DEF:blocked_avg={file}:blocked:AVERAGE',
        'DEF:blocked_min={file}:blocked:MIN',
        'DEF:blocked_max={file}:blocked:MAX',
        'CDEF:paging_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,blocked_avg,paging_avg,+,+,+,+,+',
        'CDEF:blocked_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,blocked_avg,+,+,+,+',
        'CDEF:zombies_acc=sleeping_avg,running_avg,stopped_avg,zombies_avg,+,+,+',
        'CDEF:stopped_acc=sleeping_avg,running_avg,stopped_avg,+,+',
        'CDEF:running_acc=sleeping_avg,running_avg,+',
        'CDEF:sleeping_acc=sleeping_avg',
        "AREA:paging_acc#$HalfYellow",
        "AREA:blocked_acc#$HalfCyan",
        "AREA:zombies_acc#$HalfRed",
        "AREA:stopped_acc#$HalfMagenta",
        "AREA:running_acc#$HalfGreen",
        "AREA:sleeping_acc#$HalfBlue",
        "LINE1:paging_acc#$FullYellow:Paging  ",
        'GPRINT:paging_min:MIN:%5.1lf',
        'GPRINT:paging_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:paging_max:MAX:%5.1lf',
        'GPRINT:paging_avg:LAST:%5.1lf\l',
        "LINE1:blocked_acc#$FullCyan:Blocked ",
        'GPRINT:blocked_min:MIN:%5.1lf',
        'GPRINT:blocked_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:blocked_max:MAX:%5.1lf',
        'GPRINT:blocked_avg:LAST:%5.1lf\l',
        "LINE1:zombies_acc#$FullRed:Zombies ",
        'GPRINT:zombies_min:MIN:%5.1lf',
        'GPRINT:zombies_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:zombies_max:MAX:%5.1lf',
        'GPRINT:zombies_avg:LAST:%5.1lf\l',
        "LINE1:stopped_acc#$FullMagenta:Stopped ",
        'GPRINT:stopped_min:MIN:%5.1lf',
        'GPRINT:stopped_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:stopped_max:MAX:%5.1lf',
        'GPRINT:stopped_avg:LAST:%5.1lf\l',
        "LINE1:running_acc#$FullGreen:Running ",
        'GPRINT:running_min:MIN:%5.1lf',
        'GPRINT:running_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:running_max:MAX:%5.1lf',
        'GPRINT:running_avg:LAST:%5.1lf\l',
        "LINE1:sleeping_acc#$FullBlue:Sleeping",
        'GPRINT:sleeping_min:MIN:%5.1lf',
        'GPRINT:sleeping_avg:AVERAGE:%5.1lf Average,',
        'GPRINT:sleeping_max:MAX:%5.1lf',
        'GPRINT:sleeping_avg:LAST:%5.1lf\l', ];
    $GraphDefs['ps_count'] = [
        //'-v', 'Processes',
        'DEF:procs_avg={file}:processes:AVERAGE',
        'DEF:procs_min={file}:processes:MIN',
        'DEF:procs_max={file}:processes:MAX',
        'DEF:thrds_avg={file}:threads:AVERAGE',
        'DEF:thrds_min={file}:threads:MIN',
        'DEF:thrds_max={file}:threads:MAX',
        "AREA:thrds_avg#$HalfBlue",
        "AREA:procs_avg#$HalfRed",
        "LINE1:thrds_avg#$FullBlue:Threads  ",
        'GPRINT:thrds_min:MIN:%5.1lf',
        'GPRINT:thrds_avg:AVERAGE:%5.1lf',
        'GPRINT:thrds_max:MAX:%5.1lf',
        'GPRINT:thrds_avg:LAST:%5.1lf\l',
        "LINE1:procs_avg#$FullRed:Processes",
        'GPRINT:procs_min:MIN:%5.1lf',
        'GPRINT:procs_avg:AVERAGE:%5.1lf',
        'GPRINT:procs_max:MAX:%5.1lf',
        'GPRINT:procs_avg:LAST:%5.1lf\l', ];
    $GraphDefs['ps_cputime'] = [
        //'-v', 'Jiffies',
        'DEF:user_avg_raw={file}:user:AVERAGE',
        'DEF:user_min_raw={file}:user:MIN',
        'DEF:user_max_raw={file}:user:MAX',
        'DEF:syst_avg_raw={file}:syst:AVERAGE',
        'DEF:syst_min_raw={file}:syst:MIN',
        'DEF:syst_max_raw={file}:syst:MAX',
        'CDEF:user_avg=user_avg_raw,1000000,/',
        'CDEF:user_min=user_min_raw,1000000,/',
        'CDEF:user_max=user_max_raw,1000000,/',
        'CDEF:syst_avg=syst_avg_raw,1000000,/',
        'CDEF:syst_min=syst_min_raw,1000000,/',
        'CDEF:syst_max=syst_max_raw,1000000,/',
        'CDEF:user_syst=syst_avg,UN,0,syst_avg,IF,user_avg,+',
        "AREA:user_syst#$HalfBlue",
        "AREA:syst_avg#$HalfRed",
        "LINE1:user_syst#$FullBlue:User  ",
        'GPRINT:user_min:MIN:%5.1lf%s ',
        'GPRINT:user_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:user_max:MAX:%5.1lf%s',
        'GPRINT:user_avg:LAST:%5.1lf%s\l',
        "LINE1:syst_avg#$FullRed:System",
        'GPRINT:syst_min:MIN:%5.1lf%s ',
        'GPRINT:syst_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:syst_max:MAX:%5.1lf%s',
        'GPRINT:syst_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['ps_pagefaults'] = [
        //'-v', 'Pagefaults/s',
        'DEF:minor_avg={file}:minflt:AVERAGE',
        'DEF:minor_min={file}:minflt:MIN',
        'DEF:minor_max={file}:minflt:MAX',
        'DEF:major_avg={file}:majflt:AVERAGE',
        'DEF:major_min={file}:majflt:MIN',
        'DEF:major_max={file}:majflt:MAX',
        'CDEF:minor_major=major_avg,UN,0,major_avg,IF,minor_avg,+',
        "AREA:minor_major#$HalfBlue",
        "AREA:major_avg#$HalfRed",
        "LINE1:minor_major#$FullBlue:Minor",
        'GPRINT:minor_min:MIN:%5.1lf%s ',
        'GPRINT:minor_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:minor_max:MAX:%5.1lf%s',
        'GPRINT:minor_avg:LAST:%5.1lf%s\l',
        "LINE1:major_avg#$FullRed:Major",
        'GPRINT:major_min:MIN:%5.1lf%s ',
        'GPRINT:major_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:major_max:MAX:%5.1lf%s',
        'GPRINT:major_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['ps_rss'] = [
        //'-v', 'Bytes',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:avg#$HalfBlue",
        "LINE1:avg#$FullBlue:RSS",
        'GPRINT:min:MIN:%5.1lf%s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['ps_state'] = [
        //'-v', 'Processes',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Processes",
        'GPRINT:min:MIN:%6.2lf',
        'GPRINT:avg:AVERAGE:%6.2lf',
        'GPRINT:max:MAX:%6.2lf',
        'GPRINT:avg:LAST:%6.2lf\l', ];
    $GraphDefs['qtype'] = [
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Queries/s",
        'GPRINT:min:MIN:%9.3lf',
        'GPRINT:avg:AVERAGE:%9.3lf Average,',
        'GPRINT:max:MAX:%9.3lf',
        'GPRINT:avg:LAST:%9.3lf\l', ];
    $GraphDefs['rcode'] = [
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Queries/s",
        'GPRINT:min:MIN:%9.3lf',
        'GPRINT:avg:AVERAGE:%9.3lf Average,',
        'GPRINT:max:MAX:%9.3lf',
        'GPRINT:avg:LAST:%9.3lf\l', ];
    $GraphDefs['swap'] = [
        //'-v', 'Bytes',
        '-b', '1024',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        'COMMENT:Bytes            Cur     Avg      Min     Max\l',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Bytes",
        'GPRINT:min:MIN:%6.2lf%sByte ',
        'GPRINT:avg:AVERAGE:%6.2lf%sByte ',
        'GPRINT:max:MAX:%6.2lf%sByte',
        'GPRINT:avg:LAST:%6.2lf%sByte\l', ];
    $GraphDefs['old_swap'] = [
        'DEF:used_avg={file}:used:AVERAGE',
        'DEF:used_min={file}:used:MIN',
        'DEF:used_max={file}:used:MAX',
        'DEF:free_avg={file}:free:AVERAGE',
        'DEF:free_min={file}:free:MIN',
        'DEF:free_max={file}:free:MAX',
        'DEF:cach_avg={file}:cached:AVERAGE',
        'DEF:cach_min={file}:cached:MIN',
        'DEF:cach_max={file}:cached:MAX',
        'DEF:resv_avg={file}:resv:AVERAGE',
        'DEF:resv_min={file}:resv:MIN',
        'DEF:resv_max={file}:resv:MAX',
        'CDEF:cach_avg_notnull=cach_avg,UN,0,cach_avg,IF',
        'CDEF:resv_avg_notnull=resv_avg,UN,0,resv_avg,IF',
        'CDEF:used_acc=used_avg',
        'CDEF:resv_acc=used_acc,resv_avg_notnull,+',
        'CDEF:cach_acc=resv_acc,cach_avg_notnull,+',
        'CDEF:free_acc=cach_acc,free_avg,+',
        "AREA:free_acc#$HalfGreen",
        "AREA:cach_acc#$HalfBlue",
        "AREA:resv_acc#$HalfYellow",
        "AREA:used_acc#$HalfRed",
        "LINE1:free_acc#$FullGreen:Free    ",
        'GPRINT:free_min:MIN:%5.1lf%s ',
        'GPRINT:free_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:free_max:MAX:%5.1lf%s',
        'GPRINT:free_avg:LAST:%5.1lf%s Last\n',
        "LINE1:cach_acc#$FullBlue:Cached  ",
        'GPRINT:cach_min:MIN:%5.1lf%s ',
        'GPRINT:cach_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:cach_max:MAX:%5.1lf%s',
        'GPRINT:cach_avg:LAST:%5.1lf%s\l',
        "LINE1:resv_acc#$FullYellow:Reserved",
        'GPRINT:resv_min:MIN:%5.1lf%s ',
        'GPRINT:resv_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:resv_max:MAX:%5.1lf%s',
        'GPRINT:resv_avg:LAST:%5.1lf%s Last\n',
        "LINE1:used_acc#$FullRed:Used    ",
        'GPRINT:used_min:MIN:%5.1lf%s ',
        'GPRINT:used_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:used_max:MAX:%5.1lf%s',
        'GPRINT:used_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['tcp_connections'] = [
        //'-v', 'Connections',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Connections",
        'GPRINT:min:MIN:%4.1lf',
        'GPRINT:avg:AVERAGE:%4.1lf',
        'GPRINT:max:MAX:%4.1lf',
        'GPRINT:avg:LAST:%4.1lf\l', ];
    $GraphDefs['temperature'] = [
        //'-v', 'Celsius',
        'DEF:temp_avg={file}:value:AVERAGE',
        'DEF:temp_min={file}:value:MIN',
        'DEF:temp_max={file}:value:MAX',
        'CDEF:average=temp_avg,0.2,*,PREV,UN,temp_avg,PREV,IF,0.8,*,+',
        "AREA:temp_max#$HalfRed",
        "AREA:temp_min#$Canvas",
        "LINE1:temp_avg#$FullRed:Temperature",
        'GPRINT:temp_min:MIN:%4.1lf',
        'GPRINT:temp_avg:AVERAGE:%4.1lf',
        'GPRINT:temp_max:MAX:%4.1lf',
        'GPRINT:temp_avg:LAST:%4.1lf\l', ];
    $GraphDefs['signal'] = [
        'DEF:signal_avg={file}:value:AVERAGE',
        'DEF:signal_min={file}:value:MIN',
        'DEF:signal_max={file}:value:MAX',
        'CDEF:average=signal_avg,0.2,*,PREV,UN,signal_avg,PREV,IF,0.8,*,+',
        "AREA:signal_max#$HalfRed",
        "AREA:signal_min#$Canvas",
        "LINE1:signal_avg#$FullRed:Signal",
        'GPRINT:signal_min:MIN:%4.1lf',
        'GPRINT:signal_avg:AVERAGE:%4.1lf',
        'GPRINT:signal_max:MAX:%4.1lf',
        'GPRINT:signal_avg:LAST:%4.1lf\l', ];
    $GraphDefs['timeleft'] = [
        //'-v', 'Minutes',
        'DEF:avg={file}:timeleft:AVERAGE',
        'DEF:min={file}:timeleft:MIN',
        'DEF:max={file}:timeleft:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Time left [min]",
        'GPRINT:min:MIN:%5.1lf%s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['time_offset'] = [ // NTPd
        'DEF:s_avg={file}:seconds:AVERAGE',
        'DEF:s_min={file}:seconds:MIN',
        'DEF:s_max={file}:seconds:MAX',
        'COMMENT:Offset     Min       Avg      Max      Cur\l',
        "AREA:s_max#$HalfBlue",
        "AREA:s_min#$Canvas",
        "LINE1:s_avg#$FullBlue:     ",
        'GPRINT:s_min:MIN:%5.2lf%s',
        'GPRINT:s_avg:AVERAGE:%5.2lf%s',
        'GPRINT:s_max:MAX:%5.2lf%s',
        'GPRINT:s_avg:LAST:%5.2lf%s', ];
    $GraphDefs['if_octets'] = [
        //'-v', 'Bits/s',
        '--units=si',
        'DEF:out_min_raw={file}:tx:MIN',
        'DEF:out_avg_raw={file}:tx:AVERAGE',
        'DEF:out_max_raw={file}:tx:MAX',
        'DEF:inc_min_raw={file}:rx:MIN',
        'DEF:inc_avg_raw={file}:rx:AVERAGE',
        'DEF:inc_max_raw={file}:rx:MAX',
        'CDEF:out_min=out_min_raw,8,*',
        'CDEF:out_avg=out_avg_raw,8,*',
        'CDEF:out_max=out_max_raw,8,*',
        'CDEF:inc_min=inc_min_raw,8,*',
        'CDEF:inc_avg=inc_avg_raw,8,*',
        'CDEF:inc_max=inc_max_raw,8,*',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'CDEF:mytime=out_avg_raw,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:out_avg_sample=out_avg_raw,UN,0,out_avg_raw,IF,sample_len,*',
        'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
        'CDEF:inc_avg_sample=inc_avg_raw,UN,0,inc_avg_raw,IF,sample_len,*',
        'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Outgoing",
        'GPRINT:out_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:out_max:MAX:%5.1lf%s',
        'GPRINT:out_avg:LAST:%5.1lf%s Last',
        'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
        "LINE1:inc_avg#$FullBlue:Incoming",
        //          'GPRINT:inc_min:MIN:%5.1lf %s ',
        'GPRINT:inc_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:inc_max:MAX:%5.1lf%s',
        'GPRINT:inc_avg:LAST:%5.1lf%s Last',
        'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l', ];
    $GraphDefs['cpufreq'] = [
        'DEF:cpufreq_avg={file}:value:AVERAGE',
        'DEF:cpufreq_min={file}:value:MIN',
        'DEF:cpufreq_max={file}:value:MAX',
        "AREA:cpufreq_max#$HalfBlue",
        "AREA:cpufreq_min#$Canvas",
        "LINE1:cpufreq_avg#$FullBlue:Frequency",
        'GPRINT:cpufreq_min:MIN:%5.1lf%s ',
        'GPRINT:cpufreq_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:cpufreq_max:MAX:%5.1lf%s',
        'GPRINT:cpufreq_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['multimeter'] = [
        'DEF:multimeter_avg={file}:value:AVERAGE',
        'DEF:multimeter_min={file}:value:MIN',
        'DEF:multimeter_max={file}:value:MAX',
        "AREA:multimeter_max#$HalfBlue",
        "AREA:multimeter_min#$Canvas",
        "LINE1:multimeter_avg#$FullBlue:Multimeter",
        'GPRINT:multimeter_min:MIN:%4.1lf',
        'GPRINT:multimeter_avg:AVERAGE:%4.1lf Average,',
        'GPRINT:multimeter_max:MAX:%4.1lf',
        'GPRINT:multimeter_avg:LAST:%4.1lf\l', ];
    $GraphDefs['users'] = [
        //'-v', 'Users',
        'DEF:users_avg={file}:users:AVERAGE',
        'DEF:users_min={file}:users:MIN',
        'DEF:users_max={file}:users:MAX',
        'COMMENT:                Min    Ave    Max    Cur\l',
        "AREA:users_max#$HalfBlue",
        "AREA:users_min#$Canvas",
        "LINE1:users_avg#$FullBlue:Users    ",
        'GPRINT:users_min:MIN:%5.1lf',
        'GPRINT:users_avg:AVERAGE:%5.1lf',
        'GPRINT:users_max:MAX:%5.1lf',
        'GPRINT:users_avg:LAST:%5.1lf\l', ];
    $GraphDefs['voltage'] = [
        //'-v', 'Voltage',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Voltage",
        'GPRINT:min:MIN:%5.1lf%sV ',
        'GPRINT:avg:AVERAGE:%5.1lf%sV ',
        'GPRINT:max:MAX:%5.1lf%sV',
        'GPRINT:avg:LAST:%5.1lf%sV\l', ];
    $GraphDefs['vmpage_action'] = [
        //'-v', 'Actions',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:max#$HalfBlue",
        "AREA:min#$Canvas",
        "LINE1:avg#$FullBlue:Action",
        'GPRINT:min:MIN:%5.1lf%sV ',
        'GPRINT:avg:AVERAGE:%5.1lf%sV ',
        'GPRINT:max:MAX:%5.1lf%sV',
        'GPRINT:avg:LAST:%5.1lf%sV\l', ];
    $GraphDefs['vmpage_faults'] = $GraphDefs['ps_pagefaults'];
    $GraphDefs['vmpage_io'] = [
        //'-v', 'Bytes/s',
        'DEF:out_min={file}:out:MIN',
        'DEF:out_avg={file}:out:AVERAGE',
        'DEF:out_max={file}:out:MAX',
        'DEF:inc_min={file}:in:MIN',
        'DEF:inc_avg={file}:in:AVERAGE',
        'DEF:inc_max={file}:in:MAX',
        'CDEF:overlap=out_avg,inc_avg,GT,inc_avg,out_avg,IF',
        'CDEF:mytime=out_avg,TIME,TIME,IF',
        'CDEF:sample_len_raw=mytime,PREV(mytime),-',
        'CDEF:sample_len=sample_len_raw,UN,0,sample_len_raw,IF',
        'CDEF:out_avg_sample=out_avg,UN,0,out_avg,IF,sample_len,*',
        'CDEF:out_avg_sum=PREV,UN,0,PREV,IF,out_avg_sample,+',
        'CDEF:inc_avg_sample=inc_avg,UN,0,inc_avg,IF,sample_len,*',
        'CDEF:inc_avg_sum=PREV,UN,0,PREV,IF,inc_avg_sample,+',
        "AREA:out_avg#$HalfGreen",
        "AREA:inc_avg#$HalfBlue",
        "AREA:overlap#$HalfBlueGreen",
        "LINE1:out_avg#$FullGreen:Written",
        'GPRINT:out_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:out_max:MAX:%5.1lf%s',
        'GPRINT:out_avg:LAST:%5.1lf%s Last',
        'GPRINT:out_avg_sum:LAST:(ca. %5.1lf%sB Total)\l',
        "LINE1:inc_avg#$FullBlue:Read   ",
        'GPRINT:inc_avg:AVERAGE:%5.1lf%s ',
        'GPRINT:inc_max:MAX:%5.1lf%s',
        'GPRINT:inc_avg:LAST:%5.1lf%s Last',
        'GPRINT:inc_avg_sum:LAST:(ca. %5.1lf%sB Total)\l', ];
    $GraphDefs['vmpage_number'] = [
        //'-v', 'Count',
        'DEF:avg={file}:value:AVERAGE',
        'DEF:min={file}:value:MIN',
        'DEF:max={file}:value:MAX',
        "AREA:avg#$HalfBlue",
        "LINE1:avg#$FullBlue:Count",
        'GPRINT:min:MIN:%5.1lf%s ',
        'GPRINT:avg:AVERAGE:%5.1lf%s ',
        'GPRINT:max:MAX:%5.1lf%s',
        'GPRINT:avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['vs_threads'] = [
        'DEF:total_avg={file}:total:AVERAGE',
        'DEF:total_min={file}:total:MIN',
        'DEF:total_max={file}:total:MAX',
        'DEF:running_avg={file}:running:AVERAGE',
        'DEF:running_min={file}:running:MIN',
        'DEF:running_max={file}:running:MAX',
        'DEF:uninterruptible_avg={file}:uninterruptible:AVERAGE',
        'DEF:uninterruptible_min={file}:uninterruptible:MIN',
        'DEF:uninterruptible_max={file}:uninterruptible:MAX',
        'DEF:onhold_avg={file}:onhold:AVERAGE',
        'DEF:onhold_min={file}:onhold:MIN',
        'DEF:onhold_max={file}:onhold:MAX',
        "LINE1:total_avg#$FullYellow:Total   ",
        'GPRINT:total_min:MIN:%5.1lf',
        'GPRINT:total_avg:AVERAGE:%5.1lf Avg.,',
        'GPRINT:total_max:MAX:%5.1lf',
        'GPRINT:total_avg:LAST:%5.1lf\l',
        "LINE1:running_avg#$FullRed:Running ",
        'GPRINT:running_min:MIN:%5.1lf',
        'GPRINT:running_avg:AVERAGE:%5.1lf Avg.,',
        'GPRINT:running_max:MAX:%5.1lf',
        'GPRINT:running_avg:LAST:%5.1lf\l',
        "LINE1:uninterruptible_avg#$FullGreen:Unintr  ",
        'GPRINT:uninterruptible_min:MIN:%5.1lf',
        'GPRINT:uninterruptible_avg:AVERAGE:%5.1lf Avg.,',
        'GPRINT:uninterruptible_max:MAX:%5.1lf',
        'GPRINT:uninterruptible_avg:LAST:%5.1lf\l',
        "LINE1:onhold_avg#$FullBlue:Onhold  ",
        'GPRINT:onhold_min:MIN:%5.1lf',
        'GPRINT:onhold_avg:AVERAGE:%5.1lf Avg.,',
        'GPRINT:onhold_max:MAX:%5.1lf',
        'GPRINT:onhold_avg:LAST:%5.1lf\l', ];
    $GraphDefs['vs_memory'] = [
        'DEF:vm_avg={file}:vm:AVERAGE',
        'DEF:vm_min={file}:vm:MIN',
        'DEF:vm_max={file}:vm:MAX',
        'DEF:vml_avg={file}:vml:AVERAGE',
        'DEF:vml_min={file}:vml:MIN',
        'DEF:vml_max={file}:vml:MAX',
        'DEF:rss_avg={file}:rss:AVERAGE',
        'DEF:rss_min={file}:rss:MIN',
        'DEF:rss_max={file}:rss:MAX',
        'DEF:anon_avg={file}:anon:AVERAGE',
        'DEF:anon_min={file}:anon:MIN',
        'DEF:anon_max={file}:anon:MAX',
        "LINE1:vm_avg#$FullYellow:VM     ",
        'GPRINT:vm_min:MIN:%5.1lf%s ',
        'GPRINT:vm_avg:AVERAGE:%5.1lf%s Avg.,',
        'GPRINT:vm_max:MAX:%5.1lf%s Avg.,',
        'GPRINT:vm_avg:LAST:%5.1lf%s\l',
        "LINE1:vml_avg#$FullRed:Locked ",
        'GPRINT:vml_min:MIN:%5.1lf%s ',
        'GPRINT:vml_avg:AVERAGE:%5.1lf%s Avg.,',
        'GPRINT:vml_max:MAX:%5.1lf%s Avg.,',
        'GPRINT:vml_avg:LAST:%5.1lf%s\l',
        "LINE1:rss_avg#$FullGreen:RSS    ",
        'GPRINT:rss_min:MIN:%5.1lf%s ',
        'GPRINT:rss_avg:AVERAGE:%5.1lf%s Avg.,',
        'GPRINT:rss_max:MAX:%5.1lf%s Avg.,',
        'GPRINT:rss_avg:LAST:%5.1lf%s\l',
        "LINE1:anon_avg#$FullBlue:Anon.  ",
        'GPRINT:anon_min:MIN:%5.1lf%s ',
        'GPRINT:anon_avg:AVERAGE:%5.1lf%s Avg.,',
        'GPRINT:anon_max:MAX:%5.1lf%s Avg.,',
        'GPRINT:anon_avg:LAST:%5.1lf%s\l', ];
    $GraphDefs['vs_processes'] = [
        //'-v', 'Processes',
        'DEF:proc_avg={file}:value:AVERAGE',
        'DEF:proc_min={file}:value:MIN',
        'DEF:proc_max={file}:value:MAX',
        "AREA:proc_max#$HalfBlue",
        "AREA:proc_min#$Canvas",
        "LINE1:proc_avg#$FullBlue:Processes",
        'GPRINT:proc_min:MIN:%4.1lf',
        'GPRINT:proc_avg:AVERAGE:%4.1lf Avg.,',
        'GPRINT:proc_max:MAX:%4.1lf',
        'GPRINT:proc_avg:LAST:%4.1lf\l', ];
    $GraphDefs['if_multicast'] = $GraphDefs['ipt_packets'];
    $GraphDefs['if_tx_errors'] = $GraphDefs['if_rx_errors'];

    $MetaGraphDefs['files_count'] = 'meta_graph_files_count';
    $MetaGraphDefs['files_size'] = 'meta_graph_files_size';
    $MetaGraphDefs['cpu'] = 'meta_graph_cpu';
    $MetaGraphDefs['if_rx_errors'] = 'meta_graph_if_rx_errors';
    $MetaGraphDefs['if_tx_errors'] = 'meta_graph_if_rx_errors';
    $MetaGraphDefs['memory'] = 'meta_graph_memory';
    $MetaGraphDefs['vs_memory'] = 'meta_graph_vs_memory';
    $MetaGraphDefs['vs_threads'] = 'meta_graph_vs_threads';
    $MetaGraphDefs['nfs_procedure'] = 'meta_graph_nfs_procedure';
    $MetaGraphDefs['ps_state'] = 'meta_graph_ps_state';
    $MetaGraphDefs['swap'] = 'meta_graph_swap';
    $MetaGraphDefs['apache_scoreboard'] = 'meta_graph_apache_scoreboard';
    $MetaGraphDefs['mysql_commands'] = 'meta_graph_mysql_commands';
    $MetaGraphDefs['mysql_handler'] = 'meta_graph_mysql_commands';
    $MetaGraphDefs['tcp_connections'] = 'meta_graph_tcp_connections';

    if (function_exists('load_graph_definitions_local')) {
        load_graph_definitions_local($logarithmic, $tinylegend);
    }

    if ($logarithmic) {
        foreach ($GraphDefs as &$GraphDef) {
            array_unshift($GraphDef, '-o');
        }
    }
    if ($tinylegend) {
        foreach ($GraphDefs as &$GraphDef) {
            for ($i = count($GraphDef) - 1; $i >= 0; $i--) {
                if (strncmp('GPRINT:', $GraphDef[$i], 7) == 0) {
                    unset($GraphDef[$i]);
                }
            }
        }
    }
}

function meta_graph_files_count($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['rrd_opts'] = ['-v', 'Mails'];

    $opts['colors'] = [
        'incoming' => '00e000',
        'active'   => 'a0e000',
        'deferred' => 'a00050',
    ];

    $type_instances = ['incoming', 'active', 'deferred'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_files_size($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['rrd_opts'] = ['-v', 'Bytes'];

    $opts['colors'] = [
        'incoming' => '00e000',
        'active'   => 'a0e000',
        'deferred' => 'a00050',
    ];

    $type_instances = ['incoming', 'active', 'deferred'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_cpu($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['rrd_opts'] = ['-r', '-u', '100', 'COMMENT:Percent         Cur     Min      Ave     Max\l'];

    $opts['colors'] = [
        'idle'      => 'ffffff',
        'nice'      => '00e000',
        'user'      => '0000ff',
        'wait'      => 'ffb000',
        'system'    => 'ff0000',
        'softirq'   => 'ff00ff',
        'interrupt' => 'a000a0',
        'steal'     => '000000',
    ];

    $type_instances = ['idle', 'wait', 'nice', 'user', 'system', 'softirq', 'interrupt', 'steal'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_memory($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = ['-b', '1024', 'COMMENT:Bytes          Cur     Min      Ave     Max\l'];

    // BYTES
    $opts['colors'] = [
        'free'     => '00e000',
        'cached'   => '0000ff',
        'buffered' => 'ffb000',
        'used'     => 'ff0000',
    ];

    $type_instances = ['free', 'cached', 'buffered', 'used'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_vs_threads($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = ['-v', 'Threads'];

    $opts['colors'] = [
        'total'   => 'F0A000',
        'running'  => 'FF0000',
        'onhold'  => '00E000',
        'uninterruptable' => '0000FF',
    ];

    $type_instances = ['total', 'running', 'onhold', 'uninterruptable'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_vs_memory($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = ['-b', '1024', '-v', 'Bytes'];

    $opts['colors'] = [
        'vm'   => 'F0A000',
        'vml'  => 'FF0000',
        'rss'  => '00E000',
        'anon' => '0000FF',
    ];

    $type_instances = ['anon', 'rss', 'vml', 'vm'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_line($opts, $sources);
}

function meta_graph_if_rx_errors($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.2lf';
    $opts['rrd_opts'] = ['-v', 'Errors/s'];

    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_mysql_commands($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['rrd_opts'] = ['COMMENT:Issues/s               Cur    Ave     Min    Max\l'];
    $opts['number_format'] = '%5.2lf';

    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_nfs_procedure($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = ['-v', 'Ops/s'];

    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_ps_state($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['rrd_opts'] = ['COMMENT:Processes      Cur     Avg      Min     Max\l'];

    $opts['colors'] = [
        'running'  => '00e000',
        'sleeping' => '0000ff',
        'paging'   => 'ffb000',
        'zombies'  => 'ff0000',
        'blocked'  => 'ff00ff',
        'stopped'  => 'a000a0',
    ];

    $type_instances = ['paging', 'blocked', 'zombies', 'stopped', 'running', 'sleeping'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_swap($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    $opts['rrd_opts'] = ['-b', '1024', 'COMMENT:Bytes        Cur     Avg     Min     Max\l'];

    $opts['colors'] = [
        'free'     => '00e000',
        'cached'   => '0000ff',
        'used'     => 'ff0000',
    ];

    $type_instances = ['free', 'cached', 'used'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_apache_scoreboard($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%6.2lf%s';
    $opts['rrd_opts'] = ['COMMENT:Processes         Cur     Min      Ave     Max\l'];

    $opts['colors'] = [
        'open'         => '00e000',
        'waiting'      => '0000ff',
        'starting'     => 'a00000',
        'reading'      => 'ff0000',
        'sending'      => '00ff00',
        'keepalive'    => 'f000f0',
        'dnslookup'    => '00a000',
        'logging'      => '008080',
        'closing'      => 'a000a0',
        'finishing'    => '000080',
        'idle_cleanup' => '000000',
    ];

    $type_instances = [/* 'open',*/ 'waiting', 'starting', 'reading', 'sending', 'keepalive', 'dnslookup', 'logging', 'closing', 'finishing', 'idle_cleanup'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file, 'ds'=>'value'];
    }

    return collectd_draw_meta_stack($opts, $sources);
}

function meta_graph_tcp_connections($host, $plugin, $plugin_instance, $type, $type_instances, $opts = [])
{
    $sources = [];

    $title = "$host/$plugin" . (! is_null($plugin_instance) ? "-$plugin_instance" : '') . "/$type";
    if (! isset($opts['title'])) {
        $opts['title'] = $title;
    }
    $opts['number_format'] = '%5.1lf%s';
    //$opts['rrd_opts']      = array('-v', 'Connections');
    $opts['rrd_opts'] = ['COMMENT:Connections      Cur     Avg      Min     Max\l'];

    $opts['colors'] = [
        'ESTABLISHED' => '00e000',
        'SYN_SENT'    => '00e0ff',
        'SYN_RECV'    => '00e0a0',
        'FIN_WAIT1'   => 'f000f0',
        'FIN_WAIT2'   => 'f000a0',
        'TIME_WAIT'   => 'ffb000',
        'CLOSE'       => '0000f0',
        'CLOSE_WAIT'  => '0000a0',
        'LAST_ACK'    => '000080',
        'LISTEN'      => 'ff0000',
        'CLOSING'     => '000000',
    ];

    $type_instances = ['ESTABLISHED', 'SYN_SENT', 'SYN_RECV', 'FIN_WAIT1', 'FIN_WAIT2', 'TIME_WAIT', 'CLOSE', 'CLOSE_WAIT', 'LAST_ACK', 'CLOSING', 'LISTEN'];
    foreach ($type_instances as $k => $inst) {
        $file = '';
        foreach (Config::get('datadirs') as $datadir) {
            if (is_file($datadir . '/' . $title . '-' . $inst . '.rrd')) {
                $file = $datadir . '/' . $title . '-' . $inst . '.rrd';
                break;
            }
        }
        if ($file == '') {
            continue;
        }

        $sources[] = ['name'=>$inst, 'file'=>$file, 'ds'=>'value'];
    }

    return collectd_draw_meta_stack($opts, $sources);
}
