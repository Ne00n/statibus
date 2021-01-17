<?php

preg_match_all('/^[0-9]{1,3}$/m', $_GET["service"], $matches, PREG_SET_ORDER, 0);
if (empty($matches)) { echo "Invalid service."; die(); }

$serviceID = $_GET["service"];

$statibus = new statibus(_rqliteIP,_rqlitePort);
$data = $statibus->sql()->select('SELECT * FROM services JOIN uptime ON uptime.serviceID=services.id WHERE services.id='.$serviceID.' ',True);

if ($data == False || isset($data['error'])) {  echo "Invalid service."; die(); }
if (!isset($data['rows'][0])) { echo "Invalid service."; die(); }
$data = $data['rows'][0];

?>

<body>
  <div class="container">
    <div class="item">
      <h2 class="mb-0"><?php echo tools::escape($data['name']); ?></h2>
      <a href="index.php"><p class="mt-0"><- Return</p></a>
    </div>
    <div id="rstatus" class="item text-right">
      <h2 class="mb-0">Service Status</h2>
      <p class="mt-0">Last update: <?php echo date('d M H:i', $data['lastrun']); ?></p>
    </div>
    <div class="item box">
      <?php
      if ($data['status'] == 0) {
        echo '<h2 class="ml-1"><span class="dot dot-orange"></span> '.tools::escape($data['name']).' is <blah class="orange">down</blah></h2>';
      } else {
        echo '<h2 class="ml-1"><span class="dot dot-green"></span> '.tools::escape($data['name']).' is <blah class="green">operational</blah></h2>';
      }
      ?>
    </div>
    <div class="item ">
      <h2 class="mb-0">Events <small>Last 90 Days</small></h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
      <div class="services">

          <?php

          $outages = $statibus->sql()->select('SELECT * FROM outages WHERE serviceID='.$serviceID.' ORDER BY timestamp DESC ',True);

          if (!isset($outages['rows'][0])) {
            echo '<h2 class="text-center">No records.</h2>';
          } else {
            $closed = False;
            for ($i = 0; $i <= count($outages['rows']) -1; $i++) {
              $row = $outages['rows'][$i];
              if ($row['status'] == 0 && !$closed) {
                 echo '<div class="container"><div class="block red"><p>Downtime</p></div><div class="block">';
                 echo '<p class="text-center">since '.date('d M H:i', $outages['rows'][$i]['timestamp']).'</p></div><div class="block text-center">ongoing</div></div>';
               } elseif ($row['status'] == 0) {
                 $diff = round( ($outages['rows'][$i -1]['timestamp'] - $outages['rows'][$i]['timestamp']) / 60);
                 echo '<p class="text-center">'.date('d M H:i', $outages['rows'][$i]['timestamp']).' until '.date('d M H:i', $outages['rows'][$i -1]['timestamp']).'</p></div><div class="block text-center">'.tools::escape($diff).'m</div></div>';
                 $closed = False;
               } elseif ($row['status'] == 1) {
                 echo '<div class="container"><div class="block '.($outages['rows'][$i +1]['flag'] != NULL ? 'orange"><p>Network died' : 'red"><p>Downtime');
                 echo '</p></div><div class="block">';
                 $closed = True;
              }
            }
          }

          ?>

      </div>
    </div>
    <div class="item ">
      <h2 class="mb-0">Overall Uptime</h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
      <div class="container">

        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($data['oneDay']), 4, '.', '');  ?>%</span>
          <p class="mt-0">Last 24 hours</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($data['sevenDays']), 4, '.', '');  ?>%</span>
          <p class="mt-0">Last 7 days</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($data['thirtyDays']), 4, '.', ''); ?>%</span>
          <p class="mt-0">Last 30 days</p>
        </div>
      </div>
    </div>

  </div>
