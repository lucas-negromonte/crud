<?php

namespace Source\Core;

use Source\Support\Message;

/**
 * Model Core
 * @package Source\Core
 */
abstract class Model
{
    /** @var object|null */
    public $data;

    /** @var \PDOException */
    protected $fail;

    /** @var Message|null */
    protected $message;

    /** @var string */
    protected $query;

    /** @var string */
    protected $join;

    /** @var string */
    protected $terms;

    /** @var string */
    protected $params;

    /** @var string */
    protected $columns;

    /** @var string */
    protected $order;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var string $entity database table */
    protected $entity;

    /** @var array $protected no update or create */
    protected $protected;

    /** @var array $entity database table */
    protected $required;

    /**
     * Model contructor
     * @param string $entity database table name
     * @param array $protected table protected columns
     * @param array $required table required columns
     */
    public function __construct(string $entity, array $protected, array $required)
    {
        $this->entity = CONF_DB_NAME . ".{$entity}";
        $this->protected = array_merge($protected, ["created_at", "updated_at"]);
        $this->required = $required;
        $this->message = new Message();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new \stdClass();
        }
        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return void
     */
    public function __get($name)
    {
        return $this->data->$name ?? null;
    }

    /**
     * @param $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    public function data()
    {
        return $this->data;
    }

    /**
     * @return \PDOException|null
     */
    public function fail(): ?\PDOException
    {
        return $this->fail;
    }

    /**
     * @return Message|null
     */
    public function message(): ?Message
    {
        return $this->message;
    }

    /**
     * Este método vai montar a query juntando o distinct, column, entity, terms e join
     * 
     * @return Model
     */
    private function setQuery(): Model
    {
        $distinct = (!empty($this->distinct) ? "DISTINCT({$this->distinct})" : '');

        if (!empty($distinct) && !empty($this->columns)) {
            $distinct .= ", ";
        }

        $this->query = "
            SELECT {$distinct}{$this->columns} 
            FROM {$this->entity} 
            {$this->join} 
            {$this->terms}
        ";
        return $this;
    }

    /**
     * Cria um distinct na coluna escolhida
     *
     * @param string $column a receber o DISTINCT
     * @return Model
     */
    public function distinct(string $column): Model
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
    private function setTerms(?string $terms, ?string $params, ?string $entity = null): Model
    {
        $entity = empty($entity) ? $this->entity : $entity;

        $arr = [" AND ", " AND", "AND ", " OR ", " OR", "OR "];
        $arrOp =  ["{AND}", "{OR}"];
        $arrOpReplace = ["AND", "OR"];
        $arrReplace = [
            " {AND} {$entity}.",
            " {AND} {$entity}.",
            " {AND} {$entity}.",
            " {OR} {$entity}.",
            " {OR} {$entity}.",
            " {OR} {$entity}."
        ];

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
        parse_str($params, $params);
        $this->params = !empty($this->params) ? array_merge($this->params, $params) : $params;

        return $this;
    }

    /**
     * @param string $columns
     * @param string|null $entity
     * @return Model
     */
    private function setColumns(string $columns, ?string $entity = null): Model
    {
        $entity = empty($entity) ? $this->entity : $entity;

        $currentColumns = !empty($this->columns) ? "{$this->columns}, " : "";

        $this->columns = "{$currentColumns}{$entity}." . str_replace(",", ", {$entity}.", $columns);
        $this->columns = str_replace(". ", ".", $this->columns);

        return $this;
    }

    /**
     * @param string|null $terms
     * @param string|null $params
     * @param string $columns
     * @return Model|mixed
     */
    public function find(?string $terms, ?string $params = null, string $columns = "*"): Model
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
    public function findById(?int $id, string $columns = "*"): ?Model
    {
        $find = $this->find("id = :id", "id={$id}", $columns);
        return $find->fetch();
    }

    /**
     * @param string $column
     * @return Model|null
     */
    public function group(string $columns): ?Model
    {
        $this->group = " GROUP BY {$columns}";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return Model
     */
    public function order(string $columnOrder): Model
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param integer $limit
     * @return Model
     */
    public function limit(int $limit): Model
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param integer $offset
     * @return Model
     */
    public function offset(int $offset): Model
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    /**
     * @param boolean $all
     * @return null|array|mixed|Model
     */
    public function fetch(bool $all = false)
    {

        try {
            $stmt = Connect::getInstance()
                ->prepare($this->query . $this->group . $this->order . $this->limit . $this->offset);
            $stmt->execute((array)$this->params);

            if (!$stmt->rowCount()) {
                return null;
            }

            if ($all) {
                return $stmt->fetchAll(\PDO::FETCH_CLASS, static::class);
            }

            return $stmt->fetchObject(static::class);
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * @param string $key
     * @return integer
     */
    public function count(string $key = "id"): int
    {
        $stmt = Connect::getInstance()->prepare($this->query);
        $stmt->execute((array)$this->params);
        return $stmt->rowCount();
    }


    /**
     * @param string $select
     * @param string|null $params
     * @return null|\PDOStatement
     */
    public function read(string $select, string $params = null): ?\PDOStatement
    {
        try {
            $stmt = Connect::getInstance()->prepare($select);
            if ($params) {
                parse_str($params, $params);
                foreach ((array)$params as $key => $value) {
                    if ($key == 'limit' || $key == 'offset') {
                        $stmt->bindValue(":{$key}", $value, \PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue(":{$key}", $value, \PDO::PARAM_STR);
                    }
                }
            }

            $stmt->execute();
            return $stmt;
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    /**
     * @param array $data
     * @return int|null
     */
    protected function create(array $data): ?int
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));
            $stmt = Connect::getInstance()->prepare("INSERT INTO {$this->entity} ({$columns}) VALUES ({$values})");
            $stmt->execute($this->filter($data));
            return connect::getInstance()->lastInsertId();
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
    protected function update(array $data, string $terms, string $params): ?int
    {
        try {
            $dataSet = [];
            foreach ($data as $bind => $value) {
                $dataSet[] = "{$bind} = :{$bind}";
            }
            $dataSet = implode(", ", $dataSet);
            parse_str($params, $params);
            $stmt = Connect::getInstance()->prepare("UPDATE {$this->entity} SET {$dataSet} WHERE {$terms}");
            $stmt->execute($this->filter(array_merge($data, $params)));
            return ($stmt->rowCount() ?? 1);
        } catch (\PDOException $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * Salvar ou atualizar
     * 
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->required()) {
            $this->message->warning('Todos os campos são obrigatórios, Verifique os dados');
            return false;
        }

        /** Update */
        if (!empty($this->id)) {
            $id = $this->id;
            $this->update($this->safe(), "id = :id", "id={$id}");
            if ($this->fail()) {
                $this->message->error('Não foi possivel atualizar verifique os dados');
                return false;
            }
        }

        /** Create */
        if (empty($this->id)) {
            $id = $this->create($this->safe());

            if ($this->fail()) {
                $this->message->error('Não foi possivel inserir, verifique os dados');
                return false;
            }
        }

        $this->id = $id;
        return true;
    }

    /**
     * @param string $terms
     * @param null|string $params
     * @return bool
     */
    public function delete(string $terms, ?string $params): bool
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
        } catch (\PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    /**
     * Remove um objeto de modelo ativo
     * @return bool
     */
    public function destroy(): bool
    {
        if (empty($this->id)) {
            return false;
        }

        $destroy = $this->delete("id = :id", "id={$this->id}");
        return $destroy;
    }

    /**
     * Para a execução e mostra a query
     *
     * @return Model
     */
    public function debug(bool $consoleLog = false): Model
    {
        echo '<pre>';
        echo $this->query . $this->group . $this->order . $this->limit;
        exit;
    }


    /**
     * @return array|null
     */
    protected function safe(): ?array
    {
        $safe = (array) $this->data;
        foreach ($this->protected as $unset) {
            unset($safe[$unset]);
        }
        return $safe;
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        }
        return $filter;
    }

    /**
     * @return boolean
     */
    protected function required(): bool
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
