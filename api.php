<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "smlaundry_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(["result" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");


function ensureRatesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS rates (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        service_type VARCHAR(100) NOT NULL,
        item_name VARCHAR(150) NOT NULL,
        rate DECIMAL(10,2) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_service_item (service_type, item_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($sql)) {
        return false;
    }

    // Seed only when the rate table is empty.
    $check = $conn->query("SELECT COUNT(*) AS c FROM rates");
    $count = $check ? (int)$check->fetch_assoc()['c'] : 0;

    if ($count === 0) {
        $defaults = [
            "Steam Ironing" => [
                "Saree"=>40, "Blouse"=>15, "Churidar set"=>45, "Jeans"=>25, "T Shirt"=>20,
                "Dress long"=>30, "Skirt"=>15, "Dhothi"=>20, "Shirt"=>20, "Pant"=>20, "Jubba"=>20,
                "Blanket"=>250, "Curtain (Small)"=>60, "Curtain (Long)"=>80, "Bedsheet"=>100,
                "Bedsheet King Size"=>120, "Pillow Cover"=>20
            ],
            "Laundry" => [
                "Pattusaree"=>250, "Saree"=>100, "Blouse"=>30, "Churidar Set"=>90, "Jeans"=>50,
                "T Shirt"=>40, "Dress long"=>50, "Skirt"=>30, "Dhothi"=>60, "Shirt"=>50, "Pant"=>60,
                "Jubba"=>60, "Baniyan"=>30, "Coat"=>250, "Blanket"=>250, "Curtain (Small)"=>60,
                "Curtain (Long)"=>80, "Bedsheet"=>100, "Bedsheet King Size"=>120, "Pillow Cover"=>20
            ]
        ];

        $stmt = $conn->prepare("INSERT INTO rates (service_type,item_name,rate) VALUES (?,?,?)");
        if ($stmt) {
            foreach ($defaults as $service => $items) {
                foreach ($items as $item => $rate) {
                    $rate=(float)$rate;
                    $stmt->bind_param("ssd",$service,$item,$rate);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }
    }
    return true;
}



function ensureCollectionTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS collection (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        order_id VARCHAR(50) NULL,
        phone VARCHAR(30) NOT NULL DEFAULT '',
        received_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        received_date DATE NOT NULL,
        instrument_type VARCHAR(20) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_collection_order (order_id),
        KEY idx_collection_received_date (received_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if (!$conn->query($sql)) return false;
    $orderCol = $conn->query("SHOW COLUMNS FROM collection LIKE 'order_id'");
    if ($orderCol && ($oc = $orderCol->fetch_assoc()) && strtoupper((string)$oc['Null']) === 'NO') { if (!$conn->query("ALTER TABLE collection MODIFY order_id VARCHAR(50) NULL")) return false; }
    $cols = $conn->query("SHOW COLUMNS FROM collection LIKE 'phone'");
    if ($cols && $cols->num_rows === 0 && !$conn->query("ALTER TABLE collection ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '' AFTER order_id")) return false;
    $indexes = $conn->query("SHOW INDEX FROM collection WHERE Key_name='uq_collection_order'");
    if ($indexes && $indexes->num_rows > 0 && !$conn->query("ALTER TABLE collection DROP INDEX uq_collection_order")) return false;
    return true;
}

function ensureReconciliationColumns($conn) {
    // Reconciliation data is stored only in collection; orders is read-only here.
    return ensureCollectionTable($conn);
}


$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listInstitutions') {
    $sql = "SELECT id, institution_name, mobile_number, status, created_at, updated_at
            FROM institutions
            WHERE status = 'Active'
            ORDER BY institution_name ASC";
    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode([
            "result" => "error",
            "message" => "Institution list failed: " . $conn->error
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode([
        "result" => "success",
        "records" => $records
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listRates') {
    if (!ensureRatesTable($conn)) {
        echo json_encode(["result"=>"error","message"=>"Rate table could not be created: ".$conn->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $service = trim((string)($_GET['service_type'] ?? ''));
    if ($service !== '') {
        $stmt = $conn->prepare("SELECT id, service_type, item_name, rate FROM rates WHERE service_type=? ORDER BY id ASC");
        $stmt->bind_param("s",$service);
    } else {
        $stmt = $conn->prepare("SELECT id, service_type, item_name, rate FROM rates ORDER BY service_type, id ASC");
    }

    if (!$stmt || !$stmt->execute()) {
        echo json_encode(["result"=>"error","message"=>$stmt ? $stmt->error : $conn->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result=$stmt->get_result();
    $records=[];
    while($row=$result->fetch_assoc()){
        $row['rate']=(float)$row['rate'];
        $records[]=$row;
    }
    $stmt->close();

    echo json_encode(["result"=>"success","records"=>$records], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'GET' && (($_GET['action'] ?? '') === 'institutionDailyItemReport')) {
    $institution = trim((string)($_GET['institution'] ?? ''));
    $start = trim((string)($_GET['start'] ?? ''));
    $end = trim((string)($_GET['end'] ?? ''));
    if ($institution === '' || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $start) || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $end) || $start > $end) {
        echo json_encode(["result"=>"error","message"=>"Invalid institution or date range"], JSON_UNESCAPED_UNICODE); exit;
    }
    $stmt = $conn->prepare("SELECT `date`, isoDate, name, items, details FROM orders WHERE name=? AND COALESCE(NULLIF(isoDate,''), STR_TO_DATE(`date`,'%d/%m/%Y')) BETWEEN ? AND ? ORDER BY COALESCE(NULLIF(isoDate,''), STR_TO_DATE(`date`,'%d/%m/%Y')) ASC");
    if (!$stmt) { echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE); exit; }
    $stmt->bind_param("sss", $institution, $start, $end);
    $stmt->execute(); $res=$stmt->get_result();
    $map=[]; $dates=[]; $totalQty=0; $grandTotal=0;
    $cursor=new DateTime($start); $last=new DateTime($end);
    while($cursor <= $last){$dates[]=$cursor->format('Y-m-d');$cursor->modify('+1 day');}
    while($o=$res->fetch_assoc()){
        $day=trim((string)($o['isoDate'] ?? ''));
        if(!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/',$day)){
            $dt=DateTime::createFromFormat('d/m/Y',(string)$o['date']); $day=$dt?$dt->format('Y-m-d'):'';
        }
        if($day==='') continue;
        $items=json_decode((string)$o['items'],true);
        if(!is_array($items)) $items=[];
        if(!$items){
            $parts=explode('|',(string)($o['details']??''));$first=trim($parts[0]);
            foreach(array_filter(array_map('trim',explode(',',$first))) as $part){
                if(preg_match('/^(.*?)\\s*\\((\\d+)\\)\\s*(?:@\\s*₹?\\s*([\\d.]+))?$/u',$part,$m))
                    $items[]=['name'=>trim($m[1]),'qty'=>(int)$m[2],'rate'=>(float)($m[3]??0)];
            }
        }
        foreach($items as $it){
            $name=trim((string)($it['name']??''));$qty=(float)($it['qty']??0);$rate=(float)($it['rate']??0);
            if($name===''||$qty<=0) continue;
            $amount=isset($it['amount'])?(float)$it['amount']:($qty*$rate);
            if(!isset($map[$name])) $map[$name]=['item'=>$name,'days'=>[],'total'=>0,'weightedRateAmount'=>0,'amount'=>0];
            $map[$name]['days'][$day]=($map[$name]['days'][$day]??0)+$qty;
            $map[$name]['total']+=$qty;$map[$name]['weightedRateAmount']+=$amount;$map[$name]['amount']+=$amount;
        }
    }
    $stmt->close();
    $rows=[];
    foreach($map as $r){$r['rate']=$r['total']>0?$r['weightedRateAmount']/$r['total']:0;unset($r['weightedRateAmount']);$totalQty+=$r['total'];$grandTotal+=$r['amount'];$rows[]=$r;}
    usort($rows,function($a,$b){return strcasecmp($a['item'],$b['item']);});
    echo json_encode(["result"=>"success","institution"=>$institution,"start"=>$start,"end"=>$end,"dates"=>$dates,"rows"=>$rows,"totalQty"=>$totalQty,"grandTotal"=>$grandTotal],JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'GET' && (($_GET['action'] ?? '') === 'dcbReport')) {
    if (!ensureCollectionTable($conn)) { echo json_encode(["result"=>"error","message"=>"Collection table could not be prepared"], JSON_UNESCAPED_UNICODE); exit; }
    $phone = preg_replace('/\D+/', '', (string)($_GET['phone'] ?? ''));
    if ($phone === '') { echo json_encode(["result"=>"error","message"=>"Valid phone number required"], JSON_UNESCAPED_UNICODE); exit; }
    $stmt = $conn->prepare("SELECT name, phone FROM orders WHERE REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','')=REPLACE(REPLACE(REPLACE(?,' ',''),'-',''),'+','') ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param('s', $phone); $stmt->execute(); $customer = $stmt->get_result()->fetch_assoc(); $stmt->close();
    if (!$customer) { echo json_encode(["result"=>"error","message"=>"Customer not found"], JSON_UNESCAPED_UNICODE); exit; }
    $stmt = $conn->prepare("SELECT id AS ref, COALESCE(NULLIF(isoDate,''), STR_TO_DATE(`date`,'%d/%m/%Y')) AS txn_date, 'Debit' AS txn_type, COALESCE(amount,0)+COALESCE(extraCharge,0) AS debit, 0 AS credit, CONCAT('Order ',id) AS particulars FROM orders WHERE REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','')=REPLACE(REPLACE(REPLACE(?,' ',''),'-',''),'+','')");
    $stmt->bind_param('s',$phone); $stmt->execute(); $res=$stmt->get_result(); $transactions=[]; while($r=$res->fetch_assoc()) $transactions[]=$r; $stmt->close();
    $stmt = $conn->prepare("SELECT CAST(id AS CHAR) AS ref, received_date AS txn_date, 'Credit' AS txn_type, 0 AS debit, received_amount AS credit, CONCAT('Payment - ',instrument_type) AS particulars FROM collection WHERE REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','')=REPLACE(REPLACE(REPLACE(?,' ',''),'-',''),'+','')");
    $stmt->bind_param('s',$phone); $stmt->execute(); $res=$stmt->get_result(); while($r=$res->fetch_assoc()) $transactions[]=$r; $stmt->close();
    usort($transactions, function($a,$b){ return strcmp((string)$a['txn_date'],(string)$b['txn_date']) ?: strcmp((string)$a['txn_type'],(string)$b['txn_type']); });
    $debit=array_sum(array_map(fn($r)=>(float)$r['debit'],$transactions)); $credit=array_sum(array_map(fn($r)=>(float)$r['credit'],$transactions)); $balance=$debit-$credit;
    echo json_encode(["result"=>"success","customer"=>$customer,"transactions"=>$transactions,"total_debit"=>$debit,"total_credit"=>$credit,"balance"=>$balance],JSON_UNESCAPED_UNICODE); exit;
}

if ($method === 'GET') {
    if (!ensureReconciliationColumns($conn)) { echo json_encode(["result"=>"error","message"=>"Reconciliation fields could not be prepared"], JSON_UNESCAPED_UNICODE); exit; }
    $sql = "SELECT * FROM orders ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $decodedItems = json_decode($row['items'], true);
        if (is_array($decodedItems) && count($decodedItems)) {
            $row['items'] = $decodedItems;
        } else {
            $row['items'] = [];
            $rawDetails = (string)($row['details'] ?? '');
            $itemPart = trim(explode('|', $rawDetails, 2)[0]);
            if ($itemPart !== '') {
                foreach (array_filter(array_map('trim', explode(',', $itemPart))) as $part) {
                    if (preg_match('/^(.*?)\s*\((\d+)\)\s*(?:@\s*₹?\s*([\d.]+))?$/u', $part, $m)) {
                        $row['items'][] = [
                            'name' => trim($m[1]),
                            'qty' => (int)$m[2],
                            'rate' => isset($m[3]) && $m[3] !== '' ? (float)$m[3] : 0
                        ];
                    }
                }
            }
        }
        $orders[] = $row;
    }
    if (ensureCollectionTable($conn)) {
        $payments = $conn->query("SELECT id, order_id, phone, received_amount, received_date, instrument_type, created_at FROM collection ORDER BY received_date DESC, id DESC");
        $byOrder = [];
        if ($payments) while ($payment = $payments->fetch_assoc()) $byOrder[(string)$payment['order_id']][] = $payment;
        foreach ($orders as &$orderRow) {
            $entries = $byOrder[(string)$orderRow['id']] ?? [];
            $orderRow['collection_entries'] = $entries;
            $orderRow['received_total'] = array_sum(array_map(static fn($entry) => (float)$entry['received_amount'], $entries));
            $orderRow['received_amount'] = $orderRow['received_total'];
            $orderRow['received_date'] = $entries[0]['received_date'] ?? null;
            $orderRow['instrument_type'] = $entries[0]['instrument_type'] ?? null;
        }
        unset($orderRow);
    }
    echo json_encode($orders);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["result" => "error", "message" => "Invalid JSON Data"]);
        exit;
    }

    $action = $data['action'] ?? '';
    // Rate settings: save one rate
    if ($action === 'saveRate') {
        if (!ensureRatesTable($conn)) {
            echo json_encode(["result"=>"error","message"=>"Rate table could not be created: ".$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $service=trim((string)($data['service_type'] ?? ''));
        $item=trim((string)($data['item_name'] ?? ''));
        $rate=(float)($data['rate'] ?? 0);

        if ($service==='' || $item==='') {
            echo json_encode(["result"=>"error","message"=>"Service type and item name are required"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt=$conn->prepare("
            INSERT INTO rates (service_type,item_name,rate)
            VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE rate=VALUES(rate), updated_at=CURRENT_TIMESTAMP
        ");
        if (!$stmt) {
            echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt->bind_param("ssd",$service,$item,$rate);

        if($stmt->execute()){
            echo json_encode(["result"=>"success","message"=>"Rate saved"], JSON_UNESCAPED_UNICODE);
        }else{
            echo json_encode(["result"=>"error","message"=>$stmt->error], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        exit;
    }

    // Rate settings: save all edited rates for a service
    if ($action === 'saveRates') {
        if (!ensureRatesTable($conn)) {
            echo json_encode(["result"=>"error","message"=>"Rate table could not be created: ".$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $rates=$data['rates'] ?? [];
        if(!is_array($rates)){
            echo json_encode(["result"=>"error","message"=>"Invalid rate data"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt=$conn->prepare("
            INSERT INTO rates (service_type,item_name,rate)
            VALUES (?,?,?)
            ON DUPLICATE KEY UPDATE rate=VALUES(rate), updated_at=CURRENT_TIMESTAMP
        ");
        if(!$stmt){
            echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $saved=0;
        foreach($rates as $r){
            $service=trim((string)($r['service_type'] ?? ''));
            $item=trim((string)($r['item_name'] ?? ''));
            $rate=(float)($r['rate'] ?? 0);
            if($service==='' || $item==='') continue;
            $stmt->bind_param("ssd",$service,$item,$rate);
            if(!$stmt->execute()){
                $err=$stmt->error;
                $stmt->close();
                echo json_encode(["result"=>"error","message"=>$err], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $saved++;
        }
        $stmt->close();

        echo json_encode(["result"=>"success","message"=>"Rates saved","saved"=>$saved], JSON_UNESCAPED_UNICODE);
        exit;
    }



    // Institution management
    if ($action === 'addInstitution' || $action === 'updateInstitution') {
        $id = isset($data['id']) && $data['id'] !== '' ? (int)$data['id'] : 0;
        $name = trim((string)($data['institution_name'] ?? ''));
        $mobile = trim((string)($data['mobile_number'] ?? ''));
        $status = trim((string)($data['status'] ?? 'Active'));

        if ($name === '' || $mobile === '') {
            echo json_encode([
                "result" => "error",
                "message" => "Institution name and mobile number are required"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($action === 'updateInstitution' && $id > 0) {
            $stmt = $conn->prepare("
                UPDATE institutions
                SET institution_name=?, mobile_number=?, status=?, updated_at=NOW()
                WHERE id=?
            ");
            if (!$stmt) {
                echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $stmt->bind_param("sssi", $name, $mobile, $status, $id);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO institutions (institution_name, mobile_number, status, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ");
            if (!$stmt) {
                echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $stmt->bind_param("sss", $name, $mobile, $status);
        }

        if ($stmt->execute()) {
            echo json_encode(["result"=>"success","message"=>"Institution saved"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["result"=>"error","message"=>$stmt->error], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        exit;
    }

    if ($action === 'deleteInstitution') {
        $id = (int)($data['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(["result"=>"error","message"=>"Institution ID missing"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Soft delete so old order/customer records are not affected.
        $stmt = $conn->prepare("UPDATE institutions SET status='Inactive', updated_at=NOW() WHERE id=?");
        if (!$stmt) {
            echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["result"=>"success","message"=>"Institution deleted"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["result"=>"error","message"=>$stmt->error], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        exit;
    }



    if ($action === 'addBulkCollection') {
        if (!ensureCollectionTable($conn)) { echo json_encode(["result"=>"error","message"=>"Collection table could not be prepared"], JSON_UNESCAPED_UNICODE); exit; }
        $phone=trim((string)($data['phone'] ?? '')); $amount=filter_var($data['received_amount'] ?? null,FILTER_VALIDATE_FLOAT); $date=trim((string)($data['received_date'] ?? '')); $mode=trim((string)($data['instrument_type'] ?? ''));
        $dt=DateTime::createFromFormat('Y-m-d',$date);
        if($phone==='' || $amount===false || $amount<=0 || !$dt || $dt->format('Y-m-d')!==$date || !in_array($mode,['Cash','UPI'],true)){echo json_encode(["result"=>"error","message"=>"ഫോൺ നമ്പർ, തുക, തീയതി, പേയ്മെന്റ് മോഡ് ശരിയായി നൽകുക"],JSON_UNESCAPED_UNICODE);exit;}
        $check=$conn->prepare("SELECT phone FROM orders WHERE REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','')=REPLACE(REPLACE(REPLACE(?,' ',''),'-',''),'+','') LIMIT 1");
        if(!$check){echo json_encode(["result"=>"error","message"=>$conn->error],JSON_UNESCAPED_UNICODE);exit;}
        $check->bind_param('s',$phone);$check->execute();$found=$check->get_result()->fetch_assoc();$check->close();
        if(!$found){echo json_encode(["result"=>"error","message"=>"ഈ ഫോൺ നമ്പറിൽ കസ്റ്റമറെ കണ്ടെത്തിയില്ല"],JSON_UNESCAPED_UNICODE);exit;}
        $orderId=null;
        $ins=$conn->prepare("INSERT INTO collection (order_id,phone,received_amount,received_date,instrument_type) VALUES (?,?,?,?,?)");
        if(!$ins){echo json_encode(["result"=>"error","message"=>$conn->error],JSON_UNESCAPED_UNICODE);exit;}
        $ins->bind_param('ssdss',$orderId,$phone,$amount,$date,$mode);
        if($ins->execute()) echo json_encode(["result"=>"success","message"=>"₹".number_format($amount,2)." കളക്ഷൻ രേഖപ്പെടുത്തി. പഴയ ഓർഡറുകളുടെ ബാലൻസ് മാറ്റിയിട്ടില്ല.","collection_id"=>$ins->insert_id],JSON_UNESCAPED_UNICODE);
        else echo json_encode(["result"=>"error","message"=>$ins->error],JSON_UNESCAPED_UNICODE);
        $ins->close(); exit;
    }

    if ($action === 'updateReconciliation' || $action === 'addReconciliation') {
        if (!ensureCollectionTable($conn)) { echo json_encode(["result"=>"error","message"=>"Collection table could not be prepared"], JSON_UNESCAPED_UNICODE); exit; }
        $id=trim((string)($data['id'] ?? ''));
        $amount=filter_var($data['received_amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $date=trim((string)($data['received_date'] ?? ''));
        $instrument=trim((string)($data['instrument_type'] ?? ''));
        $dateObj=DateTime::createFromFormat('Y-m-d',$date);
        if($id==='' || $amount===false || $amount<=0 || !$dateObj || $dateObj->format('Y-m-d')!==$date || !in_array($instrument,['Cash','UPI'],true)) {
            echo json_encode(["result"=>"error","message"=>"Invalid reconciliation details"], JSON_UNESCAPED_UNICODE); exit;
        }
        $order=$conn->prepare("SELECT phone FROM orders WHERE id=? AND status IN ('Completed','Delivered','Part Bill') AND COALESCE(collection,0)>0");
        if(!$order){echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);exit;}
        $order->bind_param("s",$id); $order->execute(); $orderRow=$order->get_result()->fetch_assoc(); $order->close();
        if(!$orderRow){echo json_encode(["result"=>"error","message"=>"Delivered order not found"], JSON_UNESCAPED_UNICODE);exit;}
        $phone=trim((string)($data['phone'] ?? $orderRow['phone'] ?? ''));
        $save=$conn->prepare("INSERT INTO collection (order_id, phone, received_amount, received_date, instrument_type) VALUES (?,?,?,?,?)");
        if(!$save){echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);exit;}
        $save->bind_param("ssdss",$id,$phone,$amount,$date,$instrument);
        if($save->execute()) echo json_encode(["result"=>"success","message"=>"New collection entry saved","collection_id"=>$save->insert_id], JSON_UNESCAPED_UNICODE);
        else echo json_encode(["result"=>"error","message"=>$save->error], JSON_UNESCAPED_UNICODE);
        $save->close(); exit;
    }

    // 1. Delete Order Action (പുതിയതായി ചേർത്തത്)
    if ($action === 'deleteOrder') {
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("s", $data['id']);
        if ($stmt->execute()) {
            echo json_encode(["result" => "success", "message" => "Order Deleted"]);
        } else {
            echo json_encode(["result" => "error", "message" => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    // 2. Status Update Action
    if ($action === 'updateStatus') {
        $requestedStatus = trim((string)($data['status'] ?? ''));
        $id = trim((string)($data['id'] ?? ''));
        if ($id === '' || !in_array($requestedStatus, ['Pending', 'Completed'], true)) {
            echo json_encode(["result" => "error", "message" => "Invalid status update"], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Delivered orders are final and cannot be changed back to Pending.
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND status <> 'Delivered'");
        $stmt->bind_param("ss", $requestedStatus, $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(["result" => "success", "message" => "Status Updated"]);
        } else {
            $check = $conn->prepare("SELECT status FROM orders WHERE id=?");
            $check->bind_param("s", $id); $check->execute();
            $row = $check->get_result()->fetch_assoc(); $check->close();
            $message = ($row && $row['status'] === 'Delivered') ? "Delivered ഓർഡറിന്റെ സ്റ്റാറ്റസ് മാറ്റാൻ കഴിയില്ല" : "Order not found or status unchanged";
            echo json_encode(["result" => "error", "message" => $message], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        exit;
    }

    // 3. Delivery Save Action
    // delivery=true  -> collection = amount + extraCharge
    // delivery=false -> collection = 0
    if ($action === 'updateDelivery') {
        $id = (string)($data['id'] ?? '');
        $delivered = filter_var($data['delivered'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($id === '') {
            echo json_encode(["result"=>"error","message"=>"Order ID missing"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($delivered) {
            $stmt = $conn->prepare("
                UPDATE orders
                SET collection = COALESCE(amount,0) + COALESCE(extraCharge,0),
                    status = 'Delivered'
                WHERE id = ?
            ");
        } else {
            $stmt = $conn->prepare("
                UPDATE orders
                SET collection = 0
                WHERE id = ?
            ");
        }

        if (!$stmt) {
            echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            echo json_encode([
                "result"=>"success",
                "message"=>$delivered ? "Delivery saved" : "Delivery unchecked",
                "id"=>$id,
                "delivered"=>$delivered
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["result"=>"error","message"=>$stmt->error], JSON_UNESCAPED_UNICODE);
        }

        $stmt->close();
        exit;
    }

    // Delivery screen uses a batch payload:
    // { action: 'updateDeliveryCollection', updates: [{id:'...', delivered:true/false}] }
    // checked   -> collection = amount + extraCharge
    // unchecked -> collection = 0
    if ($action === 'updateDeliveryCollection') {
        $updates = $data['updates'] ?? [];

        if (!is_array($updates)) {
            echo json_encode([
                "result" => "error",
                "message" => "Invalid delivery update data"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE orders
            SET collection = CASE
                WHEN ? = 1 THEN ROUND(COALESCE(amount,0) + COALESCE(extraCharge,0), 2)
                ELSE 0
            END,
            status = CASE WHEN ? = 1 THEN 'Delivered' ELSE status END
            WHERE id = ? AND status IN ('Completed','Delivered')
        ");

        if (!$stmt) {
            echo json_encode([
                "result" => "error",
                "message" => "Delivery update prepare failed: ".$conn->error
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $updated = 0;

        foreach ($updates as $item) {
            $id = trim((string)($item['id'] ?? ''));
            $delivered = !empty($item['delivered']) ? 1 : 0;

            if ($id === '') {
                continue;
            }

            $stmt->bind_param("iis", $delivered, $delivered, $id);

            if (!$stmt->execute()) {
                $error = $stmt->error;
                $stmt->close();
                echo json_encode([
                    "result" => "error",
                    "message" => "Delivery update failed: ".$error
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            $updated += $stmt->affected_rows;
        }

        $stmt->close();

        echo json_encode([
            "result" => "success",
            "message" => "Delivery വിവരങ്ങൾ വിജയകരമായി സേവ് ചെയ്തു",
            "updated" => $updated
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Compatibility: direct collection update
    if ($action === 'updateCollection') {
        $id = (string)($data['id'] ?? '');
        $collection = (float)($data['collection'] ?? 0);

        if ($id === '') {
            echo json_encode(["result"=>"error","message"=>"Order ID missing"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $conn->prepare("UPDATE orders SET collection=? WHERE id=?");
        if (!$stmt) {
            echo json_encode(["result"=>"error","message"=>$conn->error], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt->bind_param("ds", $collection, $id);
        if ($stmt->execute()) {
            echo json_encode(["result"=>"success","message"=>"Collection updated"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["result"=>"error","message"=>$stmt->error], JSON_UNESCAPED_UNICODE);
        }
        $stmt->close();
        exit;
    }

    // 3. Save or Edit Order
    $id = $data['id'];
    $date = $data['date'];
    $isoDate = $data['isoDate'];
    $time = $data['time'];
    $name = $data['name'];
    $phone = $data['phone'];
    $count = (int)$data['count'];
    $service = $data['service'];
    $items = json_encode($data['items'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $extraCharge = (float)($data['extraCharge'] ?? 0);
    // Canonical order-detail text is stored in the single `details` column.
    $details = $data['details'] ?? $data['detail'] ?? '';
    $amount = (float)$data['amount'];
    $status = $data['status'] ?? 'Pending';

    // Check if ID exists
    $checkStmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
    $checkStmt->bind_param("s", $id);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    $checkStmt->close();

    if ($res->num_rows > 0) {
        // UPDATE Existing Row
        $stmt = $conn->prepare("UPDATE orders SET date=?, isoDate=?, time=?, name=?, phone=?, count=?, service=?, items=?, extraCharge=?, details=?, amount=?, status=? WHERE id=?");
        $stmt->bind_param("sssssissdsdss", $date, $isoDate, $time, $name, $phone, $count, $service, $items, $extraCharge, $details, $amount, $status, $id);
    } else {
        // INSERT New Row
        $stmt = $conn->prepare("INSERT INTO orders (id, date, isoDate, time, name, phone, count, service, items, extraCharge, details, amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssissdsds", $id, $date, $isoDate, $time, $name, $phone, $count, $service, $items, $extraCharge, $details, $amount, $status);
    }

    if ($stmt->execute()) {
        echo json_encode(["result" => "success"]);
    } else {
        echo json_encode(["result" => "error", "message" => $stmt->error]);
    }
    $stmt->close();
    exit;
}

$conn->close();
?>