<?php

echo "<div style='margin:auto; text-align: center; margin-top: 50px; max-width:600px'>";
print_optionbar_start(100, 600);
echo "
   <table height=100% width=100%><tr>
   <td style='color: darkred'><i class='fa fa-3x fa-ban'></i></td>
   <td width=10></td>
   <td>
     <span style='color: darkred; font-weight: bold;'>
       <span style='font-size: 16px; font-weight: bold;'>Error</span>
       <br />
       <span style='font-size: 12px;'>You have insufficient permissions to view this page.</span>
     </span>
   </td>
   </tr></table>";
print_optionbar_end();
echo '</div>';
