<?php

namespace App\Exception\CustomerProject;

use Exception;

/**
 * Triggered when creating project item and there is a variant coming
 * from the scanner with doesn't exist in the internal database
 */
class VariantsInconsistencyWithPlumScannerException extends Exception
{
}
