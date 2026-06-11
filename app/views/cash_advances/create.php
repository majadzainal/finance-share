<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Input Kasbon</h1>
        <p class="text-secondary mb-0">Remaining amount otomatis mengikuti amount saat kasbon dibuat.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/cash-advances');
            $submitLabel = 'Save Kasbon';
            require view_path('cash_advances/form.php');
            ?>
        </div>
    </div>
</div>
