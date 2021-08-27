document.addEventListener('DOMContentLoaded', function () {

    [].forEach.call(document.getElementsByClassName('tabbed-set'), function (tabSet) {
        [].forEach.call(tabSet.getElementsByTagName('label'), function (tab) {
            tab.addEventListener("click", changeInstallTab);
        });
    });
}, false);

function changeInstallTab(e) {
    e = e || window.event;
    var target = e.target || e.srcElement;
    var text = target.textContent || target.innerText;

    [].forEach.call(document.getElementsByClassName('tabbed-set'), function (tabSet) {
        var tabs = [];
        var children = tabSet.children;
        var found = false;
        for (i = 0; i < children.length; i++) {
            var el = children[i];
            if (el.tagName === 'LABEL') {
                var tabText = el.textContent || el.innerText;
                if (tabText === text) {
                    found = true;
                }
                tabs.push({id: el.htmlFor, checked: tabText === text});
            }
        }

        // apply changes if tab found in group
        if (found) {
            tabs.forEach(function (tab) {
                document.getElementById(tab.id).checked = tab.checked;
            });
        }
    });

    // make sure original element did not get pushed off screen
    target.scrollIntoViewIfNeeded();
}

// scrollIntoViewIfNeeded Polyfill
if (!Element.prototype.scrollIntoViewIfNeeded) {
    Element.prototype.scrollIntoViewIfNeeded = function (centerIfNeeded) {
        centerIfNeeded = arguments.length === 0 ? true : !!centerIfNeeded;

        var parent = this.parentNode,
            parentComputedStyle = window.getComputedStyle(parent, null),
            parentBorderTopWidth = parseInt(parentComputedStyle.getPropertyValue('border-top-width')),
            parentBorderLeftWidth = parseInt(parentComputedStyle.getPropertyValue('border-left-width')),
            overTop = this.offsetTop - parent.offsetTop < parent.scrollTop,
            overBottom = (this.offsetTop - parent.offsetTop + this.clientHeight - parentBorderTopWidth) > (parent.scrollTop + parent.clientHeight),
            overLeft = this.offsetLeft - parent.offsetLeft < parent.scrollLeft,
            overRight = (this.offsetLeft - parent.offsetLeft + this.clientWidth - parentBorderLeftWidth) > (parent.scrollLeft + parent.clientWidth),
            alignWithTop = overTop && !overBottom;

        if ((overTop || overBottom) && centerIfNeeded) {
            parent.scrollTop = this.offsetTop - parent.offsetTop - parent.clientHeight / 2 - parentBorderTopWidth + this.clientHeight / 2;
        }

        if ((overLeft || overRight) && centerIfNeeded) {
            parent.scrollLeft = this.offsetLeft - parent.offsetLeft - parent.clientWidth / 2 - parentBorderLeftWidth + this.clientWidth / 2;
        }

        if ((overTop || overBottom || overLeft || overRight) && !centerIfNeeded) {
            this.scrollIntoView(alignWithTop);
        }
    };
}
