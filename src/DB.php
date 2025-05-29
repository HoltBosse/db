<?php
namespace HoltBosse\DB;

use PDO;
use Exception;
use PDOException;

class DB {
    private static $instance = null;
    private $pdo;

    public function __construct(string $dsn, string $username, string $password) {
        $options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    public static function createInstance(...$args) {
        if (self::$instance === null) {
            self::$instance = new DB(...$args);
            return true;
        }

        return false;
    }

    public final static function getInstance() {
        if (self::$instance === null) {
			throw new Exception("HoltBosse\DB\DB Instance not created!!!");
		}
		return self::$instance;
    }

    public final function getPdo(): PDO {
        return $this->pdo;
    }

    public static function fetch(string $query, $paramsarray=[], array $options=[]): object|array {
        $classInstance = DB::getInstance();

        if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

        $stmt = $classInstance->getPdo()->prepare($query);
        $stmt->execute($paramsarray);
        return $stmt->fetch($options["mode"] ?? PDO::FETCH_OBJ);
    }

    public static function fetchAll(string $query, $paramsarray=[], array $options=[]): object|array {
        $classInstance = DB::getInstance();

        if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

        $stmt = $classInstance->getPdo()->prepare($query);
        $stmt->execute($paramsarray);
        return $stmt->fetchAll($options["mode"] ?? PDO::FETCH_OBJ);
    }

    public static function exec(string $query, $paramsarray=[]) {
        $classInstance = DB::getInstance();

        if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

        $stmt = $classInstance->getPdo()->prepare($query);
		return $stmt->execute($paramsarray);
    }

    public static function getLastInsertedId() {
        $classInstance = DB::getInstance();

        return $classInstance->getPdo()->lastInsertId();
    }
}