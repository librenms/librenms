<script type="text/javascript" src="js/tw-sack.js"></script>
<script type="text/javascript">

var ajax = new Array();

function getInterfaceList(sel)
{
        var deviceId = sel.options[sel.selectedIndex].value;
        // Empty city select box
        var el = document.getElementById('port_id');
        if (el !== null) {
            el.options.length = 0;
        }
        if (deviceId.length>0) {
                var index = ajax.length;
                ajax[index] = new sack();

                ajax[index].requestFile = 'ajax_listports.php?device_id='+deviceId;    // Specifying which file to get
                ajax[index].onCompletion = function() { createInterfaces(index) };       // Specify function that will be executed after file has been found
                ajax[index].runAJAX();          // Execute AJAX function
        }
}

function createInterfaces(index) {
    const obj = document.getElementById('port_id');

    // Assuming ajax[index].response contains JavaScript-like code as a string
    const lines = ajax[index].response.split(';'); // Split into individual lines of code

    lines.forEach(line => {
        if (line.trim()) { // Skip empty lines
            const match = line.match(/new Option\(['"](.*?)['"],['"](.*?)['"]\)/);
            if (match) {
                const label = match[1];
                const value = match[2];
                obj.options[obj.options.length] = new Option(label, value);
            }
        }
    });
}
</script>
