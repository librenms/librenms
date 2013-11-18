GIT=git
SYNC=rsync -rtvx --cvs-exclude --exclude '.*.swp'
TEST_DEST=librenms:/opt/librenms/

default:	check
	$(GIT) merge master
	$(SYNC) html includes $(TEST_DEST)

test t:	merge check push-testing gc

dev d:	merge check push-dev gc

check:
	for i in `$(GIT) diff --name-only`; do php -l $$i; done

gc:
	$(GIT) gc

merge:
	$(GIT) merge master

pull:
	$(GIT) pull

push-dev:
	$(GIT) push

push-testing:
	$(GIT) push testing

