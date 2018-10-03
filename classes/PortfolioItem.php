<?php

/*
 * A class for an individual Portfolio Item
 */

class PortfolioItem {
    private $_db;

    public function __construct() {
        $this->_db = DB::getInstance();
    }
    public static function create() {

    }

    public static function update() {

    }

    public function getSingle($id = null) {
        $query = $this
            ->_db
            ->select('portfolio_items')
            ->where(array('id', '=', $id))
            ->get();

        if ($query->count() > 0) {
            return $query->first();
        }

        return false;
    }

    public function getAll() {
        $allItems = $this
            ->_db
            ->select('portfolio_items')
            ->get();

        if ($allItems->count() > 0) {
            return $allItems->results();
        }

        return false;
    }

    public static function delete() {

    }
}