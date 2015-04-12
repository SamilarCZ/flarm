<?php

namespace ActiveRecord;

class Helper extends Model
{

    public function addSpaces($str)
    {
        return ' ' . $str . ' ';
    }

    /*
       $array[0]['cond']['row'] = ' ' . $clause['row'] . ' ';
       $array[0]['cond']['val'] = ' ' . $clause['val'] . ' ';
       $array[0]['cond']['operator'] = ' ' . $clause['operator'] . ' ';
       $array[0]['glue'] = ' ' . $clause['glue'] . ' ';
    */
    public function where($where)
    {
        $conn = ConnectionManager::get_connection("development");
        $builder = new SQLBuilder($conn, $this->table_name());
        $utils = new Utils();
        if (count($where) > 0) {
            foreach ($where as $clause) {
                foreach ($clause as $key => $value) {
                    $builder->where($clause);
                    $utils->add_condition($clause);
                }
            }
        }
    }

    public function whereAnd()
    {

    }

    public function order()
    {

    }

    public function limit()
    {

    }

    public function groupBy()
    {

    }

}