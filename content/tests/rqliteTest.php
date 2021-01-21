<?php

#Load config
include_once 'configs/config.example.php';

use PHPUnit\Framework\TestCase;

class rqliteTest extends TestCase {
  private $rqlite;

  public function setUp(): void {
    $this->rqlite = new rqlite(_rqliteIP,_rqlitePort);
  }

  public function testInit(): void {
    $this->assertTrue($this->rqlite->init());
  }

}
