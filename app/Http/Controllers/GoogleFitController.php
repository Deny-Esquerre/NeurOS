<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Fitness;
use Google_Service_Fitness_DataSource;

class GoogleFitController extends Controller
{
    // Redirige al usuario a la pantalla de autorización de Google
    public function redirect()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline'); // Solicita un refresh token
        $client->setApprovalPrompt('force');
        $client->addScope([
            Google_Service_Fitness::FITNESS_HEART_RATE_READ,
            Google_Service_Fitness::FITNESS_HEART_RATE_WRITE,
            Google_Service_Fitness::FITNESS_ACTIVITY_WRITE,
            'email',
            'profile',
            'openid'
        ]);

        return redirect($client->createAuthUrl());
    }

    // Recibe el token de Google
    public function callback(Request $request)
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));

        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect('/admin/watch-datas')->with('error', 'Error al conectar con Google Fit: ' . $token['error_description']);
        }

        // Guardar el token en el usuario autenticado
        $user = auth()->user();
        $user->google_token = $token;
        $user->save();

        return redirect('/admin/watch-datas')->with('success', 'Conexión con Google Fit exitosa.');
    }






}