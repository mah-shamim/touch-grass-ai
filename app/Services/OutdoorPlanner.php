<?php
namespace TouchGrass\Services;
final class OutdoorPlanner {
    public function __construct(private OllamaService $ai) {}
    public function plan(array $input): array {
        $system = "You are TouchGrass AI, a safety-conscious outdoor activity coach. Help people move from screen to outdoor activity. Never claim live trail closures, weather, emergency conditions, wildlife certainty, or medical facts. Keep the plan practical and concise: at most 6 short bullets.";
        $prompt = "Create a short outdoor mission.\nActivity: {$input['activity']}\nArea: {$input['location']}\nAvailable time: {$input['duration']} minutes\nExperience: {$input['difficulty']}\nPreferences: {$input['notes']}\nInclude a simple activity goal, a few steps, what to bring, a safety check that says to verify current local conditions, one observation prompt, and a return/stop trigger. Finish with a reminder to put the phone away.";
        return $this->ai->chat($system, $prompt);
    }
    public function garden(array $input): array {
        $system = "You are a concise, local-first gardening assistant. Never claim live weather or exact local frost information unless the user provided it. Note assumptions.";
        $prompt = "Give a concise weekly garden plan for {$input['crop']} in {$input['location']}. User-supplied frost date: {$input['frost_date']}. Season: {$input['season']}. Preferences: {$input['notes']}. In at most 6 short bullets include a small prep/planting task, watering and sunlight checks, pest observation, a progress checkpoint, and one task under 30 minutes.";
        return $this->ai->chat($system, $prompt);
    }
    public function birding(array $input): array {
        $system = "You are a concise educational birding guide. Do not identify a species from text alone. Encourage uncertainty-aware observation and ethical wildlife viewing.";
        $prompt = "Build a beginner birding mission for {$input['location']}. User saw/heard: {$input['notes']}. In at most 6 short bullets include three field marks or behaviors to observe, one habitat clue, one question to distinguish possibilities, wildlife ethics, and a 20-minute no-screen challenge. Do not claim live local species data.";
        return $this->ai->chat($system, $prompt);
    }
}
