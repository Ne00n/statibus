<html>

<head>
  <meta charset="utf-8">
  <title>statibus</title>
  <link rel="stylesheet" href="css/code.css">
</head>

<body>
  <div class="container">
  <div class="item">
    <h1>whatever</h1>
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
      <div class="container">

        <div class="service">
          <p class="inline">Test</p>
          <span class="green inline pull-right mt-1 mr-1">100.00%</span>
        </div>
        <div class="uptime">
          <svg width="100%" height="20" viewBox="0 0 640 20">
            <?php
            for ($i = 0; $i <= 630; $i = $i +7) {
              echo '<rect class="rgreen" height="18" width="5" x="'.$i.'"></rect>';
            }
            ?>
          </svg>
        </div>
        <div class="status">
          <p class="green pull-right mr-1">Up</p>
        </div>

      </div>
      <div class="container">

        <div class="service">
          <p class="inline">Test</p>
          <span class="green inline pull-right mt-1 mr-1">100.00%</span>
        </div>
        <div class="uptime">
          <svg width="100%" height="20" viewBox="0 0 640 20">
            <?php
            for ($i = 0; $i <= 630; $i = $i +7) {
              echo '<rect class="rorange" height="18" width="5" x="'.$i.'"></rect>';
            }
            ?>
          </svg>
        </div>
        <div class="status">
          <p class="green pull-right mr-1">Up</p>
        </div>

      </div>
      <div class="container">

        <div class="service">
          <p class="inline">Test</p>
          <span class="green inline pull-right mt-1 mr-1">100.00%</span>
        </div>
        <div class="uptime">
          <svg width="100%" height="20" viewBox="0 0 640 20">
            <?php
            for ($i = 0; $i <= 630; $i = $i +7) {
              echo '<rect class="rred" height="18" width="5" x="'.$i.'"></rect>';
            }
            ?>
          </svg>
        </div>
        <div class="status">
          <p class="green pull-right mr-1">Up</p>
        </div>

      </div>

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
