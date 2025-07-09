<?php
return [
    'api_url' => env('GESTSUP_API_URL', 'https://api.gestsup.fr/v1'),
    'api_key' => env('GESTSUP_API_KEY'),
    'project_id' => env('GESTSUP_PROJECT_ID'),
    
    'sla' => [
        'critique' => [
            'response_time_hours' => 2,
            'resolution_time_hours' => 4,
            'escalation_levels' => ['lead_dev', 'cto', 'client']
        ],
        'elevee' => [
            'response_time_hours' => 4,
            'resolution_time_hours' => 24,
            'escalation_levels' => ['lead_dev', 'product_owner']
        ],
        'normale' => [
            'response_time_hours' => 24,
            'resolution_time_hours' => 168, // 1 semaine
            'escalation_levels' => ['assignee']
        ],
        'faible' => [
            'response_time_hours' => 72,
            'resolution_time_hours' => 720, // 1 mois
            'escalation_levels' => ['assignee']
        ]
    ],
    
    'webhook_secret' => env('GESTSUP_WEBHOOK_SECRET'),
    
    'ticket_types' => [
        'bug' => 'Bug',
        'evolution' => 'Évolution',
        'amelioration' => 'Amélioration',
        'support' => 'Support'
    ],
    
    'priorities' => [
        'critique' => 'Critique',
        'elevee' => 'Élevée', 
        'normale' => 'Normale',
        'faible' => 'Faible'
    ]
];