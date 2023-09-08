<?php

#Load config
include_once 'configs/config.example.php';

use PHPUnit\Framework\TestCase;

class statibusTest extends TestCase {
  private $statibus;
  private $cron;

  public function setUp(): void {
    $this->statibus = new statibus(_rqliteIP,_rqlitePort);
    $this->cron = new cron(_rqliteIP,_rqlitePort);
  }

  public function testAddGroup(): void {
    $this->assertTrue($this->statibus->groupAdd(array(3 => 'Servers')));
  }

  public function testListGroup(): void {
    $this->assertTrue($this->statibus->list("groups"));
  }

  public function testAddService(): void {
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'Server',5 => "ping",6 => "8.8.8.8")));
    $this->assertFalse($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'Server',5 => "ping",6 => "8.8.8.8")));
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'HTTP',5 => "port",6 => "8.8.8.8:80",7 => 2)));
    $this->assertTrue($this->statibus->serviceAdd(array(3 => 'Servers',4 => 'Website',5 => "http",6 => "https://website.com",7 => 4,8 => 200)));
  }

  public function testListService(): void {
    $this->assertTrue($this->statibus->list("services"));
  }

  public function testRun(): void {
    $this->assertTrue($this->cron->run());
  }

  public function testFindFalsePositives(): void {
    $this->assertTrue($this->cron->findFalsePositives());
  }

  public function testUptime(): void {
    $this->assertTrue($this->cron->uptime());
  }

  public function testDeleteService(): void {
    $this->assertTrue($this->statibus->delete("services",array(3 => 'Server')));
  }

  public function testDeleteGroup(): void {

    $this->assertFalse($this->statibus->delete("groups",array(3 => 'Servers')));
    $this->assertTrue($this->statibus->delete("services",array(3 => 'HTTP')));
    $this->assertTrue($this->statibus->delete("services",array(3 => 'Website')));
    $this->assertTrue($this->statibus->delete("groups",array(3 => 'Servers')));
  }

}
