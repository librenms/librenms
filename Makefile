GIT=git
GIT_SUBTREE=$(GIT) subtree pull --squash
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

push-testing pt:
	$(GIT) push testing

update-subtrees: datetime-subtree moment-subtree font-awesome vis

datetime-subtree:
	$(GIT_SUBTREE) --prefix=html/js/datetime https://github.com/Eonasdan/bootstrap-datetimepicker master

moment-subtree:
	$(GIT_SUBTREE) --prefix=html/js/moment   https://github.com/moment/moment master

font-awesome:
	$(GIT_SUBTREE) --prefix=lib/Font-Awesome https://github.com/FortAwesome/Font-Awesome.git master

vis:
	$(GIT_SUBTREE) --prefix=lib/vis https://github.com/almende/vis.git master

typeahead:
	$(GIT_SUBTREE) --prefix=lib/typeahead https://github.com/twitter/typeahead.js.git master

gridster:
	$(GIT_SUBTREE) --prefix=lib/gridster https://github.com/dsmorse/gridster.js.git master

jquery-mapael:
	$(GIT_SUBTREE) --prefix=lib/jQuery-Mapael https://github.com/neveldo/jQuery-Mapael.git master

mapael-maps:
	$(GIT_SUBTREE) --prefix=lib/mapael-maps https://github.com/neveldo/mapael-maps.git master

jquery-mousewheel:
	$(GIT_SUBTREE) --prefix=lib/jquery-mousewheel https://github.com/jquery/jquery-mousewheel.git master

Leaflet.markercluster:
	$(GIT_SUBTREE) --prefix=lib/Leaflet.markercluster https://github.com/Leaflet/Leaflet.markercluster.git master

Leaflet.awesome-markers:
	$(GIT_SUBTREE) --prefix=lib/Leaflet.awesome-markers https://github.com/lvoogdt/Leaflet.awesome-markers.git 2.0/develop
