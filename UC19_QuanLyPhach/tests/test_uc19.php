<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/Services/UC19Service.php';
require dirname(__DIR__) . '/app/Models/UC19Model.php';
require dirname(__DIR__) . '/app/Core/uc19-helpers.php';

function check(bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
}
function rejected(callable $fn, string $message): void
{
    try { $fn(); } catch (DomainException $e) {
        check(str_contains($e->getMessage(), $message), 'Sai thông báo: ' . $e->getMessage());
        return;
    }
    throw new RuntimeException('Chưa từ chối: ' . $message);
}
function fixture(): array
{
    $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $db->exec('PRAGMA foreign_keys=ON');
    $sql = file_get_contents(dirname(__DIR__) . '/database/uc19_demo.sql');
    $sql = preg_replace('/^CREATE DATABASE.*;|^USE .*;/m', '', $sql);
    $sql = str_replace([' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4','INSERT IGNORE',' AUTO_INCREMENT'],
        ['','INSERT OR IGNORE',''], $sql);
    $db->exec($sql);
    return [$db,new UC19Model($db)];
}
function total(PDO $db, string $table): int
{
    return (int)$db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
$tests=[];
$tests['TC01: Túi nhiều phòng, lưu toàn bộ quan hệ và nhật ký'] = function () {
    [$db,$m]=fixture();
    $papers=$m->handover(1,1);
    check(count(array_unique(array_column($papers,'room_id')))===3,'Phải có bài từ 3 phòng');
    $draft=$m->createDraft(1,1);
    check(total($db,'papers')===0 && total($db,'audit_logs')===0,'Xem trước đã ghi DB');
    $m->save($draft,1);
    $b=$m->bag(1);
    check($b['status']==='created' && (int)$b['created_by']===1 && $b['created_at']!==null,'Thiếu trạng thái/người/thời gian');
    check(count($m->grading(1,1))===3 && array_sum(array_column($m->grading(1,1),'sheet_count'))===6,'Sai phách hoặc số tờ');
    $rows=$db->query('SELECT * FROM papers ORDER BY handover_id')->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $row) {
        check((int)$row['bag_id']===1 && (int)$row['student_id']===(int)$row['handover_id']
            && $row['number']===$draft['codes'][$row['handover_id']],'Sai quan hệ đối chiếu');
    }
    $log=$m->logs(1,1);
    check(count($log)===1 && $log[0]['action']==='CREATE_CODES' && (int)$log[0]['sheet_count']===6,'Thiếu nhật ký');
};
$tests['TC02: Sinh lại phách trùng trong túi và trên toàn kỳ/môn'] = function () {
    $values=['111111111','222222222','222222222','333333333'];
    $codes=UC19Service::generate([['id'=>1],['id'=>2]],['111111111'],function() use (&$values){return array_shift($values);});
    check($codes===[1=>'222222222',2=>'333333333'],'Không sinh lại phách trùng');
};
$tests['TC03: Tạo lại một phách và từ chối bài ngoài túi'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1); $new=$m->regenerate($d,1,1);
    check($new['codes'][1]!==$d['codes'][1] && $new['codes'][2]===$d['codes'][2] && $new['codes'][3]===$d['codes'][3],'Tạo lại không đúng bài');
    check($new['token']!==$d['token'] && total($db,'papers')===0,'Sai token hoặc ghi sớm');
    rejected(fn()=>$m->regenerate($d,4,1),'không thuộc bản xem trước');
};
$tests['TC04: Hủy bản dự kiến không thay đổi dữ liệu'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1); unset($d);
    check($m->bag(1)['status']==='pending' && total($db,'papers')===0 && total($db,'audit_logs')===0,'Hủy đã lưu');
};
$tests['TC05: Xử lý túi khác không thay đổi túi đã lưu'] = function () {
    [$db,$m]=fixture(); $m->save($m->createDraft(1,1),1); $before=$m->grading(1,1);
    $m->save($m->createDraft(2,1),1);
    check($before===$m->grading(1,1) && count($m->grading(2,1))===2,'Túi cũ bị thay đổi');
    check(count(array_unique($m->used(1)))===5,'Trùng phách giữa túi');
};
$tests['TC06: Bộ lọc chỉ tự luận và vẫn có phạm vi chưa có túi'] = function () {
    [$db,$m]=fixture(); $catalog=$m->catalog();
    check(count($catalog)===3 && !in_array(3,array_column($catalog,'subject_id')),'Có môn trắc nghiệm');
    check(in_array(3,array_column($catalog,'scope_id')) && $m->bags(3)===[],'Thiếu phạm vi rỗng');
};
$tests['TC07: Chặn tạo hoặc lưu lại túi đã tạo phách'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1); $m->save($d,1);
    rejected(fn()=>$m->createDraft(1,1),'Túi chấm đã được tạo phách');
    rejected(fn()=>$m->save($d,1),'Túi chấm đã được tạo phách');
    check(total($db,'audit_logs')===1,'Ghi nhật ký lặp');
};
$tests['TC08: Túi rỗng, lệch số bài, lệch số tờ, thiếu dữ liệu đối chiếu'] = function () {
    [$db,$m]=fixture();
    foreach([3=>'không có bài thi',4=>'Số bài không khớp',5=>'Tổng số tờ',6=>'Thiếu biên bản',9=>'Số tờ giấy thi',10=>'thông tin đối chiếu'] as $id=>$reason) {
        rejected(fn()=>$m->createDraft($id,1),$reason);
    }
    check(total($db,'papers')===0,'Dữ liệu lỗi được lưu');
};
$tests['TC09: Một bài thuộc hai túi trong cùng kỳ/môn'] = function () {
    [$db,$m]=fixture();
    rejected(fn()=>$m->createDraft(7,1),'đã thuộc túi chấm khác');
    rejected(fn()=>$m->createDraft(8,1),'đã thuộc túi chấm khác');
};
$tests['TC10: Lỗi ghi nhật ký hoàn tác cả phách lẫn trạng thái'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1);
    $db->exec("CREATE TRIGGER fail_audit BEFORE INSERT ON audit_logs BEGIN SELECT RAISE(ABORT,'Simulated failure'); END");
    try {$m->save($d,1); throw new RuntimeException('Không gây được lỗi');} catch(PDOException $expected) {}
    check(total($db,'papers')===0 && total($db,'audit_logs')===0 && $m->bag(1)['status']==='pending','Không rollback');
    check($m->bag(1)['created_by']===null && $m->bag(1)['created_at']===null,'Lưu dở nhật ký người tạo');
};
$tests['Phân quyền quản lý và đối chiếu ở tầng Model'] = function () {
    [$db,$m]=fixture();
    check(password_verify('ThuKy@123',$m->user('thuky')['password_hash']),'Sai mật khẩu');
    foreach([0,2,3] as $userId) {
        rejected(fn()=>$m->handover(1,$userId),'không có quyền');
        rejected(fn()=>$m->createDraft(1,$userId),'không có quyền');
        rejected(fn()=>$m->save($m->createDraft(1,1),$userId),'không có quyền');
    }
    rejected(fn()=>$m->grading(1,0),'không có quyền');
};
$tests['Dữ liệu và HTML chấm thi không chứa thông tin nguồn'] = function () {
    [$db,$m]=fixture(); $m->save($m->createDraft(1,1),1);
    $numbers=$m->grading(1,3);
    check(array_keys($numbers[0])===['number','sheet_count'],'API lộ nguồn');
    $bag=$m->bag(1); $user=$m->authorizedUser(3); $context=['exam'=>1,'subject'=>1]; $bagId=1;
    ob_start(); require dirname(__DIR__).'/app/Views/UC19/grading.php'; $html=ob_get_clean();
    foreach(['Đăng xuất','BT001','260001','HS001','P.101','P.102','Nguyễn Minh Anh','candidate_number','handover_id','room_id'] as $secret) {
        check(!str_contains($html,$secret),'Lộ nguồn: '.$secret);
    }
};
$tests['Dữ liệu bàn giao thay đổi sau xem trước'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1);
    $db->exec("UPDATE grading_bags SET handover_ref='CHANGED' WHERE id=1");
    rejected(fn()=>$m->save($d,1),'đã thay đổi');
    rejected(fn()=>$m->regenerate($d,1,1),'đã thay đổi');
};
$tests['Số tờ từng bài thay đổi dù tổng số tờ giữ nguyên'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1);
    $db->exec('UPDATE handover_entries SET sheet_count=1 WHERE id=1');
    $db->exec('UPDATE handover_entries SET sheet_count=2 WHERE id=2');
    rejected(fn()=>$m->save($d,1),'đã thay đổi');
};
$tests['Bài bị gán trùng sau xem trước'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1);
    $db->exec('INSERT INTO handover_entries VALUES (17,2,1,1,1,2)');
    rejected(fn()=>$m->save($d,1),'đã thuộc túi chấm khác');
};
$tests['Phách bị sử dụng bởi túi khác trước lúc xác nhận'] = function () {
    [$db,$m]=fixture(); $a=$m->createDraft(1,1); $b=$m->createDraft(2,1);
    $b['codes'][4]=$a['codes'][1]; $m->save($a,1);
    rejected(fn()=>$m->save($b,1),'bị trùng');
    check(!$m->grading(2,1) && $m->bag(2)['status']==='pending','Lưu một phần');
};
$tests['Số phách có thể trùng ở môn khác; cùng kỳ/môn bị DB chặn'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1); $m->save($d,1);
    $other=$m->createDraft(11,1); $other['codes'][14]=$d['codes'][1]; $m->save($other,1);
    try {
        $stmt=$db->prepare('INSERT INTO papers VALUES (4,1,2,4,?,1)'); $stmt->execute([$d['codes'][1]]);
        throw new RuntimeException('DB chấp nhận phách trùng');
    } catch(PDOException $expected) {}
};
$tests['Sai phạm vi môn, vắng thi, thiếu SBD bị từ chối'] = function () {
    [$db,$m]=fixture(); $db->exec('INSERT INTO handover_entries VALUES (17,1,2,1,1,2)');
    rejected(fn()=>$m->createDraft(1,1),'không thuộc kỳ thi và môn thi');
    [$db,$m]=fixture(); $db->exec("UPDATE exam_candidates SET attendance='absent' WHERE scope_id=1 AND student_id=1");
    rejected(fn()=>$m->createDraft(1,1),'thông tin đối chiếu');
    [$db,$m]=fixture(); $db->exec('UPDATE exam_candidates SET candidate_number=NULL WHERE scope_id=1 AND student_id=1');
    rejected(fn()=>$m->createDraft(1,1),'thông tin đối chiếu');
};
$tests['Bản dự kiến bị sửa, thiếu bài hoặc sai định dạng'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1); unset($d['codes'][1]);
    rejected(fn()=>$m->save($d,1),'không còn hợp lệ');
    $d=$m->createDraft(1,1); $d['codes'][1]='abc';
    rejected(fn()=>$m->save($d,1),'không hợp lệ');
};
$tests['Thu hồi quyền sau khi đã tạo bản dự kiến'] = function () {
    [$db,$m]=fixture(); $d=$m->createDraft(1,1);
    $db->exec('UPDATE users SET can_view_mapping=0 WHERE id=1');
    rejected(fn()=>$m->save($d,1),'không có quyền');
    check(total($db,'papers')===0,'Lưu trái phép');
};
$failed=0;
foreach($tests as $name=>$test) {
    try {$test(); echo "PASS: $name\n";} catch(Throwable $e) {$failed++; fwrite(STDERR,"FAIL: $name: {$e->getMessage()}\n");}
}
echo count($tests)." tests, $failed failures\n";
exit($failed ? 1 : 0);
