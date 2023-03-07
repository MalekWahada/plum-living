<?php

namespace App\Exception\CustomerProject;

use Exception;

/**
 * Triggered when trying to perform an action on a plum Scanner project with unloaded items
 */
class UnfetchedProjectException extends Exception
{
}
