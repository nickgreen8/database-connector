<?php
namespace N8G\Database\Exceptions;

use N8G\Utils\Log;

/**
 * This is an abstract class that should be extended by all exceptions. The class extends the
 * PHP exception class. The class contains generic functionality that is required in the custom
 * exceptions created should be placed in here and can be overloaded in any child classes.
 *
 * @author Nick Green <nick-green@live.co.uk>
 */
abstract class ExceptionAbstract extends \Exception
{
	/**
	 * Default custom exception constructor
	 *
	 * @param string         $message  A message to embed in the exception
	 * @param integer        $code     A user defined error
	 * @param Exception|null $previous A previous exception that has caused this exception
	 */
	public function __construct($message, $code = -1, Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	/**
	 * This function logs the errors in the relevent log. This means that the behaviour of the
	 * system can be monitored. Any errors that are thrown up can be investigated quickly and
	 * easily.
	 *
	 * @return void
	 */
	protected function log()
	{
		//Write to log
		$message = sprintf('Exception Thrown:  %s%s        Message:  %s%s        File:     %s%s        Line:     %s%s        Trace:    %s',
							str_replace('Exceptions\\', '', get_class($this)),
							Log::LOG_SPACING,
							$this->getMessage(),
							Log::LOG_SPACING,
							$this->getFile(),
							Log::LOG_SPACING,
							$this->getLine(),
							Log::LOG_SPACING,
							$this->getTraceAsString());

		switch ($this->getCode()) {
			case Log::FATAL :
				Log::fatal($message);
				break;
			case Log::ERROR :
				Log::error($message);
				break;
			case Log::WARN :
				Log::warn($message);
				break;
			case Log::NOTICE :
				Log::notice($message);
				break;
			case Log::INFO :
				Log::info($message);
				break;
			case Log::DEBUG :
				Log::debug($message);
				break;
		}
	}
}