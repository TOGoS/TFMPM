<?php

global $PHPTemplateProjectNS_Registry;
require __DIR__.'/init-environment.php';
$PHPTemplateProjectNS_Registry = $PHPTemplateProjectNS_Registry->withNamedSchema('test');
