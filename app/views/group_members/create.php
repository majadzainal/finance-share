<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Add Group Member</h1>
        <p class="text-secondary mb-0">Pilih group/store, member, dan share percent.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/group-members');
            $submitLabel = 'Save Group Member';
            $isEdit = false;
            require view_path('group_members/form.php');
            ?>
        </div>
    </div>
</div>
