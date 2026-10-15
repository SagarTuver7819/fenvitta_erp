<?php
/*
 * DEMO / TESTING DATA
 * Run from command line:   C:\xampp\php\php.exe database\seed_demo.php
 *
 * It clears all transactions (purchase, sales, production, dispatch, payments, stock)
 * and loads sample data for FY 2026-27 (April to October). Masters (parties / items / users) are kept.
 */
if (PHP_SAPI !== 'cli') die('Run from command line only.');
require_once __DIR__ . '/../includes/functions.php';

mt_srand(2627);

// ---------- Clear old transactions ----------
foreach (['purchase_items', 'purchases', 'sale_items', 'sales', 'payments', 'stock_ledger', 'rm_utilities',
             'batch_rm', 'batch_data', 'batch_outputs', 'production_batches', 'dispatch_items', 'dispatches', 'packing_labels'] as $t) {
    q("TRUNCATE TABLE `$t`");
}

$item = fn($code) => (int)val('SELECT id FROM items WHERE code=?', [$code]);
$party = fn($name) => (int)val('SELECT id FROM parties WHERE name=?', [$name]);

$PYR = $item('RM001'); $PYR_R = $item('RM002'); $CSA = $item('RM003'); $SUC = $item('RM004');
$SODA = $item('RM005'); $CHL = $item('RM006'); $CHL_R = $item('RM007'); $ALC = $item('RM008');
$DRUM = $item('PM001'); $FIBRE = $item('PM002'); $STICK = $item('PM003');
$SLF = $item('FG001'); $VPF = $item('FG002');
$TRANSPORT = $item('SV001'); $ANALYSIS = $item('SV002');

// ---------- Opening stock (01-04-2026, from Excel "ALTITUDE STOCK UPDATE") ----------
$opening = [$PYR => [1000, 338.5], $PYR_R => [1031, 301], $CSA => [1830, 73], $SUC => [375, 59], $SODA => [2000, 32],
    $CHL => [5100, 14], $ALC => [1000, 94], $DRUM => [120, 290], $FIBRE => [80, 210], $STICK => [1500, 2]];
foreach ($opening as $id => [$qtyVal, $rate]) {
    update('items', ['opening_qty' => $qtyVal, 'opening_rate' => $rate, 'opening_date' => '2026-04-01'], $id);
    ledger_add('2026-04-01', $id, $qtyVal, 0, 'OPENING', $id, $rate, null, 'Opening Stock');
}

// ---------- Purchases ----------
function seed_purchase($date, $poNo, $inv, $partyId, $category, $terms, $lines, $paidPart = 1.0, $payDate = null)
{
    $sub = 0; $gst = 0;
    foreach ($lines as &$l) {
        $l['amount'] = round($l[1] * $l[2], 2);
        $l['gst_amount'] = round($l['amount'] * $l[3] / 100, 2);
        $sub += $l['amount']; $gst += $l['gst_amount'];
    }
    unset($l);
    $id = insert('purchases', [
        'po_no' => $poNo, 'invoice_no' => $inv, 'purchase_date' => $date, 'party_id' => $partyId, 'category' => $category,
        'payment_terms' => $terms, 'due_date' => date('Y-m-d', strtotime("$date +$terms days")), 'subtotal' => $sub, 'gst_amount' => $gst,
        'freight' => 0, 'other_charges' => 0, 'total' => round($sub + $gst, 2), 'delivery_at' => 'Panoli 394116',
        'transport' => 'By Private Transport', 'freight_terms' => 'Paid', 'created_by' => 1,
    ]);
    foreach ($lines as $l) {
        insert('purchase_items', ['purchase_id' => $id, 'item_id' => $l[0], 'description' => '', 'qty' => $l[1], 'unit' => $l[4] ?? 'kg',
            'rate' => $l[2], 'amount' => $l['amount'], 'gst_rate' => $l[3], 'gst_amount' => $l['gst_amount'], 'total' => $l['amount'] + $l['gst_amount']]);
        if (val('SELECT item_type FROM items WHERE id=?', [$l[0]]) !== 'SERVICE') {
            ledger_add($date, $l[0], $l[1], 0, 'PURCHASE', $id, $l[2], null, "Purchase $inv");
        }
    }
    if ($paidPart > 0) {
        $total = round($sub + $gst, 2);
        insert('payments', ['pay_type' => 'PAYMENT', 'ref_id' => $id, 'pay_date' => $payDate ?: date('Y-m-d', strtotime("$date +$terms days")),
            'amount' => round($total * $paidPart, 2), 'tds' => 0, 'mode' => 'Bank', 'ref_no' => 'NEFT' . mt_rand(100000, 999999), 'created_by' => 1]);
    }
}

$nirva = $party('Nirva Speciality Chemicals'); $true = $party('True Chem Enterprise'); $aceto = $party('Aceto Chem Agencies');
$dhan = $party('Dhanraj Sugars Pvt Ltd'); $kavya = $party('Kavya Chemical'); $dev = $party('Dev Polymers & Containers');
$pramukh = $party('Pramukh Chemicals'); $rudra = $party('Rudra Chemicals'); $bhavna = $party('Bhavna Roadways'); $accu = $party('Accuprec Research Labs');

seed_purchase('2026-04-04', 'FNV/26-27/001', '26-27/10', $kavya, 'Raw Material', 60, [[$CHL, 3300, 14, 18]], 1, '2026-06-03');
seed_purchase('2026-04-08', 'FNV/26-27/002', '2627/DSPL/D-93', $dhan, 'Raw Material', 0, [[$SUC, 700, 59, 5]], 1, '2026-04-08');
seed_purchase('2026-04-09', 'FNV/26-27/003', '260024', $true, 'Raw Material', 60, [[$SODA, 2000, 32, 18], [$ALC, 2000, 94, 18]], 1, '2026-06-08');
seed_purchase('2026-04-10', 'FNV/26-27/004', 'J0078', $aceto, 'Raw Material', 0, [[$PYR, 2000, 338.5, 18]], 1, '2026-04-10');
seed_purchase('2026-04-15', '', '9644461', $bhavna, 'Transport', 0, [[$TRANSPORT, 1, 3648, 0, 'Nos']], 1, '2026-04-18');
seed_purchase('2026-05-02', 'FNV/26-27/006', '260051', $true, 'Raw Material', 60, [[$ALC, 3000, 91, 18], [$SODA, 3000, 32, 18]], 1, '2026-07-01');
seed_purchase('2026-05-11', 'FNV/26-27/007', 'DPC/112', $dev, 'Packing Material', 45, [[$DRUM, 300, 320, 18, 'Nos']], 1, '2026-06-25');
seed_purchase('2026-05-20', 'FNV/26-27/008', 'D-141', $dhan, 'Raw Material', 0, [[$SUC, 1100, 63, 5]], 1, '2026-05-20');
seed_purchase('2026-05-27', '', 'ARL/26-27/0223', $accu, 'Analysis', 15, [[$ANALYSIS, 1, 15000, 18, 'Nos']], 1, '2026-06-11');
seed_purchase('2026-06-03', 'FNV/26-27/011', 'NSC/44/2627', $nirva, 'Raw Material', 60, [[$CSA, 2000, 81, 18]], 1, '2026-08-02');
seed_purchase('2026-06-18', 'FNV/26-27/013', 'NSC/71/2627', $nirva, 'Raw Material', 60, [[$CSA, 2100, 81, 18], [$ALC, 2000, 94, 18]], 1, '2026-08-17');
seed_purchase('2026-06-30', 'FNV/26-27/014', 'J0131', $aceto, 'Raw Material', 0, [[$PYR, 1000, 308, 18]], 1, '2026-06-30');
seed_purchase('2026-07-14', 'FNV/26-27/016', '260177', $true, 'Raw Material', 60, [[$SODA, 4000, 31.5, 18]], 1, '2026-09-12');
seed_purchase('2026-07-22', 'FNV/26-27/018', 'DPC/188', $dev, 'Packing Material', 45, [[$DRUM, 350, 290, 18, 'Nos']], 0.5, '2026-09-05');
seed_purchase('2026-08-05', 'FNV/26-27/020', 'NSC/102/2627', $nirva, 'Raw Material', 90, [[$CSA, 2000, 88, 18], [$SODA, 2000, 32.5, 18]], 0);
seed_purchase('2026-08-19', 'FNV/26-27/022', 'D-266', $dhan, 'Raw Material', 60, [[$SUC, 1500, 69, 5]], 1, '2026-10-10');
seed_purchase('2026-09-02', 'FNV/26-27/024', 'NSC/131/2627', $nirva, 'Raw Material', 90, [[$PYR, 2000, 320, 18]], 0);
seed_purchase('2026-09-14', 'FNV/26-27/025', 'D-301', $dhan, 'Raw Material', 60, [[$SUC, 1000, 75, 5]], 0);
seed_purchase('2026-09-25', 'FNV/26-27/027', 'RC/0091', $rudra, 'Raw Material', 60, [[$ALC, 3000, 95, 18]], 0);
seed_purchase('2026-10-06', 'FNV/26-27/028', 'PC/2627/044', $pramukh, 'Raw Material', 60, [[$ALC, 2000, 86.5, 18], [$CHL, 2000, 14, 18]], 0);

// ---------- Production batches (Sucralfate - Altitude job work) ----------
$plant = PLANTS[0];
$rmPerBatch = [$PYR => 380, $PYR_R => 120, $CSA => 640, $SUC => 330, $SODA => 980, $CHL => 560, $ALC => 470];
$batchPlan = [
    // batch no, start, output date, output qty, status
    ['FV26SLF001', '2026-04-12', '2026-04-24', 982, 'Completed'],
    ['FV26SLF002', '2026-05-05', '2026-05-16', 975, 'Completed'],
    ['FV26SLF003', '2026-05-22', '2026-06-02', 988, 'Completed'],
    ['FV26SLF004', '2026-06-20', '2026-07-01', 969, 'Completed'],
    ['FV26SLF005', '2026-07-16', '2026-07-28', 990, 'Completed'],
    ['FV26SLF006', '2026-08-21', '2026-09-01', 978, 'Completed'],
    ['FV26SLF007', '2026-09-16', '2026-09-27', 985, 'Completed'],
    ['FV26SLF008', '2026-10-08', null, 0, 'In Process'],
];
$stages = [
    ['Raw Material Charging', 'Charged Pyridine & CSA in reactor R-101', 'Temperature (°C)', '5-10'],
    ['Reaction', 'Sulphation reaction with sucrose', 'Temperature (°C)', '62'],
    ['Neutralisation', 'Neutralised with Soda Ash solution', 'pH', '7.2'],
    ['Distillation / Solvent Recovery', 'Pyridine recovery under vacuum', 'Vacuum (mmHg)', '680'],
    ['Centrifuge', 'Wet cake centrifuged & washed', 'Weight (kg)', '1460'],
    ['Drying', 'Dried in FBD', 'LOD (%)', '3.8'],
    ['Milling / Sifting', 'Milled & sifted through 80 mesh', 'Weight (kg)', ''],
    ['QC Sampling', 'Sample sent to QC', 'Assay (%)', '99.1'],
];
$slfBatchIds = [];
foreach ($batchPlan as [$no, $start, $outDate, $outQty, $status]) {
    $mfg = $outDate ?: null;
    $bid = insert('production_batches', [
        'batch_no' => $no, 'item_id' => $SLF, 'plant' => $plant, 'batch_size' => 1000, 'start_date' => $start, 'end_date' => $outDate,
        'mfg_date' => $mfg, 'exp_date' => $mfg ? date('Y-m-d', strtotime("$mfg +60 months -1 day")) : null, 'status' => $status, 'created_by' => 1,
    ]);
    foreach ($rmPerBatch as $rm => $q) {
        $q = round($q * (0.97 + mt_rand(0, 60) / 1000), 0);
        $rate = item_avg_rate($rm);
        $rid = insert('batch_rm', ['batch_id' => $bid, 'issue_date' => $start, 'item_id' => $rm, 'qty' => $q, 'rate' => $rate]);
        ledger_add($start, $rm, 0, $q, 'BATCH_RM', $rid, $rate, null, "Issued to batch $no");
    }
    foreach ($stages as $i => [$stage, $act, $param, $value]) {
        if ($status !== 'Completed' && $i > 2) break;
        $day = date('Y-m-d', strtotime("$start +" . min($i * 2, 10) . ' days'));
        if ($param === 'Weight (kg)' && $value === '') $value = (string)$outQty;
        insert('batch_data', ['batch_id' => $bid, 'entry_date' => $day, 'stage' => $stage, 'activity' => $act,
            'start_time' => sprintf('%02d:00', 9 + $i % 4), 'end_time' => sprintf('%02d:30', 12 + $i % 5), 'parameter' => $param,
            'observed_value' => $value, 'done_by' => ['Druvin G.', 'Mayur R.', 'Altitude Shift-A'][$i % 3]]);
    }
    if ($outQty > 0) {
        $oid = insert('batch_outputs', ['batch_id' => $bid, 'output_date' => $outDate, 'qty' => $outQty, 'grade' => 'IP/USP',
            'containers' => (int)ceil($outQty / 25), 'pack_type' => 'HDPE Drum', 'qc_status' => 'Approved']);
        ledger_add($outDate, $SLF, $outQty, 0, 'BATCH_OUTPUT', $oid, 0, $bid, "Output of batch $no");
        insert('packing_labels', ['batch_id' => $bid, 'label_count' => (int)ceil($outQty / 25), 'start_no' => 1, 'net_wt' => 25, 'tare_wt' => 1.5,
            'pack_type' => 'HDPE Drum', 'storage' => setting('label_storage'), 'created_by' => 1]);
    }
    $slfBatchIds[] = $bid;
}

// One Vonoprazan trial batch (QC pending)
$vb = insert('production_batches', ['batch_no' => 'FV26VPF001', 'item_id' => $VPF, 'plant' => 'Fenvitta - Kothariya, Rajkot', 'batch_size' => 50,
    'start_date' => '2026-09-20', 'end_date' => '2026-10-03', 'mfg_date' => '2026-10-03', 'exp_date' => '2029-10-02', 'status' => 'Completed',
    'remarks' => 'Trial batch for customer sample', 'created_by' => 1]);
insert('batch_outputs', ['batch_id' => $vb, 'output_date' => '2026-10-03', 'qty' => 42.5, 'grade' => 'IH', 'containers' => 2, 'pack_type' => 'Fibre Drum', 'qc_status' => 'Pending', 'remarks' => 'Awaiting COA']);

// ---------- RM utilities (recovery / consumption) ----------
$util = [
    ['2026-04-26', $PYR_R, 'Solvent Recovery', 310, 'Solvent Recovery Plant', 'FV26SLF001'],
    ['2026-05-18', $CHL_R, 'Solvent Recovery', 420, 'Solvent Recovery Plant', 'FV26SLF002'],
    ['2026-06-04', $PYR_R, 'Solvent Recovery', 295, 'Solvent Recovery Plant', 'FV26SLF003'],
    ['2026-07-03', $PYR_R, 'Solvent Recovery', 305, 'Solvent Recovery Plant', 'FV26SLF004'],
    ['2026-07-10', $SODA, 'Utility Consumption', 150, 'Effluent Treatment', ''],
    ['2026-08-02', $SUC, 'Lab / QC Use', 5, 'Lab / QC', ''],
    ['2026-08-30', $PYR_R, 'Solvent Recovery', 300, 'Solvent Recovery Plant', 'FV26SLF006'],
    ['2026-09-12', $STICK, 'Utility Consumption', 400, 'Packing', ''],
    ['2026-09-29', $CHL_R, 'Solvent Recovery', 380, 'Solvent Recovery Plant', 'FV26SLF007'],
    ['2026-10-01', $DRUM, 'Wastage / Loss', 6, 'Warehouse', 'Damaged drums'],
];
$utilIn = ['Solvent Recovery', 'Return from Plant', 'Adjustment (+)'];
foreach ($util as [$d, $it, $type, $q, $area, $ref]) {
    $rate = item_avg_rate($it);
    $uid = insert('rm_utilities', ['entry_date' => $d, 'item_id' => $it, 'entry_type' => $type, 'qty' => $q, 'rate' => $rate, 'area' => $area, 'ref_no' => $ref, 'created_by' => 1]);
    $in = in_array($type, $utilIn);
    ledger_add($d, $it, $in ? $q : 0, $in ? 0 : $q, 'RM_UTILITY', $uid, $rate, null, "$type $area");
}

// ---------- Sales + dispatch (from Excel DISPATCH SHEET style) ----------
$sales = [
    // date, customer, location, qty, rate, terms, paid part, dispatch?
    ['2026-04-28', 'JRS Oryx Pharma', 'Karnal', 600, 930, 30, 1, true],
    ['2026-04-30', 'Farma Fusion', 'Mumbai', 300, 950, 20, 1, true],
    ['2026-05-06', 'RX Pharmachem', 'Ahmedabad', 50, 925, 30, 1, true],
    ['2026-05-19', 'Farma Fusion', 'Mumbai', 300, 925, 20, 1, true],
    ['2026-05-25', 'Sahika Pharma', 'Sirmour', 100, 910, 60, 1, true],
    ['2026-06-05', 'JRS Oryx Pharma', 'Karnal', 500, 930, 30, 1, true],
    ['2026-06-12', 'Zee Laboratories', 'Ahmedabad', 300, 920, 30, 1, true],
    ['2026-06-24', 'Believe Pharma Pvt Ltd', 'Surat', 600, 850, 15, 1, true],
    ['2026-07-04', 'Farma Fusion', 'Mumbai', 400, 925, 20, 1, true],
    ['2026-07-18', 'Ariheet Pharmachem', 'Ahmedabad', 500, 915, 30, 1, true],
    ['2026-07-30', 'Walter Healthcare', 'Baddi', 50, 940, 30, 1, true],
    ['2026-08-07', 'Believe Pharma Pvt Ltd', 'Surat', 850, 850, 15, 0.6, true],
    ['2026-08-21', 'Jain Pharma', 'Ahmedabad', 100, 900, 90, 0, true],
    ['2026-09-03', 'Farma Fusion', 'Mumbai', 350, 925, 20, 0.95, true],
    ['2026-09-28', 'Aurachem Life Sciences', 'Vapi', 700, 920, 100, 0, true],
    ['2026-09-30', 'HL Healthcare', 'Hyderabad', 300, 925, 39, 0, true],
    ['2026-10-02', 'Sahika Pharma', 'Sirmour', 150, 945, 60, 0, true],
    ['2026-10-05', 'JRS Oryx Pharma', 'Karnal', 400, 935, 30, 0, true],
    ['2026-10-09', 'Mednova Pharma', 'Vadodara', 25, 960, 30, 0, false],
    ['2026-10-13', 'Regal Laboratories', 'Rajkot', 200, 930, 30, 0, false],
];
$transporters = ['VRL Logistics', 'Bhavna Roadways', 'Shree Maruti Courier', 'Gati Ltd'];
$n = 0;
foreach ($sales as [$date, $cust, $loc, $q, $rate, $terms, $paidPart, $doDispatch]) {
    $n++;
    $pid = $party($cust);
    $amount = $q * $rate;
    $gst = round($amount * 0.18, 2);
    $invoice = "FNV/$n/26-27";
    $sid = insert('sales', ['invoice_no' => $invoice, 'sale_date' => $date, 'party_id' => $pid, 'location' => $loc, 'agent_name' => 'NA',
        'payment_terms' => $terms, 'due_date' => date('Y-m-d', strtotime("$date +$terms days")), 'subtotal' => $amount, 'freight' => 0,
        'gst_amount' => $gst, 'total' => round($amount + $gst), 'created_by' => 1]);
    insert('sale_items', ['sale_id' => $sid, 'item_id' => $SLF, 'grade' => $n % 3 ? 'IP/USP' : 'USP', 'qty' => $q, 'unit' => 'kg', 'rate' => $rate,
        'amount' => $amount, 'gst_rate' => 18, 'gst_amount' => $gst, 'total' => $amount + $gst]);

    if ($paidPart > 0) {
        $total = round($amount + $gst);
        insert('payments', ['pay_type' => 'RECEIPT', 'ref_id' => $sid, 'pay_date' => min(today(), date('Y-m-d', strtotime("$date +" . max($terms - 3, 2) . ' days'))),
            'amount' => round($total * $paidPart), 'tds' => 0, 'mode' => $terms >= 60 ? 'PDC' : 'Bank', 'ref_no' => 'UTR' . mt_rand(1000000, 9999999), 'created_by' => 1]);
    }

    if (!$doDispatch) continue;
    $dDate = date('Y-m-d', strtotime("$date +1 day"));
    $did = insert('dispatches', ['dispatch_no' => 'DSP/26-27/' . str_pad((string)$n, 3, '0', STR_PAD_LEFT), 'dispatch_date' => $dDate, 'sale_id' => $sid,
        'party_id' => $pid, 'invoice_no' => $invoice, 'destination' => $loc, 'transporter' => $transporters[$n % 4],
        'vehicle_no' => 'GJ03' . chr(65 + $n % 26) . chr(66 + $n % 20) . mt_rand(1000, 9999), 'lr_no' => 'LR' . mt_rand(10000, 99999),
        'driver_name' => ['Ramesh', 'Suresh', 'Bharat', 'Kishor'][$n % 4], 'driver_mobile' => '98' . mt_rand(10000000, 99999999),
        'qr_token' => bin2hex(random_bytes(8)), 'created_by' => 1]);

    // FIFO from SLF batches having stock on dispatch date
    $left = $q;
    foreach ($slfBatchIds as $bid) {
        if ($left <= 0) break;
        $out = row('SELECT output_date FROM batch_outputs WHERE batch_id=?', [$bid]);
        if (!$out || $out['output_date'] > $dDate) continue;
        $bal = batch_balance($bid);
        if ($bal <= 0) continue;
        $take = min($left, $bal);
        insert('dispatch_items', ['dispatch_id' => $did, 'item_id' => $SLF, 'batch_id' => $bid, 'grade' => $n % 3 ? 'IP/USP' : 'USP',
            'qty' => $take, 'packs' => (int)ceil($take / 25), 'pack_type' => 'HDPE Drum']);
        $bn = val('SELECT batch_no FROM production_batches WHERE id=?', [$bid]);
        ledger_add($dDate, $SLF, 0, $take, 'DISPATCH', $did, 0, $bid, "Dispatch to $cust");
        $left -= $take;
    }
    if ($left > 0) echo "Warning: short stock for $invoice ($left kg)\n";
}

echo "Demo data loaded:\n";
foreach (['purchases', 'sales', 'dispatches', 'production_batches', 'payments', 'rm_utilities', 'stock_ledger'] as $t) {
    echo str_pad($t, 22) . val("SELECT COUNT(*) FROM `$t`") . "\n";
}
echo 'SLF FG stock: ' . stock_qty($SLF) . " kg\n";
