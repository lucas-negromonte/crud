<?php

namespace Source\Models;

use Source\Core\Model;

/**
 * User Models\AnexxaBr

 * @author Lucas Almeida <lucas.almeida@anexxa.com.br>
 * @package Source\Models\AnexxaBr
 */
class User extends Model
{
    /**
     * User constructor
     */
    public function __construct()
    {
          parent::__construct("users", ["id"], ["name", "email"]);
    }
}
