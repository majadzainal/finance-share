<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php
        $action = url('/users/' . $user['id']);
        $submitLabel = 'Save Changes';
        $passwordRequired = false;
        require view_path('users/form.php');
        ?>
    </div>
</div>
