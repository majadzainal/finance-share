<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Edit Group</h1>
        <p class="text-secondary mb-0">Perbarui code, name, dan status group.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/groups/' . $group['id']);
            $submitLabel = 'Update Group';
            require view_path('groups/form.php');
            ?>
        </div>
    </div>
</div>
