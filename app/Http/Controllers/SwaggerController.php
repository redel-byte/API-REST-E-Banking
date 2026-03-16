<?php

namespace App\Http\Controllers;

use OpenApi\Generator;
use OpenApi\Annotations as OA;

/**
 * @OA\PathItem(
 *     path="/api/docs/json",
 *     summary="Generate OpenAPI documentation",
 *     description="Generate JSON OpenAPI specification from controller annotations"
 * )
 */
class SwaggerController extends Controller
{
    /**
     * @OA\Info(
     *     version="1.0.0",
     *     title="Laravel JWT API Documentation",
     *     description="API documentation for Laravel JWT Authentication System",
     *     @OA\Contact(
     *         name="API Support",
     *         email="support@example.com"
     *     )
     * )
     * @OA\Server(
     *     url="http://localhost:8000",
     *     description="API Server"
     * )
     * @OA\SecurityScheme(
     *     type="http",
     *     description="JWT Bearer token authentication",
     *     name="Bearer",
     *     in="header",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     securityScheme="api_key"
     * )
     */
    public function generate()
    {
        try {
            // Create a minimal OpenAPI specification manually first
            $openapi = [
                'openapi' => '3.0.0',
                'info' => [
                    'title' => 'Laravel JWT API Documentation',
                    'version' => '1.0.0',
                    'description' => 'API documentation for Laravel JWT Authentication System'
                ],
                'servers' => [
                    [
                        'url' => 'http://localhost:8000',
                        'description' => 'API Server'
                    ]
                ],
                'paths' => [
                    '/api/register' => [
                        'post' => [
                            'tags' => ['Authentication'],
                            'summary' => 'Register a new user',
                            'description' => 'Create a new user account and return user data',
                            'requestBody' => [
                                'required' => true,
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'required' => ['name', 'email', 'password'],
                                            'properties' => [
                                                'name' => [
                                                    'type' => 'string',
                                                    'example' => 'John Doe'
                                                ],
                                                'email' => [
                                                    'type' => 'string',
                                                    'format' => 'email',
                                                    'example' => 'john@example.com'
                                                ],
                                                'password' => [
                                                    'type' => 'string',
                                                    'format' => 'password',
                                                    'example' => 'password123'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'responses' => [
                                '201' => [
                                    'description' => 'User registered successfully',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'Account created successfully'
                                                    ],
                                                    'user' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'id' => ['type' => 'integer', 'example' => 1],
                                                            'name' => ['type' => 'string', 'example' => 'John Doe'],
                                                            'email' => ['type' => 'string', 'example' => 'john@example.com']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                '422' => [
                                    'description' => 'Validation error',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'The given data was invalid.'
                                                    ],
                                                    'errors' => [
                                                        'type' => 'object',
                                                        'example' => [
                                                            'name' => ['The name field is required.'],
                                                            'email' => ['The email field is required.'],
                                                            'password' => ['The password field is required.']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '/api/login' => [
                        'post' => [
                            'tags' => ['Authentication'],
                            'summary' => 'Login user and get JWT token',
                            'description' => 'Authenticate user credentials and return JWT token for API access',
                            'requestBody' => [
                                'required' => true,
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'required' => ['email', 'password'],
                                            'properties' => [
                                                'email' => [
                                                    'type' => 'string',
                                                    'format' => 'email',
                                                    'example' => 'john@example.com'
                                                ],
                                                'password' => [
                                                    'type' => 'string',
                                                    'format' => 'password',
                                                    'example' => 'password123'
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            'responses' => [
                                '200' => [
                                    'description' => 'Login successful',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'Login successfully'
                                                    ],
                                                    'token' => [
                                                        'type' => 'string',
                                                        'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                '401' => [
                                    'description' => 'Invalid credentials',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'Login failed'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                '422' => [
                                    'description' => 'Validation error',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'The given data was invalid.'
                                                    ],
                                                    'errors' => [
                                                        'type' => 'object',
                                                        'example' => [
                                                            'email' => ['The email field is required.'],
                                                            'password' => ['The password field is required.']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '/api/me' => [
                        'get' => [
                            'tags' => ['User Profile'],
                            'summary' => 'Get current user profile',
                            'description' => 'Retrieve the authenticated user\'s profile information',
                            'security' => [['api_key' => []]],
                            'responses' => [
                                '200' => [
                                    'description' => 'User profile retrieved successfully',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer', 'example' => 1],
                                                    'name' => ['type' => 'string', 'example' => 'John Doe'],
                                                    'email' => ['type' => 'string', 'example' => 'john@example.com']
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                '401' => [
                                    'description' => 'Unauthenticated - Invalid or missing token',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'Unauthenticated.'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '/api/logout' => [
                        'post' => [
                            'tags' => ['Authentication'],
                            'summary' => 'Logout user and invalidate token',
                            'description' => 'Invalidate the current JWT token and logout the user',
                            'security' => [['api_key' => []]],
                            'responses' => [
                                '200' => [
                                    'description' => 'Logout successful',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'logout successfully'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                '401' => [
                                    'description' => 'Unauthenticated - Invalid or missing token',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'message' => [
                                                        'type' => 'string',
                                                        'example' => 'Unauthenticated.'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'components' => [
                    'securitySchemes' => [
                        'api_key' => [
                            'type' => 'http',
                            'description' => 'JWT Bearer token authentication',
                            'scheme' => 'bearer',
                            'bearerFormat' => 'JWT'
                        ]
                    ]
                ]
            ];
            
            return response()->json($openapi);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate OpenAPI documentation',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    /**
     * Simple test method to verify the controller is working
     */
    public function test()
    {
        return response()->json(['message' => 'Swagger controller is working!']);
    }
    
    /**
     * Serve the Swagger UI interface
     */
    public function ui()
    {
        // Simple HTML for Swagger UI
        $html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laravel JWT API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui.css" />
    <style>
        html { box-sizing: border-box; overflow: -moz-scrollbars-vertical; overflow-y: scroll; }
        *, *:before, *:after { box-sizing: inherit; }
        body { margin:0; background: #fafafa; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/api/docs/json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>';
        
        return response($html);
    }
}
