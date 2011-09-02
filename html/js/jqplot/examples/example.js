$(document).ready(function(){
    $('script.code').each(function(index) {
        if ($('pre.code').eq(index).length  ) {
            $('pre.code').eq(index).text($(this).html());
        }
        else {
            var str = $(this).html();
            $('div.jqplot-target').eq(index).after($('<pre class="code">'+str+'</pre>'));
        }
    });
    $(document).unload(function() {$('*').unbind(); });
});