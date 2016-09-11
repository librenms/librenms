<?php

$css = <<<EOF

<style>

    .right {
	text-align	: right;
    }

    .left {
	text-align	: left;
    }

    .center {
	text-align	: center;
    }

    h1 {
	font-size	: 2.0em;
	border-bottom	: 3px solid #000;
	letter-spacing	: 5px;
    }

    h2 {
	font-size	: 1.6em;
	letter-spacing	: 3px;
	color		: #336699;
	border-bottom	: 2px solid #6699ff;
    }

    table {
	font-size	: 1.0em;
    }

    table th {
	font-weight	: bold;
    }

    table th.title {
	width		: 125px;
    }

    table td.divider {
	width		: 15px;
    }

    table td.content {
	width		: 345px;
	text-align	: left;
    }

    table td.qtag {
	width		: 150px;
    }

    table.transferOverview th {
	text-align	: center;
	color		: #fff;
	letter-spacing	: 1px;
    }

    table.transferOverview th.period {
	width		: 230px;
    }

    table.transferOverview th.inbound,
    table.transferOverview th.outbound,
    table.transferOverview th.total {
	width		: 135px;
    }

</style>

EOF;
