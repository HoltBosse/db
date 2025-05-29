<?php
namespace HoltBosse\DB;

use PDO;
use Exception;
use PDOException;

class DB {
	private static ?DB $instance = null;
	private PDO $pdo;

	public function __construct(string $dsn, string $username, string $password) {
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		$this->pdo = new PDO($dsn, $username, $password, $options);
	}

	/** @phpstan-ignore missingType.parameter */
	public static function createInstance(...$args): bool {
		if (self::$instance === null) {
			/** @phpstan-ignore argument.type */
			self::$instance = new DB(...$args);
			return true;
		}

		return false;
	}

	public final static function getInstance(): DB {
		if (self::$instance === null) {
			throw new Exception("HoltBosse\DB\DB Instance not created!!!");
		}
		return self::$instance;
	}

	public final function getPdo(): PDO {
		return $this->pdo;
	}

	/**
		* @param array<string, mixed> $options
	*/ 
	public static function fetch(string $query, mixed $paramsarray=[], array $options=[]): mixed {
		$classInstance = DB::getInstance();

		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

		$stmt = $classInstance->getPdo()->prepare($query);
		$stmt->execute($paramsarray);
		/** @phpstan-ignore argument.type */
		return $stmt->fetch($options["mode"] ?? PDO::FETCH_OBJ);
	}

	/**
		* @param array<string, mixed> $options
	*/ 
	public static function fetchAll(string $query, mixed $paramsarray=[], array $options=[]): mixed {
		$classInstance = DB::getInstance();

		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

		$stmt = $classInstance->getPdo()->prepare($query);
		$stmt->execute($paramsarray);
		/** @phpstan-ignore argument.type */
		return $stmt->fetchAll($options["mode"] ?? PDO::FETCH_OBJ);
	}

	public static function exec(string $query, mixed $paramsarray=[]): bool {
		$classInstance = DB::getInstance();

		if (!is_array($paramsarray)) {
			$paramsarray = [$paramsarray];
		}

		$stmt = $classInstance->getPdo()->prepare($query);
		return $stmt->execute($paramsarray);
	}

	public static function getLastInsertedId(): string|false {
		$classInstance = DB::getInstance();

		return $classInstance->getPdo()->lastInsertId();
	}
}