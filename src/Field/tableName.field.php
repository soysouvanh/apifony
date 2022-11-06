<?php 
return [
	'label' => 'Nom de table',
	'title' => 'Table',
	'type' => 'text',
	'values' => null,
	'defaultValue' => null,
	'helpMessage' => 'Le nom de table doit uniquement être composé de caractères alphanumériques sans accent. Le caractère "_" est accepté. Un nom de table doit commencer par une lettre ou "_".',
	'placeholder' => 'Nom de table',
	'format' => [
		'value' => '/^[a-zA-Z_][a-zA-Z_\d-]{1,31}$/',
		'message' => 'Le nom de table est incorrect.'
	],
	'required' => [
		'value' => true,
		'message' => 'Le nom de table est obligatoire.'
	],
	'minLength' => [
		'value' => 1,
		'message' => 'Le nom de table est obligatoire.'
	],
	'maxLength' => [
		'value' => 32,
		'message' => 'Le nom de table est limité à 32 caractères.'
	],
	'messages' => [
		'TableNameNotFound' => 'Nom de table introuvable.'
	]
];