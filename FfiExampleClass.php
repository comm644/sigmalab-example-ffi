<?php
declare(strict_types=1);

namespace Sigmalab\ExampleFFI;

/**
 * This class provides example (stub) with basic demonstration how
 * to use FFI with callbacks.
 *
 * @license MIT
 * @author Alexey V. Vasilyev, 2023
 * @copyright (c) 2023, Alexey V. Vasilyev, Sigmalab LLC.
 */
class FfiExampleClass
{
	/** @var int */
	private static int $instanceIndex = 0;
	/** @var self[] */
	private static array $instances = [];
	private int $instanceId = 0;

	/** @var ffi_scope<libfeature>
	 * @noinspection KphpUndefinedClassInspection
	 */
	private $lib;

	/** @var callable(self,string):void */
	private $processWithCallback_handler;

	public function __construct()
	{
		self::loadFFI();
		/** @noinspection KphpAssignmentTypeMismatchInspection */
		$this->lib = \FFI::scope("libfeature");
		if ($this->lib === null) {
			throw new \RuntimeException("Can't load libfeature");
		}

		self::$instances[self::$instanceIndex] = $this;
		//always increase instances
		$this->instanceId = self::$instanceIndex++;
	}

	public static function loadFFI(): bool
	{
		//FIXME: replace to real header.
		// Prepare header:
		// ```
		//  g++ -E /path/to/feature.h | grep -vE "^#" > feature-ffi.h
		// ```

		return \FFI::load(__DIR__ . '/feature-ffi.h') !== null;
	}

	/** Dispose class.
	 * You must run this method when need destroy class.
	 * For details see: MSDN IDisposable.Dispose
	 */
	public function dispose(): void
	{
		//dispose instance, yes can be holes in instances.
		unset(self::$instances[$this->instanceId]);

		//FIXME: free allocated memory. close resources.
	}

	/** Example method how to use callback functions
	 *
	 * @param string $data in-param for library function
	 * @param callable(self, string) $callback our callback method
	 * @return void
	 */
	public function processWithCallback(string $data, callable $callback): void
	{
		//store received callback in class property.
		$this->processWithCallback_handler = $callback;

		//Define userData as transfer container and store to it 'instanceId'
		$userData = $this->newUserData();

		//call library function
		$this->lib->processWithCallback($userData, $data,
			function ($userData, $payload) {
				//decode userdata to instance.
				$self = self::getSelf($userData);
				$handler = $self->processWithCallback_handler;
				$handler($self, (string)$payload);
			});

		//free userData
		$this->freeUserData($userData);
	}

#region Instance management methods

	/** Decode userData to instance
	 * @param ffi_cdata<C, const void*> $ctx
	 * @return self
	 * @noinspection KphpUndefinedClassInspection
	 * @noinspection KphpDocInspection
	 * @noinspection NoTypeDeclarationInspection
	 */
	private static function getSelf($ctx): self
	{
		$userData = \FFI::cast("int32_t*", $ctx);

		/** @noinspection NullPointerExceptionInspection */
		/** @var int $instanceIdx */
		$instanceIdx = ffi_array_get($userData, 0);

		/** @var self $self */
		$self = self::$instances[$instanceIdx];
		return $self;
	}

	/** Define userData as transfer container and store to it 'instanceId'
	 * @return ffi_cdata<C, void*>
	 */
	private function newUserData()
	{
		/** @var ffi_cdata<C,int[1]>
		 * @noinspection KphpUndefinedClassInspection
		 */
		$userData = \FFI::new('int[1]', false);
		ffi_array_set($userData, 0, $this->instanceId);
		return \FFI::cast("void*", $userData);
	}

	/**
	 * @param ffi_cdata<C, void*> $userData
	 * @return void
	 * @noinspection KphpUndefinedClassInspection
	 */
	private function freeUserData($userData): void
	{
		$allocated = \FFI::cast('int[1]', $userData);
		//destroy created userData.
		\FFI::free($allocated);
	}

#endregion
}