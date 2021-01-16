<?php

$statibus = new statibus(_rqliteIP,_rqlitePort);
$services = $statibus->sql()->select('SELECT * FROM services');
$uptime = $statibus->sql()->select('SELECT * FROM uptime');
if ($services == False || isset($services['error'])) {  echo "Database ded."; die(); }

$isDown = $statibus->isDownTimeHuh($services);
$percentages = $statibus->gimmahDowntimePercentaaages($uptime);

if (isset($services['values'])) {
  $lastrun = $services['values'][count($services['values']) -1][7];
} else {
  $lastrun = "n/a";
}

?>

<body>
  <div class="container">
    <div class="item">
      <a href="index.php"><h1><?php echo _title; ?></h1></a>
    </div>
    <div id="rstatus" class="item text-right">
      <h2 class="mb-0">Service Status</h2>
      <p class="mt-0">Last update: <?php echo date('d M h:i', $lastrun); ?></p>
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
    <div class="item ">
      <h2 class="mb-0">Uptime <small>Last 90 Days</small></h2>
    </div>
    <div class="item">

    </div>
    <div class="item box">
      <div class="services">

          <?php

          if (isset($services['values'])) {
            foreach ($services['values'] as $service) {
              echo '<div class="container">';
              $data = tools::getUptimeFromService($service[0],$uptime);
              echo '<div class="service"><a href="index.php?service='.tools::escape($service[0]).'"><p class="inline">'.$service[1].'</p></a><span class="green inline pull-right mt-1 mr-1">'.($data ? tools::escape($data[5])."%" : 'n/a').'</span></div>';
              echo '<div class="uptime mt-05"><svg width="100%" height="20" viewBox="0 0 640 20">';
              $detailed = json_decode(base64_decode($data[1]),True); $spacing = 7;
              $keys = array_keys($detailed);
              if ($detailed == False || $detailed == "[]") {
                for ($i = 0; $i <= 89; $i++) {
                  echo '<rect class="rnew" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                }
              } else {
                for ($i = 90; $i > 0; $i = $i -1) {
                  $negate = 91 - count($detailed);
                  if ($negate <= $i) {
                    $selector = $i - $negate;
                    $percentage = $detailed[$keys[$selector]];
                    if ($percentage == 100) {
                      echo '<rect class="rgreen" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                    } elseif ($percentage < 100 && $percentage > 99) {
                       echo '<rect class="rgreendark" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                    } elseif ($percentage < 99 && $percentage > 97) {
                      echo '<rect class="rorange" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                    } else {
                      echo '<rect class="rred" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                    }
                  } else {
                    echo '<rect class="rnew" height="18" width="5" x="'.$i*$spacing.'"></rect>';
                  }
                }
              }
              echo '</svg></div>';
              echo '<div class="status"><p class="'.($service[2] ? "green" : 'red').' pull-right mr-1">'.($service[2] ? "Up" : 'Down').'</p></div>';
              echo '</div>';
            }
          } else {
            echo '<h2 class="text-center">No services added.</h2>';
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
          <span class="inline"><?php echo tools::escape($percentages['1day']); ?>%</span>
          <p class="mt-0">Last 24 hours</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo tools::escape($percentages['7days']); ?>%</span>
          <p class="mt-0">Last 7 days</p>
        </div>
        <div class="block mt-1 text-center">
          <span class="inline"><?php echo tools::escape($percentages['30days']); ?>%</span>
          <p class="mt-0">Last 30 days</p>
        </div>
      </div>
    </div>

  </div>

<footer>

</footer>

</body>

</html>
