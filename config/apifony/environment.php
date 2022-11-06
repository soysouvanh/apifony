<?php 
/**
 * Initialize apifony environment variables.
 */

// Configuration
$_ENV['CONFIG_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../config/apifony';

// BO
$_ENV['BO_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../src/Bo';
$_ENV['BO_FILE_EXTENSION'] = '.php';
$_ENV['BO_CLASS_SUFFIX'] = 'Bo';

// DAO
$_ENV['DAO_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../src/Dao';
$_ENV['DAO_FILE_EXTENSION'] = &$_ENV['BO_FILE_EXTENSION'];
$_ENV['DAO_CLASS_SUFFIX'] = 'Dao';

$_ENV['DAO_TEMPLATE_ROOT'] = $_ENV['DAO_ROOT'] . '/template';
$_ENV['DAO_TEMPLATE_FILE_EXTENSION'] = '.template.php';

// DS
$_ENV['DS_ROOT'] = $_ENV['DAO_ROOT'] . '/ds';

// Field
$_ENV['FIELD_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../src/Field';
$_ENV['FIELD_FILE_EXTENSION'] = '.field.php';

// Form
$_ENV['FORM_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../src/Form';
$_ENV['FORM_FILE_EXTENSION'] = '.form.php';

// Weaver
$_ENV['WEAVER_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . '/../src/Weaver';
$_ENV['WEAVER_FILE_EXTENSION'] = '.weaver.php';