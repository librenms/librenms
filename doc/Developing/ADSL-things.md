source: Developing/ADSL-things.md

# ADSL things

## Introduction

Looking at the current (commit 61316ce2c) codebase and git log there are a few attempts at better interpreting and presenting DSL information.

For example changing the wording from `upstream` and `downstream` to `Central to CPE` and `CPE to Central`.

This is only half of the story. The `ADSL-LINE-MIB` has values for ATU-C and ATU-R which is DSL parlance for Central and Remote sides of the connection.
Some of these values are for TX (or sending) properties of the connection and some are for RX (or receiving) properties of the connection.

The description section of `ADSL-LINE-MIB` has a good explanation of how the MIB return values should be interpreted.

## challenge

The challenge is to map the values presented by `ADSL-LINE-MIB` to a representation that matches the presentation of other port values.

`ADSL-LINE-MIB` represents values for the connection in stead of for the port, whereas LibreNMS presents values for a port.

One challenge is to determine whether a monitored port is a Central or Remote port. This is currently not implemented by LibreNMS. Assumption in the code seems to be the monitored port is always a Remote port.

## conversion table

Based on MIB variable description. This MIB is quite well documented.

| MIB variable | R / C | TX / RX | at R port |
| --- | :---: | :---: | --- |
| AturChanCurrTxRate  | R | TX | R-C speed |
| AtucChanCurrTxRate  | C | TX | C-R speed |
| AturCurrAttainableR | R | TX | R-C att. speed |
| AtucCurrAttainableR | C | TX | C-R att. speed |
| AturCurrAtn         | R | RX | C-R attn |
| AtucCurrAtn         | C | RX | R-C attn |
| AturCurrOutputPwr   | R | TX | R-C pwr |
| AtucCurrOutputPwr   | C | TX | C-R pwr |
| AturCurrSnrMgn      | R | RX | C-R snr |
| AtucCurrSnrMgn      | C | RX | R-C snr |
