<?php
require __DIR__.'/../vendor/autoload.php';
use TouchGrass\Core\Database;
$path=sys_get_temp_dir().'/touchgrass-test-'.bin2hex(random_bytes(4)).'.sqlite';
$db=new Database($path); $pdo=$db->pdo();
$tables=$pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
foreach(['outings','nature_logs','garden_plans'] as $t) if(!in_array($t,$tables,true)) throw new RuntimeException("Missing $t");
$pdo->prepare('INSERT INTO nature_logs(kind,name) VALUES(?,?)')->execute(['bird','test bird']);
if((int)$pdo->query('SELECT COUNT(*) FROM nature_logs')->fetchColumn()!==1) throw new RuntimeException('CRUD test failed');
unlink($path); echo "PASS: database migrations + write/read test\n";
