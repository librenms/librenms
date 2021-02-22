#!/usr/bin/env bash

# Pastes from STDIN or file from commandline argument to Stikked pastebin
# Default server: paste.mate-desktop.org
# URL: https://github.com/glensc/pbin

# Copyright (C) 2014 Elan Ruusamäe

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

set -e
PROGRAM=${0##*/}

# can be overriden from env
: "${PASTE_URL=https://p.libren.ms/api/create}"

# paste. take input from stdin
pastebin() {
	# show params

        if [[ "${expire}" == "" ]]; then
            expire=0;
        fi

        if [[ "${private}" == "" ]]; then
            private=1;
        fi

	sed -e '/^$/d' >&2 <<-EOF
		${title+title: "$title"}
		${name+name: "$name"}
		${private+private: "$private"}
		${language+language: "$language"}
		${expire+expire: "$expire"}
		${reply+reply: "$reply"}
	EOF

	# do paste
	curl "$PASTE_URL" \
		${title+-F title="$title"} \
		${name+-F name="$name"} \
		${private+-F private="$private"} \
		${language+-F lang="$language"} \
		${expire+-F expire="$expire"} \
		${reply+-F reply="$reply"} \
		-F 'text=<-'
}

# try to resolve mime-type to language
mime_to_lang() {
	local mime="$1"

	awk -F ':' -vm="$mime" 'm==$1 {print $2}' <<-EOF
	application/javascript:javascript
	application/xml:xml
	text/html:html5
	text/x-c:c
	text/x-c++:cpp
	text/x-diff:diff
	text/x-lua:lua
	text/x-php:php
	text/x-python:python
	text/x-ruby:ruby
	text/x-shellscript:bash
	EOF
}

# detect filetype. outputs nothing if no file binary or no detected
detect_mimetype() {
	local file="$1" out

	out=$(file -L --mime-type "$file" 2>/dev/null || :)
	echo "${out#*: }"
}

usage() {
	echo "Usage: $PROGRAM [options] < [input_file]

Options:
  -t, --title     title of this paste
  -n, --name      author of this paste
  -p, --private   should this paste be private (0 = public, 1 = private)
  -l, --language  language this paste is in
  -e, --expire    paste expiration in minutes
  -r, --reply     reply to existing paste
"
}

set_defaults() {
	unset title
	unset private
	unset language
	unset expire
	unset reply

	# default to user@hostname
	name=${SUDO_USER:-$USER}
}

set_defaults

# parse command line args
t=$(getopt -o h,t:,n:,p:,l:,e:,r:,b: --long help,title:,name:,private:,language:,expire:,reply: -n "$PROGRAM" -- "$@")
eval set -- "$t"

while :; do
	case "$1" in
	-h|--help)
		usage
		exit 0
	;;
	-t|--title)
		shift
		title="$1"
	;;
	-n|--name)
		shift
		name="$1"
	;;
	-p|--private)
                shift
		private="$1"
	;;
	-l|--language)
		shift
		language="$1"
	;;
	-e|--expire)
		shift
		expire="$1"
	;;
	-r|--reply)
		shift
		reply="$1"
	;;
	-b)
		shift
		PASTE_URL="$1"
	;;
	--)
		shift
		break
	;;
	*)
		echo >&2 "$PROGRAM: Internal error: [$1] not recognized!"
		exit 1
	;;
	esac
	shift
done

echo "Paste endpoint: $PASTE_URL"

# if we have more commandline arguments, set these as title
if [ "${title+set}" != "set" ]; then
	title="$*"
fi

# set language from first filename
if [ -n "$1" ] && [ "${language+set}" != "set" ]; then
	mime=$(detect_mimetype "$1")

	if [ -n "$mime" ]; then
		language=$(mime_to_lang "$mime")
	fi
fi

# take filenames from commandline, or from stdin
if [ $# -gt 0 ]; then
	paste=$(cat "$@")
else
	paste=$(cat)
fi

echo "$paste" | pastebin
