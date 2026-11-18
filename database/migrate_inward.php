<?php
/*
 * One-time migration: Inward Entry against PO (supports Partial Inward).
 * Run:  C:\xampp\php\php.exe database\migrate_inward.php
 *
 * 1) Creates inward_entries + inward_items tables
 * 2) Converts old PURCHASE stock ledger rows into full Inward entries
 *    so existing stock balances stay the same
 */
if (PHP_SAPI !== 'cli') die('Run from command line only.');
require_once __DIR__ . '/../includes/functions.php';

echo "Creating inward tables...\n";
q("CREATE TABLE IF NOT EXISTS inward_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  inward_no VARCHAR(40) NOT NULL,
  inward_date DATE NOT NULL,
  purchase_id INT NOT NULL,
  party_id INT NOT NULL,
  invoice_no VARCHAR(60) NULL,
  vehicle_no VARCHAR(60) NULL,
  remarks TEXT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (inward_date), INDEX (purchase_id), INDEX (party_id)
) ENGINE=InnoDB");

q("CREATE TABLE IF NOT EXISTS inward_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  inward_id INT NOT NULL,
  purchase_item_id INT NOT NULL,
  item_id INT NOT NULL,
  qty DECIMAL(14,3) NOT NULL DEFAULT 0,
  unit VARCHAR(15) NULL,
  rate DECIMAL(14,2) NOT NULL DEFAULT 0,
  INDEX (inward_id), INDEX (purchase_item_id), INDEX (item_id)
) ENGINE=InnoDB");

q("INSERT INTO settings (skey, svalue) VALUES ('inward_prefix', 'GRN')
   ON DUPLICATE KEY UPDATE skey = skey");

$existing = (int)val('SELECT COUNT(*) FROM inward_entries');
if ($existing > 0) {
    echo "Inward entries already exist ($existing). Skipping data conversion.\nDone.\n";
    exit;
}

$purchases = rows("SELECT DISTINCT ref_id AS id FROM stock_ledger WHERE ref_type='PURCHASE' ORDER BY ref_id");
echo 'Converting ' . count($purchases) . " old PURCHASE stock rows to Inward...\n";

foreach ($purchases as $p) {
    $pid = (int)$p['id'];
    $po = row('SELECT * FROM purchases WHERE id=?', [$pid]);
    if (!$po) {
        ledger_clear('PURCHASE', $pid);
        continue;
    }

    $inwardNo = next_series('inward_entries', 'inward_no', setting('inward_prefix', 'GRN'), $po['purchase_date']);
    $inwardId = insert('inward_entries', [
        'inward_no' => $inwardNo,
        'inward_date' => $po['purchase_date'],
        'purchase_id' => $pid,
        'party_id' => (int)$po['party_id'],
        'invoice_no' => $po['invoice_no'],
        'remarks' => 'Migrated from old Purchase stock entry',
        'created_by' => $po['created_by'],
    ]);

    foreach (rows('SELECT * FROM purchase_items WHERE purchase_id=? ORDER BY id', [$pid]) as $pi) {
        $type = val('SELECT item_type FROM items WHERE id=?', [$pi['item_id']]);
        if ($type === 'SERVICE') continue;
        insert('inward_items', [
            'inward_id' => $inwardId,
            'purchase_item_id' => (int)$pi['id'],
            'item_id' => (int)$pi['item_id'],
            'qty' => $pi['qty'],
            'unit' => $pi['unit'],
            'rate' => $pi['rate'],
        ]);
        ledger_add($po['purchase_date'], (int)$pi['item_id'], (float)$pi['qty'], 0, 'INWARD', $inwardId, (float)$pi['rate'], null,
            'Inward ' . $inwardNo . ' against PO ' . $po['po_no']);
    }
    ledger_clear('PURCHASE', $pid);
    echo "  PO {$po['po_no']} -> $inwardNo\n";
}

echo "Done.\n";
