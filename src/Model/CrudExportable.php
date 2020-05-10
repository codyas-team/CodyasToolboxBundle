<?php

namespace Codyas\Toolbox\Model;


interface CrudExportable
{

	public static function supportsExportFor( $format ): bool;

	public static function getSupportedExportFormats(): array;

}