<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Create Group</h1>
        <p class="text-secondary mb-0">Tambahkan group atau store baru.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/groups');
            $submitLabel = 'Save Group';
            require view_path('groups/form.php');
            ?>
        </div>
    </div>
</div>
