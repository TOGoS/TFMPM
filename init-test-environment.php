<?php

global $TFMPM_Registry;
require __DIR__.'/init-environment.php';
$TFMPM_Registry = $TFMPM_Registry->withNamedSchema('test');
