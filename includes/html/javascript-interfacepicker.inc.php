<script type="text/javascript" src="js/tw-sack.js"></script>
<script type="text/javascript">

var ajax = new Array();

function getInterfaceList(sel)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        document.getElementById('port_id').options.length = 0;     // Empty city select box
        if (deviceId.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'ajax_listports.php?device_id='+deviceId;    // Specifying which file to get
                ajax[index].onCompletion = function() { createInterfaces(index) };       // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createInterfaces(index)
{
        var obj = document.getElementById('port_id');
        eval(ajax[index].response);     // Executing the response from Ajax as Javascript code
}

</script>
