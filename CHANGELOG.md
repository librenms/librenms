# Change Log

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

<a name=""></a>
#  (2016-08-27)


### Bug Fixes

* add resize.min_size option to default config object ([5672edb](https://github.com/laf/librenms/commit/5672edb))
* Apply fix from [#22](https://github.com/librenms/librenms/issues/22) to vertical positioning ([dfc6491](https://github.com/laf/librenms/commit/dfc6491))
* convert from JQury to DOM ([4936ef8](https://github.com/laf/librenms/commit/4936ef8))
* fixes and improvements in widget-resizing. Closes [#32](https://github.com/librenms/librenms/issues/32) ([ae02b32](https://github.com/laf/librenms/commit/ae02b32))
* forgot about the nicecase ([a6e72db](https://github.com/laf/librenms/commit/a6e72db))
* recalculate grid width when adding widgets ([4774597](https://github.com/laf/librenms/commit/4774597))
* **draggable:** namespace events with unique ids ([79aff38](https://github.com/laf/librenms/commit/79aff38))
* sensor id for rrd file ([b201e64](https://github.com/laf/librenms/commit/b201e64))
* setting user port permissions fails ([b52b493](https://github.com/laf/librenms/commit/b52b493))
* strstr won't return anything if nfsensuffix is empty ([7817021](https://github.com/laf/librenms/commit/7817021))
* **gridster:** sort widgets appropriately when reading them from DOM ([5c6d25c](https://github.com/laf/librenms/commit/5c6d25c))
* use entPhysicalDescr if entPhysicalName is empty ([ad4562d](https://github.com/laf/librenms/commit/ad4562d))
* **autogrow:** refining autogrow_cols behavior and grid width issues ([835c2df](https://github.com/laf/librenms/commit/835c2df))
* **draggable:** bind drag events to $document ([dd6c742](https://github.com/laf/librenms/commit/dd6c742))
* **draggable:** fix offset during drag ([c726c4a](https://github.com/laf/librenms/commit/c726c4a))
* **draggable:** handle both touch and click events ([021a6c2](https://github.com/laf/librenms/commit/021a6c2)), closes [#207](https://github.com/laf/librenms/issues/207) [#236](https://github.com/laf/librenms/issues/236) [#329](https://github.com/laf/librenms/issues/329) [#380](https://github.com/laf/librenms/issues/380)
* **draggable:** replaced scrollX/Y with scrollLeft/Top ([bb7463a](https://github.com/laf/librenms/commit/bb7463a))
* **generated-styles:** cleaning cached serializations properly ([f8b04f2](https://github.com/laf/librenms/commit/f8b04f2))
* **gridster:** `resize_widget` also accepts HTMLElements ([cda560f](https://github.com/laf/librenms/commit/cda560f))
* **gridster:** Add additiona error checking on widgets removal ([2b0f996](https://github.com/laf/librenms/commit/2b0f996))
* **gridster:** adding bower.json file ([7654437](https://github.com/laf/librenms/commit/7654437))
* **gridster:** adding bower.json file ([fa29663](https://github.com/laf/librenms/commit/fa29663))
* **gridster:** changed "instanceof jQuery" to "instanceof $" ([c622630](https://github.com/laf/librenms/commit/c622630))
* **gridster:** changed the way widgets were getting positioned so that margins are actually the same size that the user specified all the way around the grid ([a391304](https://github.com/laf/librenms/commit/a391304))
* **gridster:** custom `ignore_dragging` overwrites the default value ([6bcfa6e](https://github.com/laf/librenms/commit/6bcfa6e))
* **gridster:** destroy resize_api ([b162932](https://github.com/laf/librenms/commit/b162932)), closes [#473](https://github.com/laf/librenms/issues/473)
* **gridster:** ensure coords instances are destroyed on widgets ([576b5ae](https://github.com/laf/librenms/commit/576b5ae))
* **gridster:** ensure widget dimensions and coords are always ints ([595a94f](https://github.com/laf/librenms/commit/595a94f))
* **gridster:** fire `positionschanged` when widget orig position changes ([9926cef](https://github.com/laf/librenms/commit/9926cef))
* **gridster:** fix add_widget to use correct size_y when adding rows ([7d22e6c](https://github.com/laf/librenms/commit/7d22e6c))
* **gridster:** fixed bugs in centering_widgets (widgets were getting smushed when being resized) and fixed bug with min_width ([86053f8](https://github.com/laf/librenms/commit/86053f8))
* **gridster:** fixed expand_widget bug not expanding full width of window ([dbc226d](https://github.com/laf/librenms/commit/dbc226d))
* **gridster:** fixes bug where widgets would overlay other widgets after a resize ([61572cd](https://github.com/laf/librenms/commit/61572cd))
* **gridster:** fixing drag limit issues when using autogrow_cols ([afd83fe](https://github.com/laf/librenms/commit/afd83fe))
* **gridster:** fixing resize limits when in fixed width mode ([6bb47dc](https://github.com/laf/librenms/commit/6bb47dc))
* **gridster:** forcing height of gridster container to auto when in collapsed mode ([749f37a](https://github.com/laf/librenms/commit/749f37a))
* **gridster:** leaking options with multiple Gridster instances ([7ed79e5](https://github.com/laf/librenms/commit/7ed79e5))
* **gridster:** Orphan preview holder when dragging is interrupted ([1b13617](https://github.com/laf/librenms/commit/1b13617))
* **gridster:** overlapping widget problem ([31fd8d6](https://github.com/laf/librenms/commit/31fd8d6))
* **gridster:** preventing gridster from adding extra resize handles ([9d077da](https://github.com/laf/librenms/commit/9d077da))
* **gridster:** remove_widget Returns the instance of the Gridster Class ([5bfbc5c](https://github.com/laf/librenms/commit/5bfbc5c))
* **gridster:** Removing previously added style tags before adding new one. ([93c46ff](https://github.com/laf/librenms/commit/93c46ff)), closes [#211](https://github.com/laf/librenms/issues/211) [#294](https://github.com/laf/librenms/issues/294)
* **gridster:** resize.axes default option only 'both' ([e9dc513](https://github.com/laf/librenms/commit/e9dc513))
* **gridster:** responsive width now resizes based off wrapper not window ([e69c3e8](https://github.com/laf/librenms/commit/e69c3e8))
* **gridster:** serialize returns an Array object, not a jQuery object ([93df6cf](https://github.com/laf/librenms/commit/93df6cf)), closes [#394](https://github.com/laf/librenms/issues/394)
* wrong variable used ([87eb2a6](https://github.com/laf/librenms/commit/87eb2a6))
* **gridster:** the preview holder should not always use `li` ([1ade74e](https://github.com/laf/librenms/commit/1ade74e))
* **gridster:** wrong addition solved in add_faux_rows/cols by adding parseInt ([d947175](https://github.com/laf/librenms/commit/d947175)), closes [#426](https://github.com/laf/librenms/issues/426) [#425](https://github.com/laf/librenms/issues/425)
* **licenses:** add required copyright message for underscore ([6f20723](https://github.com/laf/librenms/commit/6f20723))
* **readme:** link title jsfiddle -> jsbin, edit 5) of process steps ([e9d8d8d](https://github.com/laf/librenms/commit/e9d8d8d))
* **resize.stop:** Call resize.stop at the latest possible moment ([e21f63a](https://github.com/laf/librenms/commit/e21f63a))


### Chores

* **css naming:** `gs_w` to `gs-w` following CSS naming conventions ([c1668d9](https://github.com/laf/librenms/commit/c1668d9))
* **draggable:** jQuery adapter returns Draggable instance ([d0ca628](https://github.com/laf/librenms/commit/d0ca628))


### Features

* **collision:** overlapping region as a config option ([720d487](https://github.com/laf/librenms/commit/720d487))
* **coords:** add destroy method ([fdeee4f](https://github.com/laf/librenms/commit/fdeee4f))
* **coords:** allow both (left/x1) and (top/y1) attr keys ([6f22217](https://github.com/laf/librenms/commit/6f22217))
* **draggable:** Add toggle draggable method. ([073fdc4](https://github.com/laf/librenms/commit/073fdc4))
* **draggable:** allow ignore_dragging config option to be a function ([69fcfe4](https://github.com/laf/librenms/commit/69fcfe4))
* **draggable:** autoscrolling ([d3f25f3](https://github.com/laf/librenms/commit/d3f25f3))
* **draggable:** CSS selectors support in `ignore_dragging` config opt ([0f95624](https://github.com/laf/librenms/commit/0f95624))
* **draggable:** Don't start new drag if previous one hasn't stopped ([91ca657](https://github.com/laf/librenms/commit/91ca657))
* **draggable:** keep container position prop if different than static ([04868a3](https://github.com/laf/librenms/commit/04868a3))
* **draggable:** method to set drag limits dynamically ([8fa3ad2](https://github.com/laf/librenms/commit/8fa3ad2))
* **draggable:** new config option to move or not the dragged element ([4d9b2a8](https://github.com/laf/librenms/commit/4d9b2a8))
* **draggable:** option to not remove helper on drag stop ([03910df](https://github.com/laf/librenms/commit/03910df))
* **draggable:** pass previous position to the drag callback ([055cc0e](https://github.com/laf/librenms/commit/055cc0e))
* **draggable:** pass useful data to all drag callbacks ([8dda241](https://github.com/laf/librenms/commit/8dda241))
* **draggable:** support horizontal scrolling while dragging ([b0b4464](https://github.com/laf/librenms/commit/b0b4464))
* **gridster:** add config to set custom show/hide widget methods ([7de5bba](https://github.com/laf/librenms/commit/7de5bba))
* **gridster:** browserify compatibility ([43148b8](https://github.com/laf/librenms/commit/43148b8))
* **gridster:** Common.js support ([446852a](https://github.com/laf/librenms/commit/446852a)), closes [#434](https://github.com/laf/librenms/issues/434)
* **gridster:** destroy element's data and optionally remove from DOM ([dc09f19](https://github.com/laf/librenms/commit/dc09f19))
* **gridster:** drag-and-drop widget resizing ([e192405](https://github.com/laf/librenms/commit/e192405))
* **gridster:** increase grid width when dragging or resizing ([b65a802](https://github.com/laf/librenms/commit/b65a802))
* **gridster:** move widget up when added if there is space available ([8ec307b](https://github.com/laf/librenms/commit/8ec307b))
* **gridster.css:** remove possible default pading ([2002c45](https://github.com/laf/librenms/commit/2002c45))
* **resize:** add option to set min_size of a widget ([429c776](https://github.com/laf/librenms/commit/429c776))
* **resize:** Add start/stop/resize callbacks ([d4ec714](https://github.com/laf/librenms/commit/d4ec714))
* **resize:** add start/stop/resize event triggers ([7ca8dee](https://github.com/laf/librenms/commit/7ca8dee))
* **utils:** add delay helper to utils ([faa6c5d](https://github.com/laf/librenms/commit/faa6c5d))
* make gridster AMD compatible ([589d7fd](https://github.com/laf/librenms/commit/589d7fd))


### BREAKING CHANGES

* draggable: If you was accessing to Draggable instances through
.data('drag') you should remove the `data` getter. Since now the instance
is returned directly.
* css naming: If you are using `gs_w` classes somewhere, replace
them with `gs-w`
