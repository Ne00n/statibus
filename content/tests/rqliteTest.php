<?php

#Load config
include_once 'content/configs/config.example.php';

use PHPUnit\Framework\TestCase;

class rqliteTest extends TestCase {
  private $rqlite;

  public function setUp(): void {
    $this->rqlite = new rqlite($rqliteIP,$rqlitePort);
  }

  public function testInit(): void {
    $this->assertTrue($this->rqlite->init());
  }

}
