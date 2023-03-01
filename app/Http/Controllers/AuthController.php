<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Helpers\DbHelper;
use Carbon\Carbon;


class AuthController extends Controller
{
    public function clientCredentialsAccessToken(Request $request)
    {
        try {
            /*$client_id = $request->input('client_id');
            
            $client_secret = $request->input('client_secret');
            
            $tokenRequest = Http::asForm()->post('http://localhost:8000/oauth/token', [
			//$tokenRequest = Http::asForm()->post(url('/') . '/oauth/token', [
            //$tokenRequest = Http::asForm()->post(config('app.url') . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ]);
            $res = json_decode((string)$tokenRequest->getBody());*/
            //$response = Http::asForm()->post('http://localhost:8000/oauth/token', [
            //echo url('/') . '/oauth/token';
            //exit();
            //echo url('/') . '/oauth/token';
            //exit();
            $tokenRequest = Http::asForm()->timeout(30)->post(url('/') . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => '9743a477-41c3-47fa-bbe2-d1ab87f83bcb',
                'client_secret' => 'F0gSDutq24oDxPi90y0cLoYbgZUj8qTeXSIFtj0Q',
                'scope' => '',
            ]);
            print_r($tokenRequest);
            exit();
            $res = json_decode((string)$tokenRequest->getBody());
            //return $response->json()['access_token'];
        } catch (\Exception $exception) {
            $res = array(
                'success' => false,
                'message' => $exception->getMessage()
            );
        } catch (\Throwable $throwable) {
            $res = array(
                'success' => false,
                'message' => $throwable->getMessage()
            );
        }
        return response()->json($res);
    }




}
