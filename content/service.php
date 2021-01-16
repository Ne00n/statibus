<?php

preg_match_all('/^[0-9]{1,3}$/m', $_GET["service"], $matches, PREG_SET_ORDER, 0);
if (empty($matches)) { echo "Invalid service."; die(); }

$serviceID = $_GET["service"];

$statibus = new statibus(_rqliteIP,_rqlitePort);
$data = $statibus->sql()->select('SELECT * FROM services JOIN uptime ON uptime.serviceID=services.id WHERE services.id='.$serviceID.' ',True);

if ($data == False || isset($data['error'])) {  echo "Database ded."; die(); }
if (!isset($data['rows'][0])) { echo "Invalid service."; die(); }
$data = $data['rows'][0];

?>

<body>
  <div class="container">
    <div class="item">
      <h1 class="mb-0"><?php echo tools::escape($data['name']); ?></h1>
      <a href="index.php"><p class="mt-0"><- Back</p></a>
    </div>
    <div id="rstatus" class="item text-right">
      <h2 class="mb-0">Service Status</h2>
      <p class="mt-0">Last update: <?php echo date('d M h:i', $data['lastrun']); ?></p>
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
      <h2 class="mb-0">Outages <small>Last 90 Days</small></h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
      <div class="services">

          <?php

          $outages = $statibus->sql()->select('SELECT * FROM outages WHERE serviceID='.$serviceID.' ',True);

          if (!isset($outages['rows'][0])) {
            echo '<h2 class="text-center">No records.</h2>';
          } else {
            $closed = False;
            foreach ($outages['rows'] as $row) {
              if ($row['status'] == 0) { echo '<div class="uptime">'; $closed = False; }
              if ($row['status'] == 1) {
                echo '<p class="text-center">Outage from '.date('d M h:i', $outages['rows'][count($outages) -1]['timestamp']).' until '.date('d M h:i', $outages['rows'][count($outages)]['timestamp']).'</p>';
                echo '</div>'; $closed = True;
              }
            }
            if ($outages['rows'][count($outages['rows']) -1]['status'] == 0 && !$closed) {
              echo '<p class="text-center">Ongoing outage since '.date('d M h:i', $outages['rows'][count($outages) -1]['timestamp']).'</p>';
              echo '</div>';
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
          <span class="inline"><?php echo tools::escape($data['oneDay']); ?>%</span>
          <p class="mt-0">Last 24 hours</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo tools::escape($data['sevenDays']); ?>%</span>
          <p class="mt-0">Last 7 days</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo tools::escape($data['thirtyDays']); ?>%</span>
          <p class="mt-0">Last 30 days</p>
        </div>
      </div>
    </div>

  </div>

<footer>

</footer>

</body>

</html>
