<?php
require "../../functions.php";
?>
<div class="navbar">
  <div class="navbar-inner">
    <div class="left"><a href="#" class="back link"> <i class="icon icon-back"></i><span>Back</span></a></div>
    <div class="center sliding">Kids Points Audit Log</div>
    <div class="right"><a href="#" data-panel="left" class="link open-panel icon-only"><i class="icon icon-bars"></i></a></div>
  </div>
</div>
<div class="pages">
  <div data-page="services" class="page">
    <div class="page-content">
      <div class="content-block-title"> / .. / .. / modules / tokens / tokens.log</div>
      <div class="content-block">
      <?php
        $records = file('../../modules/tokens/tokens.log');

        echo "<pre style='font-size: 12px; height: 700px; overflow: scroll; padding: 5px'>";

        foreach($records as $record) {

            $record = explode(',', $record);

            $change              = $record['0'];
            if ($change > 0)       $change = '+'.$change;
            $kid                 = ucwords($record['1']);
            $timestamp           = date('D M j g:i a', trim($record['2']));
            @$runningTotal[$kid]+= $change;
            $color               = ($change > 0) ? '#addfad' : '#ffd1dc';

            $htmlRows[] = "<td style='background-color: $color; text-align: left; border: solid 1px; padding: 10px; min-width: 30px'>$timestamp</td>
                           <td style='background-color: $color; text-align: center; border: solid 1px; padding: 10px; min-width: 30px'>$change</td>
                           <td style='background-color: $color; text-align: center; border: solid 1px; padding: 10px; min-width: 30px'>$kid</td>
                           <td style='background-color: $color; text-align: center; border: solid 1px; padding: 10px; min-width: 30px'><img style='margin: -10px; margin-top: -5px' src='images/$kid.jpg' /></td>
                           <td style='background-color: $color; text-align: center; border: solid 1px; padding: 10px; min-width: 30px'>{$runningTotal[$kid]}</td>";

        }
        $htmlRows = array_reverse($htmlRows);

        echo '<table style="border-collapse: collapse; font-size: 17px; width: 700px">
                <tr>
                    <th>Date</th>
                    <th>Change</th>
                    <th>Name</th>
                    <th>&nbsp;</th>
                    <th>Running Total</th>
                </tr>';
        foreach ($htmlRows as $row) {

            echo "<tr style=''>"; echo $row; echo '</tr>';

        }
        echo '</table>';
        echo "</pre>";
      ?>
      </div>
    </div>
  </div>
</div>
