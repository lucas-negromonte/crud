<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= (isset($title) ? $title . " :: " : "") ?>OM Telemarketing</title>
    <link type="text/css" href="<?= theme("/assets/style.css", CONF_VIEW_THEME) ?>" rel="stylesheet">
    <?= $v->section("styles"); ?>

</head>

<body class="d-flex flex-column h-100 bg-secondary">
    <header>
        <div class="py-3 text-center text-md-left  bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 col-md-6 text-left">
                        <div class="row">
                            <div class="col-sm-12 col-md-3">
                                <i class="bi bi-person-bounding-box text-secondary" style="font-size: xxx-large;"></i>

                            </div>
                            <div class="col-sm col-md-9 text-start mt-auto">
                                <h5 class="text-secondary"><?= ($title ?? null) ?></h5>
                                <small class="text-secondary"><?= ($sub_title ?? null) ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 mt-auto text-end">
                        <?= $v->section("top-right"); ?>
                    </div>
                </div>
            </div>

        </div>


    </header>

    <!-- Begin page content -->
    <main class="flex-shrink-0">
        <div class="container p-3">
            <?= $v->section("content"); ?>

            <div class="main-loading">
                <span class="spinner spinner-border text-primary" role="status">
                    <span class="sr-only"></span>
                </span>
            </div>

        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container text-center">
            <span style="font-size: 1em;">Copyright <?= date("Y") ?> - All rights reserved </span>
        </div>
    </footer>

    <script src="<?= theme("/assets/scripts.js"); ?>?<?= date('YmdHis') ?>"></script>
    <?= $v->section("scripts"); ?>
    <div class="ajax_response"><?= flash_message() ?></div>
</body>

</html>