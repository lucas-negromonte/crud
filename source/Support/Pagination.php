<?php

namespace Source\Support;

use stdClass;

class Pagination
{

    // /** @var string  = armazena tudo que houver na URL após o {?} */
    // protected $queryString;

    // /** Pagina Atual */
    // protected $currentPage;

    // /** última página */
    // protected $lastPage ;
    private $limit;



    public function __construct()
    {
        $this->limit = 50;
    }

    public function basePagination()
    {
        $limit = $this->limit;

        /** Pagina Atual */
        $currentPage = $this->currentPage();

        $paginationStart = ($limit * $currentPage) - $limit;
        $pagination = " LIMIT " . $paginationStart . " ," . $limit . " ";
        return $pagination;
    }

    public function paginationStart()
    {
        $limit = $this->limit;
        $currentPage = $this->currentPage();
        return ($limit * $currentPage) - $limit;
    }

    /** Pagina Atual */
    public function currentPage()
    {
        $ex = explode("page=", $_SERVER["REQUEST_URI"]);
        return (!empty($ex[1]) ? $ex[1] : 1);
    }

    public function showPagination($totalRows)
    {
        $limit = $this->limit;

        /** Pagina Atual */
        $currentPage = $this->currentPage();

        /** última página */
        $lastPage = ceil($totalRows / $limit);

        if (!empty($_GET['page']) && !empty($_SERVER["REQUEST_URI"])) {
            $_SERVER["REQUEST_URI"] =  str_replace('?page=' . $_GET['page'], '', $_SERVER["REQUEST_URI"]);
            $_SERVER["REQUEST_URI"] =  str_replace('?page=' . $_GET['page'], '', $_SERVER["REQUEST_URI"]);
        }

        $ex = explode("?", $_SERVER["REQUEST_URI"]);
        //Se for O primeiro parametro, deve iniciar com interrogação
        if (empty($ex[1])) {
            $paramPage = "?page=";
        } else {
            $paramPage = "&page=";
        }

        //Não existe pagina 2
        if ($totalRows <= $limit) {
            return "";
        }

        /** Url Limpa */
        $cleanUrl =  str_replace($paramPage . $currentPage, '', $_SERVER["REQUEST_URI"]);



        $pagination = '<nav aria-label="Navegação de página" style="margin: 30px;">
        <ul class="pagination pagination-md justify-content-center" style="font-size: large;">';


        if ($currentPage > 1) {
            $pagination .= ' 	<li class="page-item">
            <a class="page-link shadow-none add_loading" href="' . $cleanUrl . $paramPage . ($currentPage - 1) . '" tabindex="-1" style="height: 100%;" ><i class="fas fa-angle-double-left"></i></a>
          </li>';
        }


        if ($currentPage < $lastPage - 3) {
            $fim_paginacao = $currentPage + 3;
            $mostra_ultima_pg = '	<li class="page-item" ><a class="page-link" style="cursor: auto;">...</a></li>
                      <li class="page-item"><a class="page-link shadow-none add_loading"  href="' . $cleanUrl . $paramPage . $lastPage . '">' . $lastPage . '</a></li>';
        } else {
            $mostra_ultima_pg = '';
            $fim_paginacao = $lastPage;
        }

        if ($currentPage > 4) {
            $inicio_paginacao = $currentPage - 3;
            $mostra_primeira_pg = '	<li class="page-item"><a class="page-link shadow-none add_loading"  href="' . $cleanUrl . $paramPage . '1">1</a></li>
                          <li class="page-item" ><a class="page-link" style="cursor: auto;">...</a></li>';
        } else {
            $inicio_paginacao = 1;
            $mostra_primeira_pg = '';
        }

        $pagination .= $mostra_primeira_pg;

        for ($i = $inicio_paginacao; $i <= $fim_paginacao; $i++) {
            $pagination .= '<li class="page-item ' . ($currentPage == $i ? "active" : "") . '"><a class="page-link shadow-none add_loading"  href="' . $cleanUrl . $paramPage . $i . '">' . $i . '</a></li>';
        }

        $pagination .= $mostra_ultima_pg;

        if ($currentPage < $lastPage) {
            $pagination .= ' 	<li class="page-item">
            <a class="page-link shadow-none add_loading" href="' . $cleanUrl . $paramPage . ($currentPage + 1) . '" style="height: 100%;"><i class="fas fa-angle-double-right"></i></a>
          </li>';
        }
        $pagination .= '</ul>
          </nav>';

        return $pagination;
    }


    /**
     * otimizar a contagem do sistema em uma sessão
     *
     * @param object $class
     * @param string $method
     * @param array $params
     * @return integer
     */
    public static function count(object $class, string $method, ?array $params): int
    {
        // se existir parametro page e não for fazio a sessão rows, retornar a total pela sessão
        if (!empty($_GET['page']) && !empty(session()->pagination_rows)) {
            // return session()->pagination_rows;
        }

        // verifica se existe esse metodo nessa classe
        if (!method_exists($class, $method)) {
            return 0;
        }

        // envia uma sessão para model informando que é uma contagem
        session()->set('pagination_count', true);

        // faz a busca na model para buscar os dados atualizados
        $rows =  $class->$method($params);

        // remove a sessão de contagem
        session()->clearSession('pagination_count');

        // se existir  registros e se retorno é um array  -- faz a contagem
        if ($rows && is_array($rows)) {
            $rows = count($rows);
        } else {
            $rows = 0;
        }

        // salva o total de registro na sessão
        session()->set('pagination_rows', $rows);

        return $rows;
    }

}
