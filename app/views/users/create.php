<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php
        $action = url('/users');
        $submitLabel = 'Create User';
        $passwordRequired = true;
        require view_path('users/form.php');
        ?>
    </div>
</div>
