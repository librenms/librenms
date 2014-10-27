GIT=git
SYNC=rsync -rtvx --cvs-exclude --exclude '.*.swp'

default:
	@echo 'Make what?'

personal p:	test push-personal

test t:	pull merge push-testing gc

check:
	find . -type f -name '*.php' | xargs -n1 php -l 2>&1 | awk '/^No syntax errors detected in / {next} {print; ret=1} END {exit ret}'

gc:
	$(GIT) gc

merge:
	$(GIT) merge master

pull:
	$(GIT) pull

push-dev:
	$(GIT) push

push-personal pp:
	$(GIT) push personal

push-testing:
	$(GIT) push testing

update-subtrees:
	$(GIT) subtree pull --squash --prefix=html/js/datetime https://github.com/Eonasdan/bootstrap-datetimepicker master
	$(GIT) subtree pull --squash --prefix=html/js/moment   https://github.com/moment/moment master
