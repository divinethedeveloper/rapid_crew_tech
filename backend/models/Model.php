<?php

namespace Model;

use Middleware\Logger;
use Middleware\Middleware;

class Model extends Middleware
{
    private string $host;
    private string $username;
    private string $password;
    private string $database;
    private object $connection;

    private string $query_string = '';
    private string $query_conditions = '';
    private string $query_offsets = '';
    private string $query_table = '';
    private string $query_result_flag = 'all';
    private string $query_join_string = '';
    public  string $query_pre_select = '';

    public array $pre_query_parameters = [];
    public array $query_parameters = [];
    private array $subquery_parameters = [];
    private array $query_select_list = [];



    protected object $logger;

    public function __construct(string $host = 'localhost', string $username = 'rapidcre_rapid', string $password = 'rapid@2023@crew', string $database = 'rapidcre_rapid')
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $logger = new Logger();

        $this->logger = $logger->DBLogger();
    }

    protected function query(string $sql = '', array $parameters = [], bool $result = true)
    {
        try {
            $this->openConnection();

            $query = $this->connection->prepare($sql);

            $param_string = str_repeat('s', count($parameters));

            $parameters = $this->clean_array($parameters)[1]; // clean array returns [empty, array], [1] ensures only array is returned

            if (strlen($param_string) > 0) {
                $query->bind_param($param_string, ...$parameters);
            }

            $query->execute();

            if (!$result) {
                $this->closeConnection();

                return $this->resultEngine([]);
            }

            $results = $query->get_result();

            $results = mysqli_fetch_all($results, MYSQLI_ASSOC);

            $this->closeConnection();

            return $this->resultEngine($results);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    protected function fetch(string $table, array $select_list = ['*'], string $flag = 'all'): object
    {
        $this->clearQueryVariables();
        $this->query_table = $table;
        $this->query_select_list = $select_list;

        $this->query_result_flag = $flag;

        return $this;
    }

    protected function fetchTotal(string $table): object
    {
        return $this->fetch($table, ['count(*) as total'], flag: 'total');
    }

    protected function fetchSum(string $table, string $column): object
    {
        return $this->fetch($table, ["sum($column) as sum"], flag: 'sum');
    }

    protected function subFetch(string $as_col, string $sql, array $parameters = []): object
    {
        $sql = " ($sql)  as $as_col";

        array_push($this->query_select_list, $sql);
        array_push($this->subquery_parameters, ...$parameters);

        return $this;
    }

    protected function join(string $sql)
    {
        $this->query_join_string = $sql;

        return $this;
    }

    protected function clearQueryVariables()
    {
        $this->query_string = '';
        $this->query_conditions = '';
        $this->query_offsets = '';
        $this->query_table = '';
        $this->query_join_string = '';
        $this->query_pre_select = '';

        $this->pre_query_parameters = [];
        $this->query_parameters = [];
        $this->subquery_parameters = [];
        $this->query_select_list = [];
    }

    protected function condition(string $condition, ...$where): object
    {
        $where_size = count($where);
        if ($where_size < 2 or $where_size > 3) {
            throw new \Exception("condition function needs a minimum of two parameters and a maximum of three parameters $where_size given");
        } elseif ($where_size == 2) {
            $this->query_conditions .= " $condition {$where[0]} = ? ";
            array_push($this->query_parameters, $where[1]);
        } else {
            $this->query_conditions .= " $condition {$where[0]} {$where[1]} ? ";
            array_push($this->query_parameters, $where[2]);
        }

        return $this;
    }

    protected function where(...$where): object
    {
        return $this->condition('where', ...$where);
    }

    public function groupWhere($values, $where = false, $operator = '')
    {
        $this->query_conditions .= $where ? 'where (' : " $operator ( ";

        foreach ($values as $value) {
            $key = array_shift($value);

            if ($key == 'where') {
                $key = '';
            }
            $this->condition($key, ...$value);
        }

        $this->query_conditions .= ' )';

        return $this;
    }

    public function andWhere(...$where): object
    {
        return $this->condition('and', ...$where);
    }

    public function rawCondition($sql, $parameters = [])
    {
        // selects jobs that belong to the provided category
        $this->query_conditions .= " $sql ";

        if(count($parameters) > 0)
        array_push($this->query_parameters, $parameters[0]);

        return $this;
    }

    protected function orWhere(...$where): object
    {
        return $this->condition('or', ...$where);
    }

    protected function offset(int $offset): object
    {
        $this->query_offsets .= ' offset ? ';
        array_push($this->query_parameters, $offset);

        return $this;
    }

    protected function limit(int $limit): object
    {
        $this->query_offsets .= ' limit ? ';
        array_push($this->query_parameters, $limit);

        return $this;
    }

    protected function asc(string $column): object
    {
        $this->query_offsets .= " order by $column asc ";

        return $this;
    }

    protected function desc(string $column): object
    {
        $this->query_offsets .= " order by $column desc ";

        return $this;
    }

    public function paginate(int $per_page = 50): object
    {
        if (!isset($_REQUEST['page'])) {
            $page = 1;
        } else {
            $page = htmlspecialchars($_REQUEST['page']);
        }

        array_push($this->query_select_list, "(select count(*) from {$this->query_table} {$this->query_conditions}) as size");
        array_push($this->query_select_list, "ceil( (select count(*) from {$this->query_table} {$this->query_conditions}) / $per_page) as page_count");

        $this->query_parameters = array_merge($this->query_parameters, $this->query_parameters, $this->query_parameters);

        $this->limit($per_page);
        $this->offset(($page - 1) * $per_page);

        return $this;
    }

    protected function getDistance(float $lat = null, float $lng = null): object
    {
        [$empty, $lat] = $this->clean($_REQUEST['lat'] ?? 0);
        [$empty, $lng] = $this->clean($_REQUEST['lng'] ?? 0);

        array_push($this->query_select_list, "(6371 * acos(cos(radians($lat)) * cos(radians(lat)) * cos(radians(lng) - radians($lng)) + sin(radians($lat)) * sin(radians(lat)))) AS distance");

        return $this;
    }

    public function execute(bool $result = true)
    {
        // convert select_list to 'item1, item2 as'

        $modified_select_list = array_map(function ($item) {
            if (str_replace(' ', '', $item) === '*') {
                return "{$this->query_table}.$item";
            }

            return $item;
        }, $this->query_select_list);

        $this->query_select_list = $modified_select_list;

        $selects = implode(', ', $this->query_select_list);

        $this->query_string = "{$this->query_pre_select} select $selects from {$this->query_table} {$this->query_join_string} {$this->query_conditions} {$this->query_offsets}";

        if (!$result) {
            $this->error($this->query_string);
        }

        $this->query_parameters = array_merge($this->pre_query_parameters, $this->subquery_parameters, $this->query_parameters);

        return $this->query($this->query_string, $this->query_parameters, $result);
    }

    private function openConnection(): void
    {
        try {
            $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);

            if ($this->connection->connect_error) {
                $logger->error('Unable to connect to the database');
            }
        } catch (exception $e) {
            $this->logger($e->getMessage());
        }
    }

    private function closeConnection(): void
    {
        try {
            if(isset($tag->connection->affected_rows))
            $this->connection->close();
        }
        catch(\Exception $e) {

        }
    }

    public function resultEngine($result)
    {
        if ($this->query_result_flag === 'total') {
            $result = $result[0]['total'];
        }
        if ($this->query_result_flag === 'sum') {
            $result = $result[0]['sum'];
        }

        return $result;
    }

    public function insert($sql, $parameters, $callback = null)
    {
        $this->openConnection();

        $query = $this->connection->prepare($sql);

        $param_string = str_repeat('s', count($parameters));
        // $parameters = $this->clean_array($parameters)[1]; // clean array returns [empty, array], [1] ensures only array is returned

        $query->bind_param($param_string, ...$parameters);

        $query->execute();

        $inserted_id = $this->connection->insert_id ?? 0;
        if ($callback) {
            $callback($inserted_id);
        }

        $this->closeConnection();
    }
}