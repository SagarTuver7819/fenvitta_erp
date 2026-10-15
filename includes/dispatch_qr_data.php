<?php
/*
 * Loads one dispatch with its items and builds the QR text.
 * Input:  $id (dispatch id)
 * Output: $dsp, $dspItems, $qrText, $verifyUrl
 */
$dsp = row('SELECT d.*, p.name party, p.address, p.city, p.state, p.gstin, p.phone
            FROM dispatches d JOIN parties p ON p.id = d.party_id WHERE d.id=?', [$id]);
if (!$dsp) redirect('dispatch');

$dspItems = rows('SELECT di.*, i.name item_name, i.unit, i.cas_no, b.batch_no, b.mfg_date, b.exp_date
                  FROM dispatch_items di JOIN items i ON i.id = di.item_id LEFT JOIN production_batches b ON b.id = di.batch_id
                  WHERE di.dispatch_id=? ORDER BY di.id', [$id]);

$verifyUrl = full_url('verify?t=' . $dsp['qr_token']);

$qrLines = [
    'FENVITTA LIFESCIENCES LLP',
    'Dispatch: ' . $dsp['dispatch_no'] . ' Dt ' . dmy($dsp['dispatch_date']),
    'Invoice: ' . ($dsp['invoice_no'] ?: '-'),
    'Customer: ' . $dsp['party'],
];
foreach ($dspItems as $it) {
    $qrLines[] = $it['item_name'] . ' ' . $it['grade'] . ' | Batch ' . $it['batch_no'] . ' | ' . qty($it['qty']) . ' ' . $it['unit']
        . ' | Mfg ' . ($it['mfg_date'] ? date('m/Y', strtotime($it['mfg_date'])) : '-') . ' Exp ' . ($it['exp_date'] ? date('m/Y', strtotime($it['exp_date'])) : '-');
}
if ($dsp['vehicle_no']) $qrLines[] = 'Vehicle: ' . $dsp['vehicle_no'];
$qrLines[] = 'Verify: ' . $verifyUrl;
$qrText = implode("\n", $qrLines);
