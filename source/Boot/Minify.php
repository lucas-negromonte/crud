<?php

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;

if (!empty($_SERVER['HTTP_HOST']) && stristr($_SERVER['HTTP_HOST'], 'localhost')) {

    /**
     * CSS Assets
     */
    $minCSS = new CSS();
    $minCSS->add(__DIR__ . "/../../shared/styles/bootstrap-5/bootstrap.min.css");

  
    $cssDir = scandir(CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/css");


    foreach ($cssDir as $css) {
        $cssFile = CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/css/{$css}";

        $path = pathinfo($cssFile);
        if (is_file($cssFile) && $path['extension'] == "css") {
            $minCSS->add($cssFile);
        }
    }

    $minCSS->add(__DIR__ . "/../../shared/styles/bootstrap-5/bootstrap-icons-1.4.1/bootstrap-icons.css");
    $minCSS->minify(CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/style.css");

    /**
     * JS Assets
     */
    $minJS = new JS();
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery/jquery.min.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery/jquery.form.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/jquery/jquery-ui.js");
    $minJS->add(__DIR__ . "/../../shared/scripts/bootstrap-5/bootstrap.min.js");

    $jsDir = scandir(CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/js");
    foreach ($jsDir as $js) {
        $jsFile =
            CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/js/{$js}";

        $path = pathinfo($jsFile);
        if (is_file($jsFile) && $path['extension'] == "js") {
            $minJS->add($jsFile);
        }
    }
    $minJS->minify(CONF_VIEW_PATH . "/" . CONF_VIEW_THEME . "/assets/scripts.js");
}
