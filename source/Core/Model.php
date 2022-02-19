<?php

namespace Source\Core;

use Source\Support\Message;
use Source\Support\Pagination;

/**
 * Model Core
 * @package Source\Core
 */
abstract class Model
{
    /** @var object|null */
    protected $dataSet;

    /** @var \PDOException */
    protected $fail;

    /** @var Message|null */
    protected $message;

    /** @var string */
    protected $query;

    /** @var string */
    protected $join;

    /** @var object */
    protected $objJoin;

    /** @var string */
    protected $terms;

    /** @var string|null|array */
    protected $params;

    /** @var string */
    protected $columns;

    /** @var string */
    protected $order;

    /** @var int */
    protected $limit;

    /** @var string */
    protected $pagination;

    /** @var int */
    protected $offset;

    /** @var string $entity database table */
    protected $entity;

    /** @var array $protected no update or create */
    protected $protected;

    /** @var array $entity database table */
    protected $required;

    /** @var string active Class for PHP 5.3 */
    protected $class;

    /** @var string */
    protected $fieldId;

    /** @var int */
    protected $lastId;

    /**
     * @param string $entity
     * @param string $fieldId
     * @param array $protected
     * @param array $required
     * @param string $class
     */
    public function __construct($entity, $fieldId, $protected, $required, $class)
    {
        $this->entity = $entity;
        $this->fieldId = $fieldId;
        $this->protected = $protected;
        $this->required = $required;
        $this->class = $class;

        $this->message = new Message();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->dataSet)) {
            $this->dataSet = new \stdClass();
        }

        $this->dataSet->$name = $value;
    }

    /**
     * @param $name
     * @return void
     */
    public function __get($name)
    {
        return empty($this->dataSet->$name) ? null : $this->dataSet->$name;
    }

    /**
     * @param $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->dataSet->$name);
    }


    public function data()
    {
        return $this->dataSet;
    }

    /**
     * @return \PDOException|null
     */
    public function fail()
    {
        return $this->fail;
    }

    /**
     * @return null|object
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Este método vai montar a query juntando o distinct, column, entity, terms e join
     * 
     * @return Model
     */
    private function setQuery()
    {
        $distinct = (!empty($this->distinct) ? "DISTINCT({$this->distinct})" : '');
        if (!empty($distinct) && !empty($this->columns)) {
            $distinct .= ", ";
        }

        $this->query = "
            SELECT {$distinct}{$this->columns} 
            FROM {$this->entity} 
            {$this->join} {$this->terms}
        ";
        return $this;
    }

    /**
     * Cria um distinct na coluna escolhida
     *
     * @param string $column a receber o DISTINCT
     * @return Model
     */
    public function distinct($column)
    {
        $this->distinct = $column;
        $this->setQuery();
        return $this;
    }

    /**
     * Adiciona termos a query . Já coloca o nome da tabela automaticamente
     * 
     * @param string $terms ex.: nome = :nome AND idade = :idade
     * @param string $params ex.: nome={$nome}&idade={$idade}
     * @param string $entity Nome da tabela, se nulo vai pegar a do model
     * @return Model
     */
    private function setTerms($terms, $params, $entity = null)
    {
        $entity = empty($entity) ? $this->entity : $entity;
        $arr = array(" AND ", " AND", "AND ", " OR ", " OR", "OR ");
        $arrOp =  array("{AND}", "{OR}");
        $arrOpReplace = array("AND", "OR");
        $arrReplace = array(
            " {AND} {$entity}.",
            " {AND} {$entity}.",
            " {AND} {$entity}.",
            " {OR} {$entity}.",
            " {OR} {$entity}.",
            " {OR} {$entity}."
        );

        $where = empty($this->terms) ? "WHERE" : "{$this->terms} AND";

        $this->terms = "{$where} {$entity}." . str_replace(
            $arrOp,
            $arrOpReplace,
            str_replace(
                $arr,
                $arrReplace,
                $terms
            )
        );

        $this->terms = str_replace($entity . '. LENGTH', ' LENGTH', $this->terms);
        $this->terms = str_replace($entity . '.LENGTH', ' LENGTH', $this->terms);

        parse_str($params, $params);
        $this->params = !empty($this->params) ? array_merge($this->params, $params) : $params;

        return $this;
    }

    /**
     * @param string $columns
     * @param string|null $entity
     * @return Model
     */
    private function setColumns($columns, $entity = null)
    {
        $entity = empty($entity) ? $this->entity : $entity;

        $currentColumns = !empty($this->columns) ? "{$this->columns}, " : "";

        $this->columns = "{$currentColumns}{$entity}." . str_replace(",", ", {$entity}.", $columns);
        $this->columns = str_replace(". ", ".", $this->columns);

        $this->columns = strtolower($this->columns);

        if (stristr($this->columns, 'count')) {
            $this->columns = str_replace("{$entity}.count(", "count({$entity}.", $this->columns);
        }

        if (stristr($this->columns, 'sum')) {
            $this->columns = str_replace("{$entity}.sum(", "sum({$entity}.", $this->columns);
        }

        if (stristr($this->columns, 'max')) {
            $this->columns = str_replace("{$entity}.max(", "max({$entity}.", $this->columns);
        }

        return $this;
    }

    /**
     * @param string|null $terms
     * @param string|null $params
     * @param string $columns
     * @return Model|mixed
     */
    public function find($terms = null, $params = null, $columns = "*")
    {
        $this->setColumns($columns);
        if (!empty($terms)) {
            $this->setTerms($terms, $params);
        }
        $this->setQuery();
        return $this;
    }

    /**
     * @param int $id
     * @param string $columns
     * @return null|mixed|Model
     */
    public function findById($id, $columns = "*")
    {
        $find = $this->find("{$this->fieldId} = :id", "id={$id}", $columns);
        return $find->fetch();
    }

    /**
     * @param string $entity tabela a ser relacionada
     * @param string $joinId id da tabela a ser relacionada que vai fazer parte do join
     * @param string|null $terms termos busca(where) para a tabela da relação
     * @param string|null $params 
     * @param string|null $columns colunas a ser buscadas desta tabela da relação
     * @param string|null $entityJoinId caso queira mudar o id da tabela base na relação, por padrão é a chave primária
     * @param string|null $entityJoin caso queira mudar a tabela base da relação
     * @return Model
     */
    public function join(
        $entity,
        $joinId,
        $terms = null,
        $params = null,
        $columns = null,
        $entityJoinId = null,
        $entityJoin = null
    ) {
        $this->objJoin = new \stdClass();
        $this->objJoin->type = "INNER";
        $this->objJoin->entity = $entity;
        $this->objJoin->joinId = $joinId;
        $this->objJoin->terms = $terms;
        $this->objJoin->params = $params;
        $this->objJoin->columns = $columns;
        $this->objJoin->entityJoinId = $entityJoinId;
        $this->objJoin->entityJoin = $entityJoin;

        return $this->setJoin();
    }

    /**
     * @param string $entity
     * @param string $joinId
     * @param string|null $terms
     * @param string|null $params
     * @param string|null $columns
     * @param string|null $entityJoinId
     * @param string|null $entityJoin
     * @return Model
     */
    public function leftJoin(
        $entity,
        $joinId,
        $terms = null,
        $params = null,
        $columns = null,
        $entityJoinId = null,
        $entityJoin = null
    ) {
        $this->objJoin = new \stdClass();
        $this->objJoin->type = "LEFT";
        $this->objJoin->entity = $entity;
        $this->objJoin->joinId = $joinId;
        $this->objJoin->terms = $terms;
        $this->objJoin->params = $params;
        $this->objJoin->columns = $columns;
        $this->objJoin->entityJoinId = $entityJoinId;
        $this->objJoin->entityJoin = $entityJoin;

        return $this->setJoin();
    }

    /**
     * @param string $entity
     * @param string $joinId
     * @param string|null $terms
     * @param string|null $params
     * @param string|null $columns
     * @param string|null $entityJoinId
     * @param string|null $entityJoin
     * @return Model
     */
    public function rightJoin(
        $entity,
        $joinId,
        $terms = null,
        $params = null,
        $columns = "*",
        $entityJoinId = null,
        $entityJoin = null
    ) {
        $this->objJoin = new \stdClass();
        $this->objJoin->type = "RIGHT";
        $this->objJoin->entity = $entity;
        $this->objJoin->joinId = $joinId;
        $this->objJoin->terms = $terms;
        $this->objJoin->params = $params;
        $this->objJoin->columns = $columns;
        $this->objJoin->entityJoinId = $entityJoinId;
        $this->objJoin->entityJoin = $entityJoin;

        return $this->setJoin();
    }

    /**
     * @return mixed|Model
     */
    private function setJoin()
    {
        $entityJoin = empty($this->objJoin->entityJoin) ? $this->entity : $this->objJoin->entityJoin;
        $entityJoinId = empty($this->objJoin->entityJoinId) ? $this->protected[0] : $this->objJoin->entityJoinId;

        if (!empty($this->objJoin->terms)) {
            $this->setTerms($this->objJoin->terms, $this->objJoin->params, $this->objJoin->entity);
        }

        $columns = !empty($this->objJoin->columns) ? $this->objJoin->columns : "*";
        $this->setColumns($columns, $this->objJoin->entity);

        $join = "{$this->objJoin->type} JOIN {$this->objJoin->entity} ON ({$entityJoin}.{$entityJoinId} = {$this->objJoin->entity}.{$this->objJoin->joinId})
            ";
        $this->join = !empty($this->join) ? $this->join . $join : $join;

        $this->setQuery();

        return $this;
    }

    /**
     * @param string $column
     * @return Model|null
     */
    public function group($columns)
    {
        $this->group = " GROUP BY {$columns}";
        return $this;
    }

    /**
     * @param string $column
     * @return Model|null
     */
    public function group_last($columnOrder, $desc = false)
    {
        $desc = empty($desc) ? "" : " DESC";
        $this->group_last = " GROUP BY {$columnOrder}{$desc}";
        return $this;
    }

    /**
     * @param string $column
     * @return Model|null
     */
    public function cont_sub($count)
    {
        $this->cont_sub = " {$count} ";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return Model
     */
    public function order($columnOrder, $desc = false)
    {
        $desc = empty($desc) ? "" : " DESC";
        $this->order = " ORDER BY {$columnOrder}{$desc}";
        return $this;
    }


    /**
     * @param string $columnOrder
     * @return Model
     */
    public function order_last($columnOrder, $desc = false)
    {
        $desc = empty($desc) ? "" : " DESC";
        $this->order_last = " ORDER BY {$columnOrder}{$desc}";
        return $this;
    }




    /**
     * @param integer $limit
     * @return Model
     */
    public function limit($limit)
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param integer $offset
     * @return Model
     */
    public function offset($offset)
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }



    public function pagination($count = false)
    {
        $pagination = new Pagination();
        $this->pagination = $pagination->basePagination($count);
        return $this;
    }


    /**
     * @param boolean $all
     * @return null|array|mixed|Model
     */
    public function fetch($all = false)
    {

        try {
            $limit = (!empty($this->pagination) ? $this->pagination : $this->limit . $this->offset);
            if (!empty($this->group_last) || !empty($this->order_last)) {
                $count_sub = (!empty($this->cont_sub) ? ',' . $this->cont_sub : null);
                $query = "SELECT *$count_sub FROM ( 
                    " . $this->query . $this->group . $this->order . "
                     ) AS meu_select " . ($this->group_last ?? null) . ($this->order_last ?? null) .  " " . $limit;
            } else {
                $query = $this->query . $this->group . $this->order . $limit;
            }
            $stmt = Connect::getInstance()->prepare($query);

            $stmt->execute($this->params);

            if (!$stmt->rowCount()) {
                return null;
            }

            if ($all) {
                return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
            }

            return $stmt->fetchObject($this->class);
            // return $stmt->fetchObject(static::class);
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * @param string $key 
     * @return integer
     */
    public function count($key = "id")
    {

        // var_dump($this->params );
        // exit;
        $limit = (!empty($this->pagination) ? $this->pagination : $this->limit . $this->offset);
        if (!empty($this->group_last) || !empty($this->order_last)) {
            $count_sub = (!empty($this->cont_sub) ? ',' . $this->cont_sub : null);
            $query = "SELECT *$count_sub FROM ( 
                " . $this->query . $this->group . $this->order . "
                 ) AS meu_select " . ($this->group_last ?? null) . ($this->order_last ?? null) .  " " . $limit;
        } else {
            $query = $this->query . $this->group . $this->order . $limit;
        }

        // var_dump('<pre>',$query,$this->params);
        // exit;
        try {
            $stmt = Connect::getInstance()->prepare($query);
            $stmt->execute($this->params);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }


    /**
     * @param string $select
     * @param string|null $params
     * @return null|\PDOStatement
     */
    protected function read($select, $params = null, $fetchAll = true)
    {
        try {

            // var_dump('<pre>',$select);

            $stmt = Connect::getInstance()->prepare($select);
            if ($params) {
                parse_str($params, $params);
                $params = (array)$params;
                foreach ($params as $key => $value) {
                    if ($key == 'limit' || $key == 'offset') {
                        $stmt->bindValue(":{$key}", $value, \PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue(":{$key}", $value, \PDO::PARAM_STR);
                    }
                }
            }

            $stmt->execute();

            if (!$stmt->rowCount()) {
                return null;
            }

            if ($fetchAll) {
                return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->class);
            }

            return $stmt->fetchObject($this->class);
        } catch (\PDOException $e) {

            $this->fail = $e;
            return null;
        }
    }

    /**
     * @param array $data
     * @return int|null
     */
    protected function create($data)
    {
        try {

            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));
            $stmt = Connect::getInstance()->prepare("INSERT INTO {$this->entity} ({$columns}) VALUES ({$values})");
            $stmt->execute($this->filter($data));
            $this->lastId = Connect::getInstance()->lastInsertId();
            return $this->lastId;
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * @param array $data
     * @param string $terms
     * @param string $params
     * @return int|null
     */
    protected function update($data, $terms, $params)
    {
        try {

            $dataSet = array();
            foreach ($data as $bind => $value) {
                $dataSet[] = " {$this->entity}.{$bind} = :{$bind}";
            }
            $dataSet = implode(", ", $dataSet);
            parse_str($params, $params);

            $stmt = Connect::getInstance()->prepare("UPDATE {$this->entity} SET {$dataSet} WHERE {$terms}");
            $stmt->execute($this->filter(array_merge($data, $params)));
            return ($stmt->rowCount() ? $stmt->rowCount() : 1);
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * Verifica dados antes de inserir. Método criado para validar a inserção de dados em mais de uma
     * tabela em uma única requisição. Criado para não precisar usar apenas beginTransaction()
     *
     * @return bool
     */
    public function beforeSave()
    {
        if (!$this->required()) {
            $this->message->warning('Por favor informe todos os campos');
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function save()
    {


        if (!$this->required()) {
            $this->message->warning('Por favor informe todos os campos');
            return false;
        }

        // Verificando se existe dados no banco
        $fieldId = $this->fieldId;
        $hasData = $this->findById($this->$fieldId, $fieldId);

        $update = (!empty($hasData) && !empty($this->$fieldId) ? true : false);

        // Update
        if ($update) {
            $id = $this->$fieldId;
            $this->update($this->safe(), "{$this->fieldId} = :id", "id={$id}");
            if ($this->fail()) {
                $this->message->error('Erro ao atualizar - verifique os dados');
                return false;
            }
        }

        // Create
        if (!$update) {
            $id = $this->create($this->safe());
            if ($this->fail()) {
                $this->message->error('Erro ao inserir - verifique os dados');
                return false;
            }
        }


        $data = $this->findById($id);
        if ($data) {
            $this->dataSet = $data->data();
        }
        return true;
    }

    /**
     * @return int
     */
    public function lastId()
    {
        return $this->lastId;
    }

    /**
     * @param string $terms
     * @param null|string $params
     * @return bool
     */
    public function delete($terms, $params)
    {
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM {$this->entity} WHERE {$terms}");

            if ($params) {
                parse_str($params, $params);
                $stmt->execute((array)$params);
                return true;
            }

            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            $this->fail = $e;
            return false;
        }
    }

    /**
     * Remove um objeto de modelo ativo
     * @return bool
     */
    public function destroy()
    {
        $fieldId = $this->fieldId;
        if (empty($this->$fieldId)) {
            return false;
        }

        $destroy = $this->delete("{$fieldId} = :id", "id={$this->$fieldId}");
        return $destroy;
    }

    /**
     * Para a execução e mostra a query
     *
     * @return Model
     */
    public function debug($consoleLog = false)
    {
        $limit = (!empty($this->pagination) ? $this->pagination : $this->limit . $this->offset);


        if (!empty($this->group_last) || !empty($this->order_last)) {
            $count_sub = (!empty($this->cont_sub) ? ',' . $this->cont_sub : null);
            $query = "SELECT *$count_sub FROM ( 
                " . $this->query . $this->group . $this->order . "
                 ) AS meu_select " . ($this->group_last ?? null) . ($this->order_last ?? null) .  " " . $limit;
        } else {
            $query = $this->query . $this->group . $this->order . $limit;
        }
        echo '<pre>' . $query;
        exit;
    }

    public function setParams($params): Model
    {
        parse_str($params, $params);
        $this->params = $params;
        return $this;
    }


    /**
     * @return array|null
     */
    protected function safe()
    {
        $safe = (array) $this->dataSet;
        foreach ($this->protected as $unset) {
            unset($safe[$unset]);
        }
        return $safe;
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function filter($data)
    {
        $filter = array();
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : htmlspecialchars($value));
        }
        return $filter;
    }

    public function columns($columns)
    {
        (empty($this->columns) ? $this->columns = $columns : $this->columns .= ' ' . $columns);
        return $this;
    }

    public function terms($terms)
    {
        (empty($this->terms) ? $this->terms = $terms : $this->terms .= ' ' . $terms);
        return $this;
    }


    public function params($params)
    {
        parse_str($params, $params);
        $this->params = !empty($this->params) ? array_merge($this->params, $params) : $params;
        return $this;
    }


    /**
     * @return boolean
     */
    protected function required()
    {
        $data = (array) $this->data();
        foreach ($this->required as $field) {
            if (empty($data[$field])) {
                return false;
            }
            return true;
        }
    }
}
