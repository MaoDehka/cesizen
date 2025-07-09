<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Services\GitHubService;

class GestSupWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('Webhook Gestsup reçu', $payload);
        
        switch ($payload['event']) {
            case 'ticket.created':
                $this->handleTicketCreated($payload['ticket']);
                break;
            case 'ticket.updated':
                $this->handleTicketUpdated($payload['ticket']);
                break;
            case 'ticket.resolved':
                $this->handleTicketResolved($payload['ticket']);
                break;
        }
        
        return response()->json(['status' => 'ok']);
    }
    
    private function handleTicketCreated($ticketData)
    {
        // Créer automatiquement une branche Git si c'est une évolution
        if ($ticketData['type'] === 'evolution') {
            $branchName = "feature/GEST-{$ticketData['id']}-" . 
                         str_slug($ticketData['title']);
            
            GitHubService::createBranch($branchName);
        }
    }
}