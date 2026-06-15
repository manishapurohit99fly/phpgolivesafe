<?php

declare(strict_types=1);

namespace App\Http\Controllers\api\v1;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        version: '1.0.0',
        description: 'REST API for AI Assessment & Case Journaling platform. Consumed by iOS/Android mobile clients.',
        title: 'AI Assessment & Case Journaling API',
        contact: new OA\Contact(email: 'admin@example.com')
    ),
    servers: [
        new OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'API Server'),
    ]
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter the Bearer token returned from login/signup. Example: **Bearer 1|abc123**'
)]
#[OA\Tag(name: 'Authentication', description: 'User registration, login, logout and password management')]
#[OA\Schema(
    schema: 'SuccessResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operation successful.'),
        new OA\Property(property: 'data', type: 'object'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Something went wrong.'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string')),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(
            property: 'error',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            )
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'UserObject',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'first_name', type: 'string', example: 'John'),
        new OA\Property(property: 'last_name', type: 'string', example: 'Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'dob', type: 'string', format: 'date', example: '1990-01-15'),
        new OA\Property(property: 'profile_photo', type: 'string', nullable: true, example: 'http://localhost/uploads/users/photo.jpg'),
        new OA\Property(property: 'phone_no', type: 'string', nullable: true, example: '+1234567890'),
        new OA\Property(property: 'role', type: 'string', example: 'User'),
        new OA\Property(property: 'status', type: 'integer', example: 1),
        new OA\Property(property: 'device_type', type: 'string', enum: ['android', 'ios'], example: 'android'),
        new OA\Property(property: 'device_id', type: 'string', nullable: true, example: 'fcm_token_here'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'AuthData',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/UserObject'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'token', type: 'string', example: '1|abcdefghijk1234567890'),
            ],
            type: 'object'
        ),
    ]
)]
class SwaggerAnnotations
{
}
