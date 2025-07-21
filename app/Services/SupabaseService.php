<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SupabaseService
{
    protected $client;
    protected $url;
    protected $anonKey;
    protected $serviceKey;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->anonKey = config('services.supabase.anon_key');
        $this->serviceKey = config('services.supabase.service_key');

        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'Content-Type' => 'application/json',
                'apikey' => $this->anonKey,
                'Authorization' => 'Bearer ' . $this->anonKey,
            ],
        ]);
    }

    /**
     * Sign up a new user
     */
    public function signUp(array $credentials)
    {
        try {
            $response = $this->client->post('/auth/v1/signup', [
                'json' => $credentials
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new \Exception('Supabase signup failed: ' . $e->getMessage());
        }
    }

    /**
     * Sign in a user
     */
    public function signIn(array $credentials)
    {
        try {
            $response = $this->client->post('/auth/v1/token?grant_type=password', [
                'json' => $credentials
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new \Exception('Supabase signin failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user data
     */
    public function getUser($accessToken)
    {
        try {
            $response = $this->client->get('/auth/v1/user', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new \Exception('Failed to get user data: ' . $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function updateUser($accessToken, array $data)
    {
        try {
            $response = $this->client->put('/auth/v1/user', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'json' => $data
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            throw new \Exception('Failed to update user: ' . $e->getMessage());
        }
    }
}
