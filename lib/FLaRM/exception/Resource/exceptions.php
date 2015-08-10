<?php

	namespace App\exception\Resource;

	interface Exception {
	}

	class InvalidArgumentException extends \InvalidArgumentException implements Exception {
	}

	class InvalidStateException extends \RuntimeException implements Exception {
	}

	class InvalidResourceException extends \UnexpectedValueException implements Exception {
	}

	class LoaderNotFoundException extends \RuntimeException implements Exception {
	}
