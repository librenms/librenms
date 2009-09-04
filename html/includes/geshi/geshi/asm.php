<?php
/*************************************************************************************
 * asm.php
 * -------
 * Author: Tux (tux@inmail.cz)
 * Copyright: (c) 2004 Tux (http://tux.a4.cz/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.8.3
 * Date Started: 2004/07/27
 *
 * x86 Assembler language file for GeSHi.
 * Words are from SciTe configuration file (based on NASM syntax)
 *
 * CHANGES
 * -------
 * 2008/05/23 (1.0.7.22)
 *   -  Added description of extra language features (SF#1970248)
 * 2004/11/27 (1.0.2)
 *   -  Added support for multiple object splitters
 * 2004/10/27 (1.0.1)
 *   -  Added support for URLs
 *   -  Added binary and hexadecimal regexps
 * 2004/08/05 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
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
    'LANG_NAME' => 'ASM',
    'COMMENT_SINGLE' => array(1 => ';'),
    'COMMENT_MULTI' => array(),
    //Line address prefix suppression
    'COMMENT_REGEXP' => array(2 => "/^(?:[0-9a-f]{0,4}:)?[0-9a-f]{4}(?:[0-9a-f]{4})?/mi"),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        /*CPU*/
        1 => array(
            'aaa','aad','aam','aas','adc','add','and','call','cbw','clc','cld','cli','cmc','cmp',
            'cmps','cmpsb','cmpsw','cwd','daa','das','dec','div','esc','hlt','idiv','imul','in','inc',
            'int','into','iret','ja','jae','jb','jbe','jc','jcxz','je','jg','jge','jl','jle','jmp',
            'jna','jnae','jnb','jnbe','jnc','jne','jng','jnge','jnl','jnle','jno','jnp','jns','jnz',
            'jo','jp','jpe','jpo','js','jz','lahf','lds','lea','les','lods','lodsb','lodsw','loop',
            'loope','loopew','loopne','loopnew','loopnz','loopnzw','loopw','loopz','loopzw','mov',
            'movs','movsb','movsw','mul','neg','nop','not','or','out','pop','popf','push','pushf',
            'rcl','rcr','ret','retf','retn','rol','ror','sahf','sal','sar','sbb','scas','scasb','scasw',
            'shl','shr','stc','std','sti','stos','stosb','stosw','sub','test','wait','xchg','xlat',
            'xlatb','xor','bound','enter','ins','insb','insw','leave','outs','outsb','outsw','popa','pusha','pushw',
            'arpl','lar','lsl','sgdt','sidt','sldt','smsw','str','verr','verw','clts','lgdt','lidt','lldt','lmsw','ltr',
            'bsf','bsr','bt','btc','btr','bts','cdq','cmpsd','cwde','insd','iretd','iretdf','iretf',
            'jecxz','lfs','lgs','lodsd','loopd','looped','loopned','loopnzd','loopzd','lss','movsd',
            'movsx','movzx','outsd','popad','popfd','pushad','pushd','pushfd','scasd','seta','setae',
            'setb','setbe','setc','sete','setg','setge','setl','setle','setna','setnae','setnb','setnbe',
            'setnc','setne','setng','setnge','setnl','setnle','setno','setnp','setns','setnz','seto','setp',
            'setpe','setpo','sets','setz','shld','shrd','stosd','bswap','cmpxchg','invd','invlpg','wbinvd','xadd','lock',
            'rep','repe','repne','repnz','repz'
            ),
        /*FPU*/
        2 => array(
            'f2xm1','fabs','fadd','faddp','fbld','fbstp','fchs','fclex','fcom','fcomp','fcompp','fdecstp',
            'fdisi','fdiv','fdivp','fdivr','fdivrp','feni','ffree','fiadd','ficom','ficomp','fidiv',
            'fidivr','fild','fimul','fincstp','finit','fist','fistp','fisub','fisubr','fld','fld1',
            'fldcw','fldenv','fldenvw','fldl2e','fldl2t','fldlg2','fldln2','fldpi','fldz','fmul',
            'fmulp','fnclex','fndisi','fneni','fninit','fnop','fnsave','fnsavew','fnstcw','fnstenv',
            'fnstenvw','fnstsw','fpatan','fprem','fptan','frndint','frstor','frstorw','fsave',
            'fsavew','fscale','fsqrt','fst','fstcw','fstenv','fstenvw','fstp','fstsw','fsub','fsubp',
            'fsubr','fsubrp','ftst','fwait','fxam','fxch','fxtract','fyl2x','fyl2xp1',
            'fsetpm','fcos','fldenvd','fnsaved','fnstenvd','fprem1','frstord','fsaved','fsin','fsincos',
            'fstenvd','fucom','fucomp','fucompp'
            ),
        /*registers*/
        3 => array(
            'ah','al','ax','bh','bl','bp','bx','ch','cl','cr0','cr2','cr3','cs','cx','dh','di','dl',
            'dr0','dr1','dr2','dr3','dr6','dr7','ds','dx','eax','ebp','ebx','ecx','edi','edx',
            'es','esi','esp','fs','gs','si','sp','ss','st','tr3','tr4','tr5','tr6','tr7'
            ),
        /*Directive*/
        4 => array(
            '186','286','286c','286p','287','386','386c','386p','387','486','486p',
            '8086','8087','alpha','break','code','const','continue','cref','data','data?',
            'dosseg','else','elseif','endif','endw','err','err1','err2','errb',
            'errdef','errdif','errdifi','erre','erridn','erridni','errnb','errndef',
            'errnz','exit','fardata','fardata?','if','lall','lfcond','list','listall',
            'listif','listmacro','listmacroall',' model','no87','nocref','nolist',
            'nolistif','nolistmacro','radix','repeat','sall','seq','sfcond','stack',
            'startup','tfcond','type','until','untilcxz','while','xall','xcref',
            'xlist','alias','align','assume','catstr','comm','comment','db','dd','df','dq',
            'dt','dup','dw','echo','elseif1','elseif2','elseifb','elseifdef','elseifdif',
            'elseifdifi','elseife','elseifidn','elseifidni','elseifnb','elseifndef','end',
            'endm','endp','ends','eq',' equ','even','exitm','extern','externdef','extrn','for',
            'forc','ge','goto','group','high','highword','if1','if2','ifb','ifdef','ifdif',
            'ifdifi','ife',' ifidn','ifidni','ifnb','ifndef','include','includelib','instr','invoke',
            'irp','irpc','label','le','length','lengthof','local','low','lowword','lroffset',
            'macro','mask','mod','msfloat','name','ne','offset','opattr','option','org','%out',
            'page','popcontext','private','proc','proto','ptr','public','purge','pushcontext','record',
            'rept','seg','segment','short','size','sizeof','sizestr','struc','struct',
            'substr','subtitle','subttl','textequ','this','title','typedef','union','width',
            '.model', '.stack', '.code', '.data'
            ),
        /*Operands*/
        5 => array(
            '@b','@f','addr','basic','byte','c','carry?','dword',
            'far','far16','fortran','fword','near','near16','overflow?','parity?','pascal','qword',
            'real4',' real8','real10','sbyte','sdword','sign?','stdcall','sword','syscall','tbyte',
            'vararg','word','zero?','flat','near32','far32',
            'abs','all','assumes','at','casemap','common','compact',
            'cpu','dotname','emulator','epilogue','error','export','expr16','expr32','farstack',
            'forceframe','huge','language','large','listing','ljmp','loadds','m510','medium','memory',
            'nearstack','nodotname','noemulator','nokeyword','noljmp','nom510','none','nonunique',
            'nooldmacros','nooldstructs','noreadonly','noscoped','nosignextend','nothing',
            'notpublic','oldmacros','oldstructs','os_dos','para','prologue',
            'readonly','req','scoped','setif2','smallstack','tiny','use16','use32','uses'
            )
        ),
    'SYMBOLS' => array(
        '[', ']', '(', ')',
        '+', '-', '*', '/', '%',
        '.', ',', ';', ':'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #00007f; font-weight: bold;',
            2 => 'color: #0000ff; font-weight: bold;',
            3 => 'color: #00007f;',
            4 => 'color: #000000; font-weight: bold;',
            5 => 'color: #000000; font-weight: bold;'
            ),
        'COMMENTS' => array(
            1 => 'color: #666666; font-style: italic;',
            2 => 'color: #adadad; font-style: italic;',
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #009900; font-weight: bold;'
            ),
        'STRINGS' => array(
            0 => 'color: #7f007f;'
            ),
        'NUMBERS' => array(
            0 => 'color: #0000ff;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #339933;'
            ),
        'REGEXPS' => array(
//            0 => 'color: #0000ff;',
//            1 => 'color: #0000ff;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => ''
        ),
    'NUMBERS' =>
        GESHI_NUMBER_BIN_PREFIX_PERCENT |
        GESHI_NUMBER_BIN_SUFFIX |
        GESHI_NUMBER_HEX_PREFIX |
        GESHI_NUMBER_HEX_SUFFIX |
        GESHI_NUMBER_OCT_SUFFIX |
        GESHI_NUMBER_INT_BASIC |
        GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_NONSCI_F |
        GESHI_NUMBER_FLT_SCI_ZERO,
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        //Hex numbers
//        0 => /*  */ "(?<=([\\s\\(\\)\\[\\],;.:+\\-\\/*]))(?:[0-9][0-9a-fA-F]{0,31}[hH]|0x[0-9a-fA-F]{1,32})(?=([\\s\\(\\)\\[\\],;.:+\\-\\/*]))",
        //Binary numbers
//        1 => "(?<=([\\s\\(\\)\\[\\],;.:+\\-\\/*]))[01]{1,64}[bB](?=([\\s\\(\\)\\[\\],;.:+\\-\\/*]))"
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 8,
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            'DISALLOWED_BEFORE' => "(?<![a-zA-Z0-9\$_\|\#>|^])",
            'DISALLOWED_AFTER' => "(?![a-zA-Z0-9_<\|%])"
        )
    )
);

?>
