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
}
