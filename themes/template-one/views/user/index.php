<?php $v->layout('_theme') ?>

<?php $v->start('top-right');  ?>
<a class="btn btn-secondary" href="<?= get_route('admin.user', "id=create") ?>">Criar usuario</a>
<?php $v->end('top-right');  ?>

<div class="card mb-3">
    <div class="card-body">
        <?php if (empty($users)) : ?>
            <h5 class="text-danger"> Nenhum usuario cadastrado.</h5>
        <?php else :  ?>

            <table class="table table-striped">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td><?= $user->name ?></td>
                            <td><?= $user->email ?></td>
                            <td class="text-center">
                                <a class="btn text-primary" href="<?= get_route('admin.user', "id={$user->id}") ?>"><i class="bi bi-pencil-square"></i></a>
                                <button class="btn text-danger" data-update="true" data-url="<?= get_route('admin.user.destroy') ?>" data-id="<?= $user->id ?>"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif;  ?>
    </div>
</div>