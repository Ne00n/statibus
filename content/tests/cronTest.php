<?php

#Load config
include_once 'configs/config.example.php';

use PHPUnit\Framework\TestCase;

class cronTest extends TestCase {

  public function setUp(): void {
    $this->cron = new cron(_rqliteIP,_rqlitePort);
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

}
