<?php

use PHPUnit\Framework\TestCase;

class TestsMain extends TestCase {

  private $statibus;

	public function setUp(): void {
    //Load config
    include 'content/config.example.php';
		//Load classes
		function dat_loader($class) {
				include 'class/' . $class . '.php';
		}

		spl_autoload_register('dat_loader');
    $this->statibus = new statibus($rqliteIP,$rqlitePort);
	}

  public function testComponents() {
    //Init
    $this->assertTrue($this->statibus->sql()->init());
  }
}
?>
