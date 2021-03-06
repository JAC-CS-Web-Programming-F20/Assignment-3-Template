<?php

namespace AssignmentThree\Exceptions;

use Exception;

class PostException extends Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
		error_log($this);
	}

	public function __toString()
	{
		return __CLASS__ . ": {$this->message}\n{$this->getFile()} @ Line {$this->getLine()}\n";
	}
}
