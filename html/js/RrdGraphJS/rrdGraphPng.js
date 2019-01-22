/* *********************************************************************
   rrdGraphPng - make rrdcharts interactive

   Copyright:
     2015 OETIKER+PARTNER AG http://www.oetiker.ch

   License:
     Gnu GPL Version 2

   Version: #VERSION#, #DATE#

   Authors:
     * Tobias Oetiker (oetiker)

* **********************************************************************/

/**
 * The rrdGraphPng control turns a static RRD chart into an interactive one
 * all that is required for this to work, is the ability to configure the
 * start and end and possible with and height parameters in the chart URL.
 *
 * See index.html in this directory for inspiration on how to use this library.
 *
 *
 */


qxWeb.define('rrdGraphPng',{
    extend: qxWeb.$$qx.ui.website.Widget,
    statics: {
        _config : {
            canvasPadding: 100,
            initialStart : (new Date()).getTime() / 1000 - 24*3600,
            initialRange: 24*3600,
            moveZoom: 1,
            autoUpdate: true,
            gridFillStyleA: 'rgba(0,0,0,0.08)',
            gridFillStyleB: 'rgba(255,255,255,0.08)'
        },
        rrdGraphPng: function(cfg){
            var png = new rrdGraphPng(this);
            png.init(cfg);
            return png;
        }
    },

    construct : function(selector, context) {
        this.base(arguments, selector, context);
    },

    members : {
        __start: null,
        __range: null,
        __syncJob: null,
        __paintGrid: null,
        __ctxCanvas: null,
        init: function(cfg){
            if (!this.base(arguments)) {
                return false;
            };
            var that = this;
            try {
                throw new Error('');
            } catch (e){
                // this is voodoo, but it does often detect where the javascript file
                // lives and thus we can hope to find the cursor files there too
                if (e.stack){
                    that.setConfig('cursorUrl',e.stack.replace(/[^$]*http/,'http').replace(/[^\/]*\.js[^$]*/,''));
                }
            };

            // update the grid no more then 30 times a second
            this.__paintGrid = qxWeb.func.throttle(this.__paintGridReal,32,{trailing: false});

            if (cfg){
                for (var key in cfg){
                    this.setConfig(key,cfg[key])
                }
            }
            var qxWindow = qxWeb(window);
            this.__start = parseInt(this.getConfig('initialStart'));
            this.__range = parseInt(this.getConfig('initialRange'));

            qxWindow.on('resize',this.update,this);
            this.setStyle('display','inline-block');
            this.setAttributes({
                unselectable: true,
                draggable: false
            });
            this.__addLoader();
            this.__addCanvas();
            this.__addTrack();
            this.__addRoll();
            this.emit('update');
            if (this.getConfig('autoUpdate')){
                this.__addSyncCharts();
            }
            return true;
        },


        setStart: function(start){
            this.__start = start;
            this.update();
        },
        getStart: function(){
            return this.__start;
        },
        getRange: function(){
            return this.__range;
        },
        setRange: function(range){
            this.__range = range;
            this.update();
        },

        setStartRange: function(start,range){
            this.__start = start;
            this.__range = range;
            this.update();
        },

        update: function(){
            this.emit('update');
        },

        __addSyncCharts: function(){
            var lastEnd = this.__start + this.__range;
            var lastNow = false;
            var that = this;
            var syncCharts = function(){
                var currentEnd = that.__start + that.__range;
                var now = Math.round((new Date()).getTime()/1000);
                if (now < currentEnd && now > that.__start){
                    if (!lastNow) {
                        lastNow = now;
                        lastEnd = currentEnd;
                        return;
                    }
                    var increment = now - lastNow;
                    var go = false;
                    if (that.__range / that.getWidth() < increment){
                        go = true;
                    }
                    if (go){
                        lastNow = now;
                        that.__start += increment;
                        lastEnd = that.__start + that.__range;
                        that.update();
                        that.emit('change',that.__start,that.__range);
                    }
                }
                else {
                    lastNow = false;
                }
            };
            if (this.getConfig('autoUpdate')){
                this.__syncJob = window.setInterval(syncCharts,1000);
            }
        },

        __buildUrl: function(zoom){
            var template = this.getData('src-template');
            var start = this.__start;
            if (start == null || isNaN(start)) return '';
            return qxWeb.template.render(template,{
                width: this.getWidth(),
                height: this.getHeight(),
                start: start,
                end: start + this.__range,
                zoom: zoom ? zoom : 1,
                random: Math.round(Math.random()*1000000000).toString(36)
            });
        },

        __addCanvas: function(){
            var offset = this.getOffset();
            var pos = this.getPosition();
            // console.log(img,offset,pos);
            var canvas = qxWeb.create('<canvas></canvas>');
            canvas.setStyles({
                position: 'absolute'
            })
            .setAttributes({
                draggable: "false",
                unselectable: "true"
            })
            .insertBefore(this);

            canvas.setStyle('cursor','url(' + this.getConfig('cursorUrl') + '/MoveCursor.cur), move');

            var resize = function(){
                var width = this.getWidth();
                var height = this.getHeight();
                canvas.setStyles({width: width+'px', height: height+'px'});
                canvas.setProperties({width: width.toString(), height: height.toString()});
                //canvas.width = width;
                //canvas.height = height;
            };
            qxWeb(window).on('resize',resize,this);
            this.__canvas = canvas;
            if (canvas[0].getContext){
                this.__ctxCanvas = canvas[0].getContext("2d");
            }
            resize.call(this);
        },

        __rangeCap: function(range){
            return Math.round(Math.min(Math.max(10,range),24*3600*366*20));
        },

        __addLoader: function(){
            var loading = false;
            var skipped = false;
            var lastSrc = null;
            var start;
            var retry = 0;
            var onError = function(){
                loading = false;
                //if (retry < 3){
                //    retry++;
                //    img.emit('update');
                //}
            };
            this.on('error',onError,this);
            var onLoad = function(){
                loading = false;
                if (skipped){
                    skipped = false;
                    this.emit('update');
                }
                retry = 0;
            };
            this.on('load',onLoad,this);
            var onUpdate = function(zoom){
                var url = this.__buildUrl(zoom);
                if (!url) return;
                if (! loading){
                    loading = true;
                    this.setProperty('src',url);
                }
                else {
                    skipped = true;
                }
            };

            var onUpdateThrottled = qxWeb.func.throttle(onUpdate,120,this);
            this.on('update',onUpdateThrottled,this);

            this.once('qxRrdDispose',function(){
                this.off('load',onLoad,this);
                this.off('update',onUpdateThrottled,this);
                this.off('error',onError,this);
            },this);

        },

        __addRoll: function(){
            var that = this;
            var syncUp = qxWeb.func.debounce(function(){
                that.update();
                that.emit('changeStartRange',{start:that.__start,range:that._range});
            },200);
            var xPos = this.getWidth()/2;
            var onMove = function(e){
                var newXPos = e.pageX - this.getOffset().left;
                if (! isNaN(newXPos)){
                    xPos = newXPos;
                }
            };

            var initialDotRange;
            var initialDotStart;
            var dotOff = true;
            var killerId;
            var onRoll = function(e){
                if (e.pointerType != "wheel" || !e._original.ctrlKey ) return;
                e.preventDefault();
                e.stopPropagation();
                var delta = e.delta.y;
                var initialRange = this.__range;
                var xOrigin = xPos / this.getWidth();
                if (dotOff){
                    initialDotRange = this.__range;
                    initialDotStart = this.__start;
                    dotOff = false;
                }
                this.__paintGrid(this,initialDotRange,initialDotStart);
                this.__range = this.__rangeCap(this.__range*(1+(delta/10000)));
                this.__start = Math.round(this.__start + (initialRange - this.__range)*xOrigin);
                var that = this;
                killerId = window.setTimeout(function(){
                    window.clearTimeout(killerId);
                    that.__clearGrid();
                    dotOff=true
                },1000);
                this.emit('update',this.getConfig('moveZoom'));
                syncUp();
            };
            this.__canvas.on('pointermove',onMove,this);
            this.__canvas.on('roll',onRoll,this);

            //img.once('qxRrdDispose',function(){
            //    img.__canvas.off('pointermove',onMove,this);
            //    img.__canvas.off('roll',onRoll,this);
            //},this);
        },
        __paintGridReal: function(initialRange,initialStart){
            var ctx = this.__ctxCanvas;
            if (!ctx){
                return;
            }
            var width = this.getWidth();
            var height = this.getHeight();
            var skip = 100;
            var xIncr = Math.round(initialRange / this.__range * skip);
            var xOff = Math.round((width / this.__range * (initialStart - this.__start)) % xIncr);
            var xWidth = Math.round(xIncr/2);
            var gridStyleA = this.getConfig('gridFillStyleA');
            var gridStyleB = this.getConfig('gridFillStyleB');
            ctx.clearRect(0,0,width,height);
            for (var x=-xIncr+xOff;x<width;x+=xIncr){
                 ctx.fillStyle = gridStyleA,
                 ctx.fillRect(x,0,xWidth,height);
                 ctx.fillStyle = gridStyleB,
                 ctx.fillRect(x+xWidth,0,xWidth,height);
            };
        },
        __clearGrid: function(){
            var ctx = this.__ctxCanvas;
            if (!ctx){
                return;
            }
            var width = this.getWidth();
            var height = this.getHeight();
            ctx.clearRect(0,0,width,height);
        },
        __addTrack: function(){
            var qxDocument = q(document);
            var initialStart = this.__start;
            var initialRange = this.__range;
            var xOrigin;
            var pointerOrigin;
            var imgWidth = this.getWidth() - this.getConfig('canvasPadding');
            var active = false;
            var trackLock = false;
            var vertical;

            var onPointerMove = function(e){
                if (!active) return;
                if (e.pointerType == 'touch' && e._original.touches.length > 1) return;
                var delta = {
                    x: e.pageX - pointerOrigin.x,
                    y: e.pageY - pointerOrigin.y
                };
                if (!trackLock){
                    if (Math.abs(delta.x) > 10 || Math.abs(delta.y) > 10){
                        vertical = Math.abs(delta.x) < Math.abs(delta.y)
                        trackLock = true;
                    }
                    else {
                        return;
                    }
                }
                if (vertical){
                    if (e.pointerType == 'touch') return;
                    if (! isNaN(xOrigin)){
                        this.__range = this.__rangeCap(initialRange*Math.pow(1.02,delta.y));
                        this.__start = Math.round(initialStart + (initialRange - this.__range)*xOrigin);
                    }
                }
                else {
                    this.__start = initialStart-Math.round(this.__range/imgWidth*delta.x);
                }
                this.__paintGrid(initialRange,initialStart);
                e.preventDefault();
                e.stopPropagation();
                this.emit('update',this.getConfig('moveZoom'));
            };

            var onPinch = function(e){
                if (!active) return;
                var scale = e.getScale();
                if (!scale) return;
                e.preventDefault();
                e.stopPropagation();

                this.__range = this.__rangeCap(initialRange/scale);
                this.__start = Math.round(initialStart + (initialRange - this.__range)/2);
                this.__paintGrid(initialRange,initialStart);
                this.emit('update',this.getConfig('moveZoom'));
            };

            var canvas = this.__canvas;

            var qxDoc = qxWeb(window);
            var onPointerUp = function(e){
                if (!active) return;
                //e.stopPropagation();
                //e.preventDefault();
                active = false;
                this.__clearGrid();
                this.update();
                if (initialRange != this.__range || initialStart != this.__start){
                    this.emit('changeStartRange',{start:this.__start,range:this.__range});
                }
                trackLock = false;
                canvas.setStyle('cursor','url(' + this.getConfig('cursorUrl') + '/MoveCursor.cur), move');
                qxDoc.off("pointermove",onPointerMove,this);
            };

            var onPointerDown = function(e){
                //e.preventDefault();
                //e.stopPropagation();
                if (active) return;
                active = true;
                initialStart = this.__start;
                initialRange = this.__range;
                imgWidth = this.getWidth() - this.getConfig('canvasPadding');
                canvas.setStyle('cursor','url(' + this.getConfig('cursorUrl') + '/DragCursor.cur), move');
                var newXPos = e.pageX - this.getOffset().left;
                pointerOrigin = {
                    x: e.pageX, y: e.pageY
                };
                if (! isNaN(newXPos)){
                    xOrigin = newXPos / this.getWidth();
                }
                qxDoc.on("pointermove",onPointerMove,this);
                // on mobile devices we do not kill 'touch' because this could
                // spell the start of a vertical scroll
                if (e.pointerType != 'touch') {
                    e.preventDefault();
                    e.stopPropagation();
                }
            };

            var onDoubleTap = function(e){
                window.open(this.getProperty('src'),'_blank','width='+(this.getWidth()+10)+',height='+(this.getHeight()+10)+'titlebar=no,status=no,menubar=no,toolbar=no',false);
            };
            
            var onPointerOut = function(e){
                if (!active) return;
                e.preventDefault();
                e.stopPropagation();
            };

            qxDoc.on("pointerup",onPointerUp,this,true);
            qxDoc.on('pointerout',onPointerOut,this,true);

            canvas.on('pinch',onPinch,this);
            canvas.on('pointerdown',onPointerDown,this);
            canvas.on('dbltap',onDoubleTap,this);
            
            this.once('qxRrdDispose',function(){
                canvas.allOff();
                qxDoc.off("pointerup",onPointerUp,this,true);
                qxDoc.off('pointerout',onPointerOut,this,true);
                qxDoc.off('pointermove',onPointerMove,this);
                canvas.remove();
            });
        },
        dispose: function(){
            if (this.__syncJob){
                window.clearInterval(this.__syncJob);
            }
            this.emit('qxRrdDispose');
            this.removeAttribute('unselectable');
            this.removeAttribute('draggable');
            return this.base(arguments);            
        }
    },

    defer : function(statics) {
        qxWeb.$attach({rrdGraphPng : statics.rrdGraphPng});
    }
});


