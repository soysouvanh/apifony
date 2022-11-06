<?php 
return [
    // Authenticate client
    [
        'instance' => &$this,
        'method' => 'authenticate',
        'arguments' => null,
        'callbackBefore' => null,
		'callbackAfter' => null
    ],

    // Check client service permission
    [
        'instance' => &$this,
        'method' => 'checkServicePermission',
        'arguments' => null,
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
        'callbackBefore' => null,
		'callbackAfter' => null
    ],
    
    // Begin transaction
    [
        'instance' => &$this,
        'method' => 'beginTransaction',
        'arguments' => [
            'dataSourceName' => 'mysql'
        ],
        'callbackBefore' => null,
		'callbackAfter' => null
    ],

    // Check business logic
    [
        'instance' => &$this->bo,
        'method' => 'getParametersCheck',
        'arguments' => null,
        'callbackBefore' => null,
		'callbackAfter' => null
    ],
    
    // Return parameters in the response
    [
        'instance' => &$this->bo,
        'method' => 'getParameters',
        'arguments' => null,
        'callbackBefore' => null,
		'callbackAfter' => null
    ]
];