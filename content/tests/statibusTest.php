<?php

#Load config
include_once 'configs/config.example.php';

use PHPUnit\Framework\TestCase;

class statibusTest extends TestCase {
  private $statibus;

  public function setUp(): void {
    $this->statibus = new statibus(_rqliteIP,_rqlitePort);
  }

  public function testAddGroup(): void {
    $this->assertTrue($this->statibus->groupAdd(array(3 => 'Servers')));
  }

  public function testDeleteGroup(): void {
    $this->assertTrue($this->statibus->groupDelete(array(3 => 'Servers')));
  }

}
