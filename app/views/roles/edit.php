<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php
        $action = url('/roles/' . $role['id']);
        $submitLabel = 'Save Changes';
        require view_path('roles/form.php');
        ?>
    </div>
</div>
