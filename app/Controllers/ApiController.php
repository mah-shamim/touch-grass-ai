<?php
namespace TouchGrass\Controllers;
use TouchGrass\Core\Database;
use TouchGrass\Core\Request;
use TouchGrass\Core\Response;
use TouchGrass\Services\OutdoorPlanner;
final class ApiController {
    public function __construct(private Database $db, private OutdoorPlanner $planner) {}
    public function ai(string $mode, Request $request): never {
        $input=$request->json();
        try {
            $result = match($mode) {
                'outing' => $this->planner->plan(['activity'=>$input['activity']??'walk','location'=>$input['location']??'nearby green space','duration'=>$input['duration']??45,'difficulty'=>$input['difficulty']??'easy','notes'=>$input['notes']??'']),
                'garden' => $this->planner->garden(['crop'=>$input['crop']??'vegetables','location'=>$input['location']??'home garden','frost_date'=>$input['frost_date']??'not provided','season'=>$input['season']??date('F'),'notes'=>$input['notes']??'']),
                'birding' => $this->planner->birding(['location'=>$input['location']??'local park','notes'=>$input['notes']??'nothing yet']),
                default => ['ok'=>false,'error'=>'Unknown mode'],
            };
            Response::json($result, $result['ok']??false ? 200 : 503);
        } catch (\Throwable $e) { Response::json(['ok'=>false,'error'=>'Unexpected server error','detail'=>$e->getMessage()],500); }
    }
    public function saveOuting(Request $r): never {
        $i=$r->json(); $stmt=$this->db->pdo()->prepare('INSERT INTO outings(activity,title,location,duration_minutes,difficulty,notes,ai_plan) VALUES(?,?,?,?,?,?,?)');
        $stmt->execute([$i['activity']??'walk',$i['title']??'Outdoor mission',$i['location']??'',$i['duration']??45,$i['difficulty']??'easy',$i['notes']??'',$i['ai_plan']??'']); Response::json(['ok'=>true,'id'=>$this->db->pdo()->lastInsertId()]);
    }
    public function logNature(Request $r): never {
        $i=$r->json(); $stmt=$this->db->pdo()->prepare('INSERT INTO nature_logs(kind,name,location,notes) VALUES(?,?,?,?)'); $stmt->execute([$i['kind']??'observation',$i['name']??'Nature observation',$i['location']??'',$i['notes']??'']); Response::json(['ok'=>true,'id'=>$this->db->pdo()->lastInsertId()]);
    }
}
