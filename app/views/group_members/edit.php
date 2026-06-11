<div class="d-flex flex-column gap-4">
    <div>
        <h1 class="h3 mb-1">Edit Share Percent</h1>
        <p class="text-secondary mb-0">Perbarui share percent member dalam group/store.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php
            $action = url('/group-members/' . $groupMember['id']);
            $submitLabel = 'Update Share Percent';
            $isEdit = true;
            require view_path('group_members/form.php');
            ?>
        </div>
    </div>
</div>
