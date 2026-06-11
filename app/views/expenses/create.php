<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Create Expense</h1>
        <p class="text-secondary mb-0">Tambah pengeluaran baru.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/expenses');
            $submitLabel = 'Save Expense';
            require view_path('expenses/form.php');
            ?>
        </div>
    </div>
</div>
