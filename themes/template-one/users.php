<?php $v->layout('_theme') ?>

<?php $v->start('styles') ?>
<?php $v->end('styles') ?>

<form action="<?= get_route('admin.users.post') ?>" method="post" enctype="multipart/form-data">
    <?= csrf(); ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm">
                    <div class="mb-2">
                        <label for="name">Nome</label>
                        <input class="form-control" type="text" name="name" value="">
                    </div>
                </div>
                <div class="col-sm">
                    <div class="mb-2">
                        <label for="email">E-mail</label>
                        <input class="form-control" type="email" name="email" value="">
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

<?php $v->start('scripts') ?>
<?php $v->end('scripts') ?>