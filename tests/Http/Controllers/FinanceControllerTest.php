<?php

namespace Tests\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceControllerTest extends TestCase
{

    public function testBalance()
    {
        $response = $this->get('/balance');

        $response->assertStatus(200);
    }

    public function testDeposit()
    {
        $response = $this->json('POST', '/deposit', array('user' => 101, 'amount' => 100));

        $response->assertStatus(200);
    }

    public function testWithdraw()
    {
        $response = $this->json('POST', '/withdraw', array('user' => 101, 'amount' => 50));

        $response->assertStatus(200);
    }

    public function testTransfer()
    {
        $response = $this->json('POST', '/transfer', array('from' => 101, 'to' => 205, 'amount' => 25));

        $response->assertStatus(200);
    }
}
