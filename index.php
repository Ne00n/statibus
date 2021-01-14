<?php

include 'configs/config.php';

function dat_loader($class) {
    include 'class/' . $class . '.php';
}

spl_autoload_register('dat_loader');

$statibus = new statibus(_rqliteIP,_rqlitePort);
$services = $statibus->sql()->select('SELECT * FROM services');
$uptime = $statibus->sql()->select('SELECT * FROM uptime');
if ($services == False || isset($services['error'])) {  echo "Database ded."; die(); }

?>

<html>

<head>
  <meta charset="utf-8">
  <title><?php echo _title; ?></title>
  <link rel="stylesheet" href="css/code.css">
</head>

<body>
  <div class="container">
  <div class="item">
    <h1><?php echo _title; ?></h1>
  </div>
  <div class="item text-right">
    <h2>Service Status</h2>
  </div>
  <div class="item box">
    <h2 class="ml-1">All systems <blah class="green">operational</blah></h2>
  </div>
  <div class="item ">
    <h2 class="mb-0">Uptime <small>Last 90 Days</small></h2>
  </div>
  <div class="item">

  </div>
  <div class="item box">
    <div class="services">

        <?php

        echo '<div class="container">';
        if (isset($services['values'])) {
          foreach ($services['values'] as $service) {
            $data = $statibus->getUptimeFromService($service[0],$uptime);
            echo '<div class="service"><p class="inline">'.$service[1].'</p><span class="green inline pull-right mt-1 mr-1">'.($data ? $data['thirtyDays'] : 'Updating...').'</span></div>';
            echo '<div class="uptime"><svg width="100%" height="20" viewBox="0 0 640 2';
            if ($data == False) {
              for ($i = 0; $i <= 630; $i = $i +7) {
                echo '<rect class="rnew" height="18" width="5" x="'.$i.'"></rect>';
              }
            } else {

            }
            echo '</svg></div>';
            echo '<div class="status"><p class="'.($service[2] ? "green" : 'red').' pull-right mr-1">'.($service[2] ? "Up" : 'Down').'</p></div>';
          }
        } else {

        }
        echo '</div>';

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
        <span class="inline">100.00%</span>
        <p class="mt-0">Last 24 hours</p>
      </div>
      <div class="block mt-1 text-center">
        <span class="inline">100.00%</span>
        <p class="mt-0">Last 7 days</p>
      </div>
      <div class="block mt-1 text-center">
        <span class="inline">100.00%</span>
        <p class="mt-0">Last 30 days</p>
      </div>
    </div>
  </div>
</div>


</body>

<footer>


</footer>

</html>
