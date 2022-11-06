<?php 
return [
    // Check expected environment: pass on dev only
    [
        'instance' => &$this,
        'method' => 'checkExpectedEnvironment',
        'arguments' => [
            'expectedEnvironment' => 'dev'
        ],
        'callbackBefore' => null,
		'callbackAfter' => null
    ],

    // Check parameters
    [
        'instance' => &$this,
        'method' => 'checkParameters',
        'arguments' => [
            'data' => &$this->parameters
        ],
        'callbackBefore' => function() {
            // Set tableName parameter optional
            $this->formData['tableName']['required']['value'] = false;
        },
		'callbackAfter' => null
    ],
    
    // Check business logic
    [
        'instance' => &$this->bo,
        'method' => 'generateCheck',
        'arguments' => null,
        'callbackBefore' => null,
		'callbackAfter' => null
    ],
    
    // Generate data source DAO
    [
        'instance' => &$this->bo,
        'method' => 'generate',
        'arguments' => null,
        'callbackBefore' => null,
		'callbackAfter' => null
    ]
];