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

  public function testAddService(): void {
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'Server',5 => "ping",6 => "8.8.8.8")));
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'HTTP',5 => "port",6 => "8.8.8.8:80",7 => 2)));
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'Website',5 => "http",6 => "https://website.com",7 => 4,8 => 200)));
  }

  public function testListService(): void {
    $this->assertTrue($this->statibus->serviceList(array()));
  }

  public function testDeleteService(): void {
    $this->assertTrue($this->statibus->serviceDelete(array(3 => 'Server')));
  }

  public function testDeleteGroup(): void {
    $this->assertFalse($this->statibus->groupDelete(array(3 => 'Servers')));
    $this->assertTrue($this->statibus->serviceDelete(array(3 => 'HTTP')));
    $this->assertTrue($this->statibus->serviceDelete(array(3 => 'Website')));
    $this->assertTrue($this->statibus->groupDelete(array(3 => 'Servers')));
  }

}
