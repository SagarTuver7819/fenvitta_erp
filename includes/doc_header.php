<?php /* Letter head used on all print documents. Set $docTitle before include. */ ?>
<div class="doc-head">
    <img src="<?= url('assets/img/logo.jpg') ?>" alt="Fenvitta">
    <div class="co">
        <b style="color:#632a4a;font-size:.95rem"><?= e(setting('company_name')) ?></b><br>
        <?= e(setting('office_address')) ?><br>
        <?= e(setting('phone')) ?> &middot; <?= e(setting('email')) ?> &middot; <?= e(setting('website')) ?>
        <?php if (setting('gstin')): ?><br><b>GSTIN:</b> <?= e(setting('gstin')) ?><?php endif; ?>
    </div>
</div>
<div class="doc-title"><?= e($docTitle ?? '') ?></div>
