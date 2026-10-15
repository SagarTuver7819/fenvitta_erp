<?php
/* Add new party  (/parties/add)   or   Edit party  (/parties/edit/{id}) */
require_once __DIR__ . '/../../includes/init.php';

$types = ['Supplier', 'Customer', 'Both', 'Job Worker', 'Transporter'];

$id = (int)get('id');
$party = $id ? row('SELECT * FROM parties WHERE id=?', [$id]) : null;
if ($id && !$party) redirect('parties');

// ---------- Save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'party_type' => in_array(post('party_type'), $types) ? post('party_type') : 'Supplier',
        'name' => post('name'),
        'address' => post('address'),
        'city' => post('city'),
        'state' => post('state'),
        'gstin' => strtoupper(post('gstin')),
        'contact_person' => post('contact_person'),
        'phone' => post('phone'),
        'email' => post('email'),
        'payment_terms' => (int)post('payment_terms'),
        'is_active' => post('is_active') ? 1 : 0,
    ];
    if ($data['name'] === '') {
        flash('danger', 'Party name is required.');
        redirect($id ? "parties/edit/$id" : 'parties/add');
    }
    if ($id) {
        update('parties', $data, $id);
        flash('success', 'Party "' . $data['name'] . '" updated.');
    } else {
        insert('parties', $data);
        flash('success', 'Party "' . $data['name'] . '" added.');
    }
    redirect('parties');
}

$p = $party ?? [
    'party_type' => get('type') ?: 'Supplier', 'name' => '', 'address' => '', 'city' => '', 'state' => 'Gujarat', 'gstin' => '',
    'contact_person' => '', 'phone' => '', 'email' => '', 'payment_terms' => 30, 'is_active' => 1,
];

$pageTitle = $party ? 'Edit Party' : 'Add Party';
$active = 'parties';
include ROOT_PATH . '/includes/header.php';
?>
<div class="card" style="max-width:820px">
    <div class="card-header"><i class="bi bi-person-plus"></i> <?= $pageTitle ?></div>
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Type *</label>
                    <select name="party_type" class="form-select">
                        <?php foreach ($types as $t): ?><option <?= $p['party_type'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8"><label class="form-label">Party Name *</label><input name="name" class="form-control" required value="<?= e($p['name']) ?>"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea name="address" rows="2" class="form-control"><?= e($p['address']) ?></textarea></div>
                <div class="col-md-4"><label class="form-label">City</label><input name="city" class="form-control" value="<?= e($p['city']) ?>"></div>
                <div class="col-md-4"><label class="form-label">State</label><input name="state" class="form-control" value="<?= e($p['state']) ?>"></div>
                <div class="col-md-4"><label class="form-label">GSTIN</label><input name="gstin" class="form-control" maxlength="15" value="<?= e($p['gstin']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Contact Person</label><input name="contact_person" class="form-control" value="<?= e($p['contact_person']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($p['phone']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($p['email']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Payment Terms (days)</label><input type="number" name="payment_terms" class="form-control" value="<?= e($p['payment_terms']) ?>"></div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_active" id="act" value="1" <?= $p['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="act">Active</label>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <a href="<?= url('parties') ?>" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
                <button class="btn btn-primary ms-auto"><i class="bi bi-check2"></i> <?= $party ? 'Update' : 'Save' ?> Party</button>
            </div>
        </form>
    </div>
</div>
<?php include ROOT_PATH . '/includes/footer.php'; ?>
