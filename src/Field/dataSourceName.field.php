<?php 
return [
	'label' => 'Nom de source de données',
	'title' => 'Source de données',
	'type' => 'text',
	'values' => null,
	'defaultValue' => null,
	'helpMessage' => 'Le nom de source de données doit uniquement être composé de caractères alphanumériques sans accent. Le caractère "_" est accepté. Le nom de source de données doit commencer par une lettre ou "_".',
	'placeholder' => 'Nom de source de données',
	'format' => [
		'value' => '/^[a-zA-Z_][a-zA-Z_\d-]{1,31}$/',
		'message' => 'Le nom de source de données est incorrect.'
	],
	'required' => [
		'value' => true,
		'message' => 'Le nom de source de données est obligatoire.'
	],
	'minLength' => [
		'value' => 1,
		'message' => 'Le nom de source de données est obligatoire.'
	],
	'maxLength' => [
		'value' => 32,
		'message' => 'Le nom de source de données est limité à 32 caractères.'
	],
	'messages' => [
		'DataSourceNameNotFound' => 'Nom de source de données introuvable.',
		'DataSourceEmpty' => 'Source de données vide.',
	]
];