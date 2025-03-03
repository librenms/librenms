#!/usr/bin/env bash
_lnms_completion()
{
	#shellcheck disable=SC2207
	COMPREPLY=($(COMP_CURRENT="${2}" COMP_PREVIOUS="${3}" COMP_LINE="${COMP_LINE}" lnms list:bash-completion))
	return $?
}
complete -F _lnms_completion lnms
