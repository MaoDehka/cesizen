<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestSupService
{
    private function getGestSupDB()
    {
        // Connexion directe à la base Gestsup
        return DB::connection('gestsup');
    }
    
    public function createTicket($data)
    {
        try {
            // Insérer directement dans la base Gestsup
            $ticketId = $this->getGestSupDB()->table('tickets')->insertGetId([
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => strtoupper($data['type']),
                'priority' => strtoupper($data['priority']),
                'creator_id' => $data['creator_id'] ?? 1,
                'gestsup_id' => 'GEST-' . str_pad(time() % 10000, 4, '0', STR_PAD_LEFT),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'id' => $ticketId,
                'status' => 'created',
                'url' => config('app.url') . '/tickets/view.php?id=' . $ticketId
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur création ticket Gestsup: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateTicketStatus($ticketId, $status)
    {
        return $this->getGestSupDB()->table('tickets')
            ->where('id', $ticketId)
            ->update([
                'status' => $status,
                'updated_at' => now()
            ]);
    }
    
    public function getTicketMetrics()
    {
        $db = $this->getGestSupDB();
        
        return [
            'total_tickets' => $db->table('tickets')->count(),
            'open_tickets' => $db->table('tickets')->whereNotIn('status', ['RESOLU', 'FERME'])->count(),
            'resolved_this_month' => $db->table('tickets')
                ->where('status', 'RESOLU')
                ->whereMonth('updated_at', now()->month)
                ->count()
        ];
    }
}