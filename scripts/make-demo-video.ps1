param(
    [string] $OutputPath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'docs\touchgrass-ai-data-entry-demo.mp4')
)

$ErrorActionPreference = 'Stop'

try {
    $health = Invoke-RestMethod -Uri 'http://localhost:8081/api/health' -TimeoutSec 5
} catch {
    throw 'TouchGrass AI is not responding at http://localhost:8081. Start it with docker compose up -d, then retry.'
}

if (-not $health.ok -or -not $health.ai.ok) {
    throw 'The TouchGrass AI app or its local Ollama runtime is not healthy.'
}

$toolsDirectory = Join-Path $env:TEMP 'touchgrass-video-recorder-tools'
$playwrightPackage = Join-Path $toolsDirectory 'node_modules\playwright-core\package.json'
if (-not (Test-Path -LiteralPath $playwrightPackage)) {
    & npm install --prefix $toolsDirectory --no-save --no-package-lock playwright-core@1.49.1
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path -LiteralPath $playwrightPackage)) {
        throw 'Could not install temporary Playwright browser-recording support.'
    }
}

$edgePath = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
if (-not (Test-Path -LiteralPath $edgePath)) {
    $edgePath = 'C:\Program Files\Microsoft\Edge\Application\msedge.exe'
}
if (-not (Test-Path -LiteralPath $edgePath)) {
    throw 'Microsoft Edge is required to record the original TouchGrass AI website.'
}

$tempDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ('touchgrass-site-recording-' + [guid]::NewGuid().ToString('N'))
$audioDirectory = Join-Path $tempDirectory 'audio'
$outputDirectory = Split-Path -Parent $OutputPath
$outputName = Split-Path -Leaf $OutputPath
$frameDirectory = Join-Path $tempDirectory 'frames'
$recorderScript = Join-Path $PSScriptRoot 'record-site-demo.cjs'
$voices = @(
    'This is TouchGrass AI.',
    'Create a 45-minute birding mission.',
    'Now I disconnect the internet.',
    'Take a bird photo.',
    'Local Gemma analyzes it.',
    'Save the observation and put the phone away.'
)

New-Item -ItemType Directory -Path $tempDirectory -Force | Out-Null
New-Item -ItemType Directory -Path $audioDirectory -Force | Out-Null
New-Item -ItemType Directory -Path $outputDirectory -Force | Out-Null

try {
    Add-Type -AssemblyName System.Speech
    $speech = New-Object System.Speech.Synthesis.SpeechSynthesizer
    $speech.Rate = -1
    $speech.Volume = 90
    for ($index = 0; $index -lt $voices.Count; $index++) {
        $voicePath = Join-Path $audioDirectory ('voice-' + $index.ToString('D2') + '.wav')
        $speech.SetOutputToWaveFile($voicePath)
        $speech.Speak($voices[$index])
    }
    $speech.Dispose()

    $env:TOUCHGRASS_VIDEO_TOOLS = $toolsDirectory
    $env:TOUCHGRASS_EDGE_PATH = $edgePath
    $env:TOUCHGRASS_FRAME_DIRECTORY = $frameDirectory
    $env:TOUCHGRASS_APP_URL = 'http://localhost:8081/'
    & node $recorderScript
    if ($LASTEXITCODE -ne 0) {
        throw "The real-site browser recording failed with exit code $LASTEXITCODE."
    }

    $workMount = $tempDirectory.Replace([char]92, '/')
    $outputMount = ([System.IO.Path]::GetFullPath($outputDirectory)).Replace([char]92, '/')
    $containerOutput = '/output/' + $outputName
    $dockerArguments = @(
        'run', '--rm',
        '--volume', "${workMount}:/work",
        '--volume', "${outputMount}:/output",
        'jrottenberg/ffmpeg:6.0-alpine',
        '-hide_banner', '-loglevel', 'error', '-y',
        '-framerate', '4', '-i', '/work/frames/frame-%04d.png'
    )
    for ($index = 0; $index -lt $voices.Count; $index++) {
        $dockerArguments += @('-i', ('/work/audio/voice-' + $index.ToString('D2') + '.wav'))
    }

    $filterParts = @()
    for ($index = 0; $index -lt $voices.Count; $index++) {
        $audioInput = $index + 1
        $filterParts += ('[{0}:a]aresample=48000,apad=whole_dur=10,atrim=duration=10,asetpts=PTS-STARTPTS[a{1}]' -f $audioInput, $index)
    }
    $audioInputs = -join (0..($voices.Count - 1) | ForEach-Object { '[a{0}]' -f $_ })
    $filterParts += ($audioInputs + 'concat=n=6:v=0:a=1[aout]')
    $dockerArguments += @(
        '-filter_complex', ($filterParts -join ';'),
        '-map', '0:v:0', '-map', '[aout]',
        '-vf', 'fps=24,format=yuv420p',
        '-c:v', 'libx264', '-preset', 'medium', '-crf', '21',
        '-c:a', 'aac', '-b:a', '128k',
        '-t', '60', '-movflags', '+faststart', $containerOutput
    )

    & docker @dockerArguments
    if ($LASTEXITCODE -ne 0) {
        throw "FFmpeg failed with exit code $LASTEXITCODE."
    }

    $absoluteOutput = [System.IO.Path]::GetFullPath($OutputPath)
    Write-Output "60-second TouchGrass AI site walkthrough created: $absoluteOutput"
} finally {
    if ($speech) {
        $speech.Dispose()
    }
    if (Test-Path -LiteralPath $tempDirectory) {
        Remove-Item -LiteralPath $tempDirectory -Recurse -Force
    }
    Remove-Item Env:TOUCHGRASS_VIDEO_TOOLS -ErrorAction SilentlyContinue
    Remove-Item Env:TOUCHGRASS_EDGE_PATH -ErrorAction SilentlyContinue
    Remove-Item Env:TOUCHGRASS_FRAME_DIRECTORY -ErrorAction SilentlyContinue
    Remove-Item Env:TOUCHGRASS_APP_URL -ErrorAction SilentlyContinue
}
