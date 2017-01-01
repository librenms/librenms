<?php
/*************************************************************************************
 * parigp.php
 * --------
 * Author: Charles R Greathouse IV (charles@crg4.com)
 * Copyright: 2011-2013 Charles R Greathouse IV (http://math.crg4.com/)
 * Release Version: 1.0.8.12
 * Date Started: 2011/05/11
 *
 * PARI/GP language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2011/07/09 (1.0.8.11)
 *  -  First Release
 * 2013/02/05 (1.0.8.12)
 *  -  Added 2.6.0 commands, default, member functions, and error-handling
 *
 * TODO (updated 2011/07/09)
 * -------------------------
 *
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

$language_data = array(
    'LANG_NAME' => 'PARI/GP',
    'COMMENT_SINGLE' => array(1 => '\\\\'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '\\',
    'NUMBERS' => array(
        # Integers
        1 => GESHI_NUMBER_INT_BASIC,
        # Reals
        2 => GESHI_NUMBER_FLT_SCI_ZERO
        ),
    'KEYWORDS' => array(
        1 => array(
            'abs','acos','acosh','addhelp','addprimes','agm','alarm','algdep',
            'alias','allocatemem','apply','arg','asin','asinh','atan','atanh',
            'bernfrac','bernpol','bernreal','bernvec','besselh1','besselh2',
            'besseli','besselj','besseljh','besselk','besseln','bestappr',
            'bestapprPade','bezout','bezoutres','bigomega','binary','binomial',
            'bitand','bitneg','bitnegimply','bitor','bittest','bitxor',
            'bnfcertify','bnfcompress','bnfdecodemodule','bnfinit',
            'bnfisintnorm','bnfisnorm','bnfisprincipal','bnfissunit',
            'bnfisunit','bnfnarrow','bnfsignunit','bnfsunit','bnrclassno',
            'bnrclassnolist','bnrconductor','bnrconductorofchar','bnrdisc',
            'bnrdisclist','bnrinit','bnrisconductor','bnrisprincipal','bnrL1',
            'bnrrootnumber','bnrstark','break','breakpoint','Catalan','ceil',
            'centerlift','charpoly','chinese','cmp','Col','component','concat',
            'conj','conjvec','content','contfrac','contfracpnqn','core',
            'coredisc','cos','cosh','cotan','dbg_down','dbg_err','dbg_up',
            'dbg_x','default','denominator','deriv','derivnum','diffop',
            'digits','dilog','dirdiv','direuler','dirmul','dirzetak','divisors',
            'divrem','eint1','elladd','ellak','ellan','ellanalyticrank','ellap',
            'ellbil','ellcard','ellchangecurve','ellchangepoint',
            'ellconvertname','elldivpol','elleisnum','elleta','ellffinit',
            'ellfromj','ellgenerators','ellglobalred','ellgroup','ellheegner',
            'ellheight','ellheightmatrix','ellidentify','ellinit',
            'ellisoncurve','ellj','ellL1','elllocalred','elllog','elllseries',
            'ellminimalmodel','ellmodulareqn','ellmul','ellneg','ellorder',
            'ellordinate','ellpointtoz','ellrootno','ellsearch','ellsigma',
            'ellsub','elltaniyama','elltatepairing','elltors','ellweilpairing',
            'ellwp','ellzeta','ellztopoint','erfc','errname','error','eta','Euler',
            'eulerphi','eval','exp','extern','externstr','factor','factorback',
            'factorcantor','factorff','factorial','factorint','factormod',
            'factornf','factorpadic','ffgen','ffinit','fflog','ffnbirred',
            'fforder','ffprimroot','fibonacci','floor','for','forcomposite','fordiv','forell',
            'forprime','forqfvec','forstep','forsubgroup','forvec','frac','galoisexport',
            'galoisfixedfield','galoisgetpol','galoisidentify','galoisinit',
            'galoisisabelian','galoisisnormal','galoispermtopol',
            'galoissubcyclo','galoissubfields','galoissubgroups','gamma',
            'gammah','gcd','getenv','getheap','getrand','getstack','gettime',
            'global','hammingweight','hilbert','hyperu','I','idealadd',
            'idealaddtoone','idealappr','idealchinese','idealcoprime',
            'idealdiv','idealfactor','idealfactorback','idealfrobenius',
            'idealhnf','idealintersect','idealinv','ideallist','ideallistarch',
            'ideallog','idealmin','idealmul','idealnorm','idealnumden',
            'idealpow','idealprimedec','idealramgroups','idealred','idealstar',
            'idealtwoelt','idealval','if','iferr','iferrname','imag','incgam','incgamc','input',
            'install','intcirc','intformal','intfouriercos','intfourierexp',
            'intfouriersin','intfuncinit','intlaplaceinv','intmellininv',
            'intmellininvshort','intnum','intnuminit','intnuminitgen',
            'intnumromb','intnumstep','isfundamental','ispolygonal','ispower','ispowerful',
            'isprime','isprimepower','ispseudoprime','issquare','issquarefree','istotient',
            'kill','kronecker','lcm','length','lex','lift','lindep','List',
            'listcreate','listinsert','listkill','listpop','listput','listsort',
            'lngamma','local','log','Mat','matadjoint','matalgtobasis',
            'matbasistoalg','matcompanion','matconcat','matcontent','matdet','matdetint',
            'matdiagonal','mateigen','matfrobenius','mathess','mathilbert',
            'mathnf','mathnfmod','mathnfmodid','matid','matimage',
            'matimagecompl','matindexrank','matintersect','matinverseimage',
            'matisdiagonal','matker','matkerint','matmuldiagonal',
            'matmultodiagonal','matpascal','matrank','matrix','matrixqz',
            'matsize','matsnf','matsolve','matsolvemod','matsupplement',
            'mattranspose','max','min','minpoly','Mod','modreverse','moebius',
            'my','newtonpoly','next','nextprime','nfalgtobasis','nfbasis',
            'nfbasistoalg','nfdetint','nfdisc','nfeltadd','nfeltdiv',
            'nfeltdiveuc','nfeltdivmodpr','nfeltdivrem','nfeltmod','nfeltmul',
            'nfeltmulmodpr','nfeltnorm','nfeltpow','nfeltpowmodpr',
            'nfeltreduce','nfeltreducemodpr','nfelttrace','nfeltval','nffactor',
            'nffactorback','nffactormod','nfgaloisapply','nfgaloisconj',
            'nfhilbert','nfhnf','nfhnfmod','nfinit','nfisideal','nfisincl',
            'nfisisom','nfkermodpr','nfmodprinit','nfnewprec','nfroots',
            'nfrootsof1','nfsnf','nfsolvemodpr','nfsubfields','norm','norml2',
            'numbpart','numdiv','numerator','numtoperm','O','omega','padicappr',
            'padicfields','padicprec','partitions','permtonum','Pi','plot',
            'plotbox','plotclip','plotcolor','plotcopy','plotcursor','plotdraw',
            'ploth','plothraw','plothsizes','plotinit','plotkill','plotlines',
            'plotlinetype','plotmove','plotpoints','plotpointsize',
            'plotpointtype','plotrbox','plotrecth','plotrecthraw','plotrline',
            'plotrmove','plotrpoint','plotscale','plotstring','Pol',
            'polchebyshev','polcoeff','polcompositum','polcyclo','polcyclofactors','poldegree',
            'poldisc','poldiscreduced','polgalois','polgraeffe','polhensellift',
            'polhermite','polinterpolate','poliscyclo','poliscycloprod',
            'polisirreducible','pollead','pollegendre','polrecip','polred',
            'polredabs','polredbest','polredord','polresultant','Polrev','polroots',
            'polrootsff','polrootsmod','polrootspadic','polsturm','polsubcyclo',
            'polsylvestermatrix','polsym','poltchebi','poltschirnhaus',
            'polylog','polzagier','precision','precprime','prime','primepi',
            'primes','print','print1','printf','printsep','printtex','prod','prodeuler',
            'prodinf','psdraw','psi','psploth','psplothraw','Qfb','qfbclassno',
            'qfbcompraw','qfbhclassno','qfbnucomp','qfbnupow','qfbpowraw',
            'qfbprimeform','qfbred','qfbsolve','qfgaussred','qfjacobi','qflll',
            'qflllgram','qfminim','qfperfection','qfrep','qfsign',
            'quadclassunit','quaddisc','quadgen','quadhilbert','quadpoly',
            'quadray','quadregulator','quadunit','quit','random','randomprime','read',
            'readvec','real','removeprimes','return','rnfalgtobasis','rnfbasis',
            'rnfbasistoalg','rnfcharpoly','rnfconductor','rnfdedekind','rnfdet',
            'rnfdisc','rnfeltabstorel','rnfeltdown','rnfeltreltoabs','rnfeltup',
            'rnfequation','rnfhnfbasis','rnfidealabstorel','rnfidealdown',
            'rnfidealhnf','rnfidealmul','rnfidealnormabs','rnfidealnormrel',
            'rnfidealreltoabs','rnfidealtwoelt','rnfidealup','rnfinit',
            'rnfisabelian','rnfisfree','rnfisnorm','rnfisnorminit','rnfkummer',
            'rnflllgram','rnfnormgroup','rnfpolred','rnfpolredabs',
            'rnfpseudobasis','rnfsteinitz','round','select','Ser','serconvol',
            'serlaplace','serreverse','Set','setbinop','setintersect',
            'setisset','setminus','setrand','setsearch','setunion','shift',
            'shiftmul','sigma','sign','simplify','sin','sinh','sizebyte',
            'sizedigit','solve','sqr','sqrt','sqrtint','sqrtn','sqrtnint','stirling','Str',
            'Strchr','Strexpand','Strprintf','Strtex','subgrouplist','subst',
            'substpol','substvec','sum','sumalt','sumdedekind','sumdiv','sumdivmult','sumdigits',
            'sumformal','suminf','sumnum','sumnumalt','sumnuminit','sumpos','system','tan',
            'tanh','taylor','teichmuller','theta','thetanullk','thue',
            'thueinit','trace','trap','truncate','type','until','valuation',
            'variable','Vec','vecextract','vecmax','vecmin','Vecrev',
            'vecsearch','Vecsmall','vecsort','vector','vectorsmall','vectorv',
            'version','warning','weber','whatnow','while','write','write1',
            'writebin','writetex','zeta','zetak','zetakinit','zncoppersmith',
            'znlog','znorder','znprimroot','znstar'
            ),

        2 => array(
            'void','bool','negbool','small','int',/*'real',*/'mp','var','lg','pol',
            'vecsmall','vec','list','str','genstr','gen','typ'
            ),

        3 => array(
            'TeXstyle','breakloop','colors','compatible','datadir','debug',
            'debugfiles','debugmem','echo','factor_add_primes','factor_proven',
            'format','graphcolormap','graphcolors','help','histfile','histsize',
            'lines','linewrap',/*'log',*/'logfile','new_galois_format','output',
            'parisize','path','prettyprinter','primelimit','prompt_cont',
            'prompt','psfile','readline','realprecision','recover','secure',
            'seriesprecision',/*'simplify',*/'sopath','strictmatch','timer'
            ),

        4 => array(
            '"e_ARCH"','"e_BUG"','"e_FILE"','"e_IMPL"','"e_PACKAGE"','"e_DIM"',
            '"e_FLAG"','"e_NOTFUNC"','"e_OP"','"e_TYPE"','"e_TYPE2"',
            '"e_PRIORITY"','"e_VAR"','"e_DOMAIN"','"e_MAXPRIME"','"e_MEM"',
            '"e_OVERFLOW"','"e_PREC"','"e_STACK"','"e_ALARM"','"e_USER"',
            '"e_CONSTPOL"','"e_COPRIME"','"e_INV"','"e_IRREDPOL"','"e_MISC"',
            '"e_MODULUS"','"e_NEGVAL"','"e_PRIME"','"e_ROOTS0"','"e_SQRTN"'
            )
        ),
    'SYMBOLS' => array(
        1 => array(
            '(',')','{','}','[',']','+','-','*','/','%','=','<','>','!','^','&','|','?',';',':',',','\\','\''
            )
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000ff;',
            2 => 'color: #e07022;',
            3 => 'color: #00d2d2;',
            4 => 'color: #00d2d2;'
            ),
        'COMMENTS' => array(
            1 => 'color: #008000;',
            'MULTI' => 'color: #008000;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #111111; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #002222;'
            ),
        'STRINGS' => array(
            0 => 'color: #800080;'
            ),
        'NUMBERS' => array(
            0 => 'color: #666666;',
            1 => 'color: #666666;',
            2 => 'color: #666666;'
            ),
        'METHODS' => array(
            0 => 'color: #004000;'
            ),
        'SYMBOLS' => array(
            1 => 'color: #339933;'
            ),
        'REGEXPS' => array(
            0 => 'color: #e07022',    # Should be the same as keyword group 2
            1 => 'color: #555555',
            2 => 'color: #0000ff'     # Should be the same as keyword group 1
            ),
        'SCRIPT' => array()
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
        ),
    'REGEXPS' => array(
        0 => array( # types marked on variables
            GESHI_SEARCH => '(?<!\\\\ )"(t_(?:INT|REAL|INTMOD|FRAC|FFELT|COMPLEX|PADIC|QUAD|POLMOD|POL|SER|RFRAC|QFR|QFI|VEC|COL|MAT|LIST|STR|VECSMALL|CLOSURE|ERROR))"',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '"',
            GESHI_AFTER => '"'
            ),
        1 => array( # literal variables
            GESHI_SEARCH => '(?<!\\\\)(\'[a-zA-Z][a-zA-Z0-9_]*)',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            ),
        2 => array( # member functions
            GESHI_SEARCH => '(?<=[.])(a[1-6]|b[2-8]|c[4-6]|area|bid|bnf|clgp|cyc|diff|disc|[efjp]|fu|gen|index|mod|nf|no|omega|pol|reg|roots|sign|r[12]|t2|tate|tu|zk|zkst)\b',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            )
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        2 => array(
            '[a-zA-Z][a-zA-Z0-9_]*:' => ''
            ),
        3 => array(
            'default(' => ''
            ),
        4 => array(
            'iferrname(' => ''
            ),
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
