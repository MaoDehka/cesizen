<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GestSupService
{
    private $apiUrl;
    private $apiKey;
    
    public function __construct()
    {
        $this->apiUrl = config('services.gestsup.api_url');
        $this->apiKey = config('services.gestsup.api_key');
    }
    
    public function createTicket($data)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '/tickets', [
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'priority' => $data['priority'],
            'project_id' => config('services.gestsup.project_id')
        ]);
        
        if ($response->successful()) {
            return $response->json();
        }
        
        throw new \Exception('Erreur lors de la création du ticket Gestsup');
    }
    
    public function updateTicketStatus($ticketId, $status)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->patch($this->apiUrl . "/tickets/{$ticketId}", [
            'status' => $status
        ]);
        
        return $response->successful();
    }
    
    public function getTicketMetrics()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey
        ])->get($this->apiUrl . '/analytics/tickets');
        
        return $response->successful() ? $response->json() : null;
    }
}