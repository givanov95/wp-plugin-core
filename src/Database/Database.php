<?php

namespace WpPluginCore\Database;

class Database
{
    protected string $table;

    public function __construct(string $table)
    {
        global $wpdb;
        $this->table = $wpdb->prefix . $table;
    }

    public function insert(array $data): int
    {
        global $wpdb;

        $formats = $this->detectFormats($data);

        if ($wpdb->insert($this->table, $data, $formats) === false) {
            throw new \Exception($wpdb->last_error);
        }

        return $wpdb->insert_id;
    }

    public function update(array $data, array $where): bool
    {
        global $wpdb;

        $dataFormats = $this->detectFormats($data);
        $whereFormats = $this->detectFormats($where);

        return (bool)$wpdb->update(
            $this->table,
            $data,
            $where,
            $dataFormats,
            $whereFormats
        );
    }

    public function where(array $conditions): array
    {
        global $wpdb;

        [$sql, $values] = $this->buildWhere($conditions);

        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE {$sql}", ...$values),
            ARRAY_A
        );
    }

    private function detectFormats(array $data): array
    {
        return array_map(fn ($v) =>
            is_int($v) || is_bool($v) ? '%d' :
            (is_float($v) ? '%f' : '%s'), $data);
    }

    private function buildWhere(array $conditions): array
    {
        $sql = [];
        $values = [];

        foreach ($conditions as $col => $value) {
            $sql[] = "{$col} = %s";
            $values[] = $value;
        }

        return [implode(" AND ", $sql), $values];
    }

    public function paginate(
        array $conditions,
        int $page = 1,
        int $perPage = 10,
        ?string $orderBy = null
    ): array {
        global $wpdb;

        [$sql, $values] = $this->buildWhere($conditions);

        $offset = ($page - 1) * $perPage;

        $whereClause = $sql ? "WHERE {$sql}" : '';

        if ($orderBy === null) {
            $orderBy = "ORDER BY id DESC";
        }

        $query = "SELECT * FROM {$this->table} {$whereClause} {$orderBy} LIMIT %d OFFSET %d";

        $values[] = $perPage;
        $values[] = $offset;

        $prepared = $wpdb->prepare($query, ...$values);

        return $wpdb->get_results($prepared, ARRAY_A);
    }


    public function count(array $conditions = []): int
    {
        global $wpdb;

        if (empty($conditions)) {
            $query = "SELECT COUNT(*) FROM {$this->table}";
            return (int)$wpdb->get_var($query);
        }

        [$sql, $values] = $this->buildWhere($conditions);

        $query = "SELECT COUNT(*) FROM {$this->table} WHERE {$sql}";

        $prepared = $wpdb->prepare($query, ...$values);

        return (int)$wpdb->get_var($prepared);
    }
}
