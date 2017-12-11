<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function balance(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user' => 'required|integer|min:1'
        ]);
        if ($validator->fails()) {
            return JsonResponse::create(array('errors' => $validator->errors()), 422);
        }

        $client = Client::where('id', $input['user'])->first();
        if (is_null($client)) {
            return JsonResponse::create(array('errors' => array('Such user cannot be found')), 422);
        }

        return JsonResponse::create(array('balance' => $client->balance));
    }

    public function deposit(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user' => 'required|integer|min:1',
            'amount' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            return JsonResponse::create(array('errors' => $validator->errors()), 422);
        }

        DB::beginTransaction();
        try {
            $client = Client::firstOrNew(array('id' => $input['user']));
            $client->balance += $input['amount'];
            $client->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // It's better to send result, if frontend is using response for rendering SPA
        //return JsonResponse::create(array('user' => $client->id, 'balance' => $client->balance));

        return JsonResponse::create();
    }

    public function withdraw(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'user' => 'required|integer|min:1',
            'amount' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            return JsonResponse::create(array('errors' => $validator->errors()), 422);
        }

        $client = Client::where('id', $input['user'])->first();
        if (is_null($client)) {
            return JsonResponse::create(array('errors' => array('Such user cannot be found')), 422);
        }
        if ($client->balance < $input['amount']) {
            return JsonResponse::create(array('errors' => array('Not enough money')), 422);
        }

        DB::beginTransaction();
        try {
            $client->balance -= $input['amount'];
            $client->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // It's better to send result, if frontend is using response for rendering SPA
        //return JsonResponse::create(array('user' => $client->id, 'balance' => $client->balance));

        return JsonResponse::create();
    }

    public function transfer(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'from' => 'required|integer|min:1',
            'to' => 'required|integer|min:1',
            'amount' => 'required|integer|min:0'
        ]);
        if ($validator->fails()) {
            return JsonResponse::create(array('errors' => $validator->errors()), 422);
        }

        if ($input['from'] === $input['to']) {
            return JsonResponse::create(array('errors' => array('Same user pointed as sender and receiver')), 422);
        }
        $clientFrom = Client::where('id', $input['from'])->first();
        $clientTo = Client::firstOrNew(array('id' => $input['to']));
        if (is_null($clientFrom)) {
            return JsonResponse::create(array('errors' => array('Such user cannot be found')), 422);
        }

        if ($clientFrom->balance < $input['amount']) {
            return JsonResponse::create(array('errors' => array('Not enough money')), 422);
        }

        DB::beginTransaction();
        try {
            $clientFrom->balance -= $input['amount'];
            $clientFrom->save();
            $clientTo->balance += $input['amount'];
            $clientTo->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        // It's better to send result, if frontend is using response for rendering SPA
        /*return JsonResponse::create(
            array(
                array('from' => array('id' => $clientFrom->id, 'balance' => $clientFrom->balance)),
                array('to' => array('id' => $clientTo->id, 'balance' => $clientTo->balance))
            )
        );*/

        return JsonResponse::create();
    }
}
