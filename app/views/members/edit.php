<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Edit Member</h1>
        <p class="text-secondary mb-0">Perbarui profil dan status member.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/members/' . $member['id']);
            $submitLabel = 'Update Member';
            require view_path('members/form.php');
            ?>
        </div>
    </div>
</div>
