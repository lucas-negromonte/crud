<?php

namespace Source\Core;

use Source\Support\Message;

/**
 * Class Controller
 *
 * @author Lucas Almeida <lucas.almeida@anexxa.com.br>
 * @package Source\Core
 */
class Controller
{
    /** @var View */
    protected $view;

    /** @var Message */
    protected $message;

    /**
     * Controller constructor.
     * @param string|null $pathToViews
     */
    public function __construct( $pathToViews = null)
    {
        $pathToViews = (empty($pathToViews) ? CONF_VIEW_PATH . "/" . CONF_VIEW_THEME : $pathToViews);
        $this->view = new View($pathToViews);
        $this->message = new Message();
    }
}
