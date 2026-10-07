<?php
require __DIR__ . '/../vendor/autoload.php';
$config=require __DIR__ . '/../app/Config/config.php';
use TouchGrass\Core\Database; use TouchGrass\Core\Request; use TouchGrass\Core\Response; use TouchGrass\Controllers\HomeController; use TouchGrass\Controllers\ApiController; use TouchGrass\Services\OllamaService; use TouchGrass\Services\OutdoorPlanner;
$db=new Database($config['db']['path']); $request=new Request(); $ai=new OllamaService($config['ai']['url'],$config['ai']['model'],$config['ai']['timeout']); $planner=new OutdoorPlanner($ai); $api=new ApiController($db,$planner);
$path=$request->path();
if (str_starts_with($path,'/api/')) { if($path==='/api/health') Response::json(['ok'=>true,'app'=>'TouchGrass AI','ai'=>$ai->health(),'model'=>$config['ai']['model']]); if($request->isPost() && preg_match('#^/api/ai/(outing|garden|birding)$#',$path,$m)) $api->ai($m[1],$request); if($request->isPost() && $path==='/api/outings') $api->saveOuting($request); if($request->isPost() && $path==='/api/logs') $api->logNature($request); Response::json(['ok'=>false,'error'=>'Not found'],404); }
if($path==='/about.php'){ $title='About · TouchGrass AI'; ob_start(); require __DIR__.'/../app/Views/about.php'; $content=ob_get_clean(); require __DIR__.'/../app/Views/layout.php'; exit; }
$data=(new HomeController($db))->index(); $title='TouchGrass AI'; extract($data); ob_start(); require __DIR__.'/../app/Views/home.php'; $content=ob_get_clean(); require __DIR__.'/../app/Views/layout.php';
