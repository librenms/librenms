<?php

echo "<div style='margin:auto; text-align: center; margin-top: 50px; max-width:600px'>";
print_optionbar_start(100, 600);
echo "
   <table height=100% width=100%><tr>
   <td><img src='images/no-48.png' valign=absmiddle></td>
   <td width=10></td>
   <td>
     <span style='color: #990000; font-weight: bold;'>
       <span style='font-size: 16px; font-weight: bold;'>Error</span>
       <br />
       <span style='font-size: 12px;'>You have insufficient permissions to view this page.</span>
     </span>
   </td>
   </tr></table>";
print_optionbar_end();
echo '</div>';
