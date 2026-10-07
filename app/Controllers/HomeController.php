<?php
namespace TouchGrass\Controllers;
use TouchGrass\Core\Database;
final class HomeController {
    public function __construct(private Database $db) {}
    public function index(): array {
        $outings=$this->db->pdo()->query('SELECT * FROM outings ORDER BY id DESC LIMIT 6')->fetchAll();
        $logs=$this->db->pdo()->query('SELECT * FROM nature_logs ORDER BY id DESC LIMIT 6')->fetchAll();
        $roadmap=[
            ['title'=>'GPS track recording','group'=>'tracking','summary'=>'Capture a walk, run, or hike as a private route trace so distance and pacing can be reviewed without sending data to the cloud.'],
            ['title'=>'GPX export','group'=>'tracking','summary'=>'Export route traces for hiking apps, sharing with a running club, or storing a personal record of favorite loops.'],
            ['title'=>'Offline maps','group'=>'tracking','summary'=>'Load cached local tiles and route overlays so the app remains useful when the signal disappears in the field.'],
            ['title'=>'Weather integration','group'=>'tracking','summary'=>'Blend current and forecast weather into the mission plan with lightweight safety prompts and gear suggestions.'],
            ['title'=>'Bird photo analysis','group'=>'birding','summary'=>'Use local vision checks to identify bird traits from a photo, including color, body shape, and habitat hints.'],
            ['title'=>'Bird-call recognition','group'=>'birding','summary'=>'Add local audio classification for common species, keeping sound-based identification in a privacy-friendly workflow.'],
            ['title'=>'Local speech-to-text','group'=>'voice','summary'=>'Convert field notes or mission prompts to text directly on-device for rough, hands-free logging in the wild.'],
            ['title'=>'Local text-to-speech','group'=>'voice','summary'=>'Read back a mission, safety note, or birding prompt in a calm natural voice without requiring an internet connection.'],
            ['title'=>'Voice outdoor assistant','group'=>'voice','summary'=>'Support a low-distraction voice loop: ask for a route, safety check, or observation prompt while keeping the screen down.'],
            ['title'=>'QR mission sharing','group'=>'sharing','summary'=>'Generate a QR code for a mission or route so group members can open a shared plan instantly on a phone.'],
            ['title'=>'Run-club route sharing','group'=>'sharing','summary'=>'Let local clubs publish safe routes, challenge loops, and pace variations without relying on a centralized platform.'],
            ['title'=>'Garden frost-date database','group'=>'garden','summary'=>'Add region-aware frost-date guidance and planting windows to improve seasonal timing for gardens and micro-farms.'],
            ['title'=>'Local RAG field guide','group'=>'knowledge','summary'=>'Offer a grounded field guide of plants, birds, insects, and habitats with local retrieval over curated notes.'],
            ['title'=>'Embedding search','group'=>'knowledge','summary'=>'Find relevant field observations or mission notes by semantic similarity rather than only text matching.'],
            ['title'=>'Family profiles','group'=>'knowledge','summary'=>'Track favorite activities, skill level, and accessibility needs for different people using the app as a family goal planner.'],
            ['title'=>'Accessibility mode','group'=>'knowledge','summary'=>'Add larger text, high-contrast themes, voice-first flows, and simplified mission screens for inclusive use outdoors.'],
            ['title'=>'FunctionGemma tool calling','group'=>'ai','summary'=>'Enable local tool-calling patterns so the assistant can read weather context, interpret GPS data, and trigger route actions safely.'],
            ['title'=>'Local AI agent actions','group'=>'ai','summary'=>'Let a local assistant trigger safe actions like save observation, generate route, or open a guided outdoor prompt.'],
            ['title'=>'Outdoor-domain LoRA fine-tuning','group'=>'ai','summary'=>'Train a smaller domain-specific model on local field notes and routes for better outdoor planning with compact weights.'],
            ['title'=>'Model evaluation framework','group'=>'ai','summary'=>'Add repeatable benchmark checks for route quality, safety guidance, birding prompts, and prompt reliability in local modes.'],
            ['title'=>'Mobile/PWA packaging','group'=>'platform','summary'=>'Package the project as a stronger mobile-first PWA with install support, offline caching, and lightweight native wrappers.'],
        ];
        return compact('outings','logs','roadmap');
    }
}
