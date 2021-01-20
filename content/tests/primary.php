<?php

use PHPUnit\Framework\TestCase;

class TestsPrimary extends TestCase {

	public function setUp(): void {
		//Load classes
		function dat_loader($class) {
				include 'class/' . $class . '.php';
		}

		spl_autoload_register('dat_loader');
	}

  public function testComponents() {
		//Testing escape
		$result = Page::escape("<script>alert('attacked')</script>");
		$this->assertEquals($result,"&lt;script&gt;alert(&#039;attacked&#039;)&lt;/script&gt;");
  }
}
?>
