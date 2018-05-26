<?php

if (!defined('_PS_VERSION_'))
    exit;

class SwExtraImage extends ObjectModel {

    public $id;
    public $id_product;
    public $url;
    public $src;
    public $alt;
    public $date_add;
    public $date_update;

    public static $definition = array(
        'table' => 'swextraprodimg',
        'primary' => 'id_image',
        'fields' => array(
            'id_product' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'url' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'src' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'alt' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_update' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function __construct($id = null) {
        parent::__construct($id);
    }

    public static function getAll() {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'swextraprodimg';
        $result = DB::getInstance()->executeS($sql);
        return $result;
    }

    public static function getByProductId($id_product) {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'swextraprodimg WHERE id_product LIKE "%,' . $id_product . ',%"';
        $result = DB::getInstance()->executeS($sql);
        return $result;
    }

}
