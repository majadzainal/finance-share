<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Edit Expense</h1>
        <p class="text-secondary mb-0">Expense hanya bisa diedit selama belum locked oleh closing.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/expenses/' . $expense['id']);
            $submitLabel = 'Update Expense';
            require view_path('expenses/form.php');
            ?>
        </div>
    </div>
</div>
