        </div>
        <footer class="app-footer no-print">
            <span>&copy; <?= date('Y') ?> <?= e(setting('company_name', 'Fenvitta Lifesciences LLP')) ?> &middot; Inventory Management System</span>
            <span class="ms-auto">Designed &amp; Developed By <a href="https://www.oceaninfotech.co.in/" target="_blank" rel="noopener">Ocean Infotech</a></span>
        </footer>
    </main>
</div>

<!-- Toast messages + confirm dialog -->
<div id="toastBox" class="no-print"></div>
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content confirm-box">
            <div class="modal-body text-center p-4">
                <div class="confirm-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <h5 class="mt-3 mb-1">Are you sure?</h5>
                <p class="text-muted small mb-4" id="confirmText"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmYes"><i class="bi bi-check2"></i> Yes, do it</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>window.FV_FLASH = <?= json_encode(get_flashes()) ?>;</script>
<script src="<?= url('assets/vendor/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php
// Extra libraries needed by a page, e.g. $pageLibs = ['qrcode.min'] or ['chart.umd.min']
foreach ($pageLibs ?? [] as $lib) echo '<script src="' . url('assets/vendor/' . $lib . '.js') . '"></script>' . "\n";
if (!empty($pageScripts)) echo $pageScripts;
?>
</body>
</html>
