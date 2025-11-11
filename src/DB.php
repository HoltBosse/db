<?php
namespace HoltBosse\DB;

use PDO;
use Exception;
use PDOException;

class DB {
	private static ?DB $instance = null;
	private static Bool $forceTypes = false;
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

	/* 
		this exists solely for terrible hosting environments that don't handle types properly
		this is not officially supported and may be removed or changed in future versions
	*/
	public static function forceTypes(Bool $force): void {
		self::$forceTypes = $force;
	}

	private static function enforceTypes(mixed $input): mixed {
		$inputType = gettype($input);
		if($inputType === "array" || $inputType === "object") {
			// @phpstan-ignore-next-line
			foreach($input as $key => $value) {
				if(is_numeric($value)) {
					if(strval(intval($value)) === strval($value)) {
						if($inputType === "array") {
							// @phpstan-ignore-next-line
							$input[$key] = intval($value);
						} else {
							$input->$key = intval($value);
						}
					} else {
						if($inputType === "array") {
							// @phpstan-ignore-next-line
							$input[$key] = floatval($value);
						} else {
							$input->$key = floatval($value);
						}
					}
				} elseif(gettype($value) === "array" || gettype($value) === "object") {
					if($inputType === "array") {
						// @phpstan-ignore-next-line
						$input[$key] = self::enforceTypes($value);
					} else {
						$input->$key = self::enforceTypes($value);
					}
				}
			}
			return $input;
		} else {
			return $input;
		}
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
		$result = $stmt->fetch($options["mode"] ?? PDO::FETCH_OBJ);
		if(self::$forceTypes && $result !== false) {
			$result = self::enforceTypes($result);
		}
		return $result;
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
		$result = $stmt->fetchAll($options["mode"] ?? PDO::FETCH_OBJ);
		if(self::$forceTypes) {
			$result = self::enforceTypes($result);
		}
		return $result;
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