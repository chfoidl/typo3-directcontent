<?php

namespace Sethorax\DirectContent\Utility;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Utility
{
	public static function getMajorTYPO3Version(): int
	{
		return (int)substr(VersionNumberUtility::getNumericTypo3Version(),0, 1);
	}
}
