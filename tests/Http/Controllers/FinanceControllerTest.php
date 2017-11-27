<?php

namespace Tests\Http\Controllers;

use App\Client;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $users = array();

    protected function setUp()
    {
        parent::setUp();
        $users = factory(Client::class, 2)->create();
        $this->users = $users->sortBy('balance');
    }

    public function testBalanceWithValidUser()
    {
        $user = $this->users->random();
        $response = $this->get('/api/balance/?user=' . $user->id);

        $response->assertStatus(200);
        $response->assertExactJson(array('balance' => $user->balance));
    }

    public function testBalanceWithInvalidUser()
    {
        $response = $this->get('/api/balance/?user=0');
        $response->assertStatus(422);

        $response = $this->get('/api/balance/?user=a');
        $response->assertStatus(422);

        $response = $this->get('/api/balance');
        $response->assertStatus(422);
    }

    public function testDepositWithExistingUser()
    {
        $user = $this->users->random();
        $balanceBeforeUpdate = $user->balance;
        $amount = 100;
        $response = $this->json('POST', '/api/deposit', array('user' => $user->id, 'amount' => $amount));

        $response->assertStatus(200);
        $this->assertEquals($balanceBeforeUpdate + $amount, $user->fresh()->balance);
    }

    public function testDepositWithUnexistingUser()
    {
        $id = mt_rand(1000, 1200);
        $amount = 90;
        $response = $this->json('POST', '/api/deposit', array('user' => $id, 'amount' => $amount));

        $response->assertStatus(200);
        $client = Client::where('id', $id)->first();
        $this->assertNotNull($client);
        $this->assertEquals($amount, $client->balance);
    }

    public function testDepositInvalidParameters()
    {
        $amount = 90;

        $response = $this->json('POST', '/api/deposit', array('user' => 'a', 'amount' => $amount));
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/deposit', array('amount' => $amount));
        $response->assertStatus(422);

        $response = $this->json('POST', '/api/deposit', array('user' => 1));
        $response->assertStatus(422);
    }

    public function testWithdrawWithExistingUser()
    {
        $user = $this->users->random();
        $balanceBeforeUpdate = $user->balance;
        $amount = 25;
        $response = $this->json('POST', '/api/withdraw', array('user' => $user->id, 'amount' => $amount));

        $response->assertStatus(200);
        $this->assertEquals($balanceBeforeUpdate - $amount, $user->fresh()->balance);
    }

    public function testWithdrawWithUnexistingUser()
    {
        $id = mt_rand(1, 99);
        $amount = 25;
        $response = $this->json('POST', '/api/withdraw', array('user' => $id, 'amount' => $amount));

        $response->assertStatus(422);
    }

    public function testTransferWithExistingUsers()
    {
        $userFrom = $this->users->shift();
        $balanceBeforeUpdateFrom = $userFrom->balance;
        $userTo = $this->users->shift();
        $balanceBeforeUpdateTo = $userTo->balance;
        $amount = mt_rand(1, 99);
        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $userFrom->id,
                'to' => $userTo->id,
                'amount' => $amount
            )
        );

        $response->assertStatus(200);
        $this->assertEquals($balanceBeforeUpdateFrom - $amount, $userFrom->fresh()->balance);
        $this->assertEquals($balanceBeforeUpdateTo + $amount, $userTo->fresh()->balance);
    }

    public function testTransferWithNonExistingAcceptor()
    {
        $userFrom = $this->users->shift();
        $balanceBeforeUpdateFrom = $userFrom->balance;
        $acceptorId = mt_rand(1, 99);
        $amount = mt_rand(1, 99);
        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $userFrom->id,
                'to' => $acceptorId,
                'amount' => $amount
            )
        );

        $response->assertStatus(200);
        $this->assertEquals($balanceBeforeUpdateFrom - $amount, $userFrom->fresh()->balance);
        $client = Client::where('id', $acceptorId)->first();
        $this->assertNotNull($client);
        $this->assertEquals($amount, $client->balance);
    }

    public function testTransferWithNonexistingDonor()
    {
        $donorId = mt_rand(1, 99);
        $userTo = $this->users->shift();
        $amount = mt_rand(1, 99);
        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $donorId,
                'to' => $userTo->id,
                'amount' => $amount
            )
        );

        $response->assertStatus(422);
    }

    public function testTransferWithInvalidParameters()
    {
        $userFrom = $this->users->shift();
        $userTo = $this->users->shift();
        $amount = mt_rand(1, 99);

        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'to' => $userTo->id,
                'amount' => $amount
            )
        );
        $response->assertStatus(422);

        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $userFrom->id,
                'amount' => $amount
            )
        );
        $response->assertStatus(422);

        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $userFrom->id,
                'to' => $userTo->id
            )
        );
        $response->assertStatus(422);

        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => 'a',
                'to' => 'b',
                'amount' => $amount
            )
        );
        $response->assertStatus(422);

        $response = $this->json(
            'POST',
            '/api/transfer',
            array(
                'from' => $userFrom->id,
                'to' => $userTo->id,
                'amount' => -100
            )
        );
        $response->assertStatus(422);
    }
}
