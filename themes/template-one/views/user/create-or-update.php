<?php $v->layout('_theme') ?>

<?php $v->start('top-right');  ?>
<a class="btn btn-secondary" href="<?= get_route('admin.users') ?>">Usuarios</a>
<?php $v->end('top-right');  ?>

<form action="<?= get_route('admin.user.createOrUpdate') ?>" method="post" enctype="multipart/form-data">
    <?= csrf(); ?>
    <input type="hidden" name="id" value="<?= ($user->id ?? null) ?>">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-sm">
                    <div class="mb-2">
                        <label for="name">Nome</label>
                        <input class="form-control" type="text" name="name" value="<?= ($user->name ?? null) ?>">
                    </div>
                </div>
                <div class="col-sm">
                    <div class="mb-2">
                        <label for="email">E-mail</label>
                        <input class="form-control" type="email" name="email" value="<?= ($user->email ?? null) ?>">
                    </div>
                </div>
                <div class="col-sm mt-auto">
                    <div class="mb-2">
                        <button class="btn btn-success w-100">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>