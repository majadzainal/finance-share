<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Create Member</h1>
        <p class="text-secondary mb-0">Tambahkan member baru.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/members');
            $submitLabel = 'Save Member';
            require view_path('members/form.php');
            ?>
        </div>
    </div>
</div>
