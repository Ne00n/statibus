<?php

$statibus = new statibus(_rqliteIP,_rqlitePort);
$data = $statibus->sql()->select('SELECT s.ID,s.name,g.name as gname,s.status,s.lastrun,g.id as gid,u.detailed,u.oneDay,u.sevenDays,u.thirtyDays,u.ninetyDays
FROM services as s JOIN uptime as u ON u.serviceID=s.id JOIN groups as g ON g.id=s.groupID ORDER BY g.id',True);

if ($data === False || isset($data['error'])) {  echo "Database ded."; die(); }

$isDown = $statibus->isDownTimeHuh($data);
$percentages = $statibus->gimmahDowntimePercentaaages($data);

if (isset($data['rows'])) {
  $lastrun = $data['rows'][count($data['rows']) -1]['lastrun'];
} else {
  $lastrun = 0;
}

?>

<body>
  <div class="container">
    <div class="item">
      <a href="index.php"><h1><?php echo _title; ?></h1></a>
    </div>
    <div id="rstatus" class="item text-right">
      <h2 class="mb-0">Service Status</h2>
      <p class="mt-0">Last update: <?php echo date('d M H:i', $lastrun); ?></p>
    </div>
    <div class="item box">
      <?php
      if ($isDown) {
        echo '<h2 class="ml-1"><span class="dot dot-orange"></span> Some systems are <blah class="orange">down</blah></h2>';
      } else {
        echo '<h2 class="ml-1"><span class="dot dot-green"></span> All systems <blah class="green">operational</blah></h2>';
      }
      ?>
    </div>
    <?php

    if (isset($data['rows'])) {
      $lastGroup = "";
      foreach ($data['rows'] as $row) {

        if ($lastGroup != $row['gname']) {
          if ($lastGroup != "") { echo '</div></div>'; }
          echo '<div class="item "><h2 class="mb-0">'.tools::escape($row['gname']).' <small>Last 90 Days of Uptime</small></h2></div>';
          echo '<div class="item"></div>';
          echo '<div class="item box"><div class="services">';
          $lastGroup = $row['gname'];
        }

        echo '<div class="container">';
        echo '<div class="service"><a href="index.php?service='.tools::escape($row['id']).'"><p class="inline service-text">'.$row['name'].'</p></a><span class="green inline pull-right mt-1 mr-1">'.($row['ninetyDays'] ? number_format(floor($row['ninetyDays']*100)/100, 2)."%" : 'n/a').'</span></div>';
        echo '<div class="uptime mt-05"><svg width="100%" height="20" viewBox="0 0 640 20">';
        $detailed = json_decode(base64_decode($row['detailed']),True); $spacing = 7;
        $keys = array_keys($detailed);
        if ($detailed == False || $detailed == "[]") {
          for ($i = 1; $i <= 90; $i++) {
            echo '<rect class="new" height="18" width="5" x="'.$i*$spacing.'"></rect>';
          }
        } else {
          for ($i = 90; $i > 0; $i = $i -1) {
            $negate = 91 - count($detailed);
            if ($negate <= $i) {
              $selector = $i - $negate;
              $percentage = $detailed[$keys[$selector]];
              if ($percentage == 100) {
                echo '<rect class="green" height="18" width="5" x="'.$i*$spacing.'"></rect>';
              } elseif ($percentage < 100 && $percentage > 99) {
                 echo '<rect class="darkgreen" height="18" width="5" x="'.$i*$spacing.'"></rect>';
              } elseif ($percentage < 99 && $percentage > 97) {
                echo '<rect class="orange" height="18" width="5" x="'.$i*$spacing.'"></rect>';
              } else {
                echo '<rect class="red" height="18" width="5" x="'.$i*$spacing.'"></rect>';
              }
            } else {
              echo '<rect class="new" height="18" width="5" x="'.$i*$spacing.'"></rect>';
            }
          }
        }

        echo '</svg></div>';
        echo '<div class="status"><p class="'.($row['status'] ? "green" : 'red').' status-text">'.($row['status'] ? "Online" : 'Offline').'</p></div>';
        echo '</div>';

      }
      echo '</div></div>';
    } else {
      echo '<h2 class="text-center">No services added.</h2>';
    }

    ?>

    <div class="item ">
      <h2 class="mb-0">Overall Uptime</h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
      <div class="container">

        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($percentages['oneDay']), 2, '.', ''); ?>%</span>
          <p class="mt-0">Last 24 hours</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($percentages['sevenDays']), 2, '.', ''); ?>%</span>
          <p class="mt-0">Last 7 days</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo number_format((float)tools::escape($percentages['thirtyDays']), 2, '.', ''); ?>%</span>
          <p class="mt-0">Last 30 days</p>
        </div>
      </div>
    </div>

  </div>
