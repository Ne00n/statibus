<?php

preg_match_all('/^[0-9]{1,3}$/m', $_GET["service"], $matches, PREG_SET_ORDER, 0);
if (empty($matches)) { echo "Invalid service."; die(); }

$serviceID = $_GET["service"];

$statibus = new statibus(_rqliteIP,_rqlitePort);
$data = $statibus->sql()->select(['SELECT * FROM services JOIN uptime ON uptime.serviceID=services.id WHERE services.id=?',$serviceID],True);

if ($data == False || isset($data['error'])) {  echo "Invalid service."; die(); }
if (!isset($data['rows'][0])) { echo "Invalid service."; die(); }
$data = $data['rows'][0];

?>

<body>
  <div class="container">
    <div class="item">
      <h2 class="mb-0"><?php echo tools::escape($data['name']); ?></h2>
      <a href="index.php"><p class="mt-0">&lt;- Return</p></a>
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
    <div class="item ">
      <h2 class="mb-0">Events <small>Last 90 Days</small></h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
    <?php

    $outages = $statibus->getOutagesArray($serviceID);

    if (empty($outages)) {
      echo '<h2 class="text-center">No records.</h2>';
    } else {
      foreach ($outages as $outage) {
        echo '<div class="container">';
        if ($outage['header'] == 'Downtime') {
          echo '<div class="block red"><p class="text-center">Downtime</p></div>';
        } else {
          echo '<div class="block orange"><p>Origin Network issue</p></div>';
        }
        echo '<div class="block"><p class="text-center">'.$outage['message'].'</p></div>';
        if ($outage['downtime'] == 'ongoing') {
          echo '<div class="block"><p class="text-center">ongoing</p></div>';
        } else {
          echo '<div class="block"><p class="text-center">'.$outage['downtime'].'m</p></div>';
        }
        echo '</div>';
      }
    }

    ?>

    </div>

  </div>
