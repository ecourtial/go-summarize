<?php

declare(strict_types=1);

namespace App\Api\User;

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactoryData
{
    public function addData(OpenApi $openApi): void
    {
        $openApi->getPaths()->addPath(
            '/api/login',
            new PathItem(
                post: new Operation(
                    tags: ['User'],
                    responses: $this->getAuthenticationResponses(),
                    summary: 'Endpoint to authenticate a user.',
                    description: 'Submit login and password to get the user API token.',
                    requestBody: $this->getAuthenticationRequestBody(),
                ),
            )
        );
    }

    private function getAuthenticationRequestBody(): RequestBody
    {
        $schema = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'description' => 'The user identifier (the email).',
                ],
                'password' => [
                    'type' => 'string',
                    'description' => 'The user password.',
                ],
            ],
            'required' => ['email', 'password'],
        ]);

        return new RequestBody(
            description: 'Login',
            content: new \ArrayObject([
                new MediaType(schema: $schema),
            ]),
            required: true,
        );
    }

    /**
     * @return array<Response>
     */
    private function getAuthenticationResponses(): array
    {
        $okSchema = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'string', 'description' => 'The user ID.'],
                'user' => ['type' => 'string', 'description' => 'The user identified.'],
                'email' => ['type' => 'string', 'description' => 'The user email.'],
                'token' => ['type' => 'string', 'description' => 'The user token.'],
            ],
        ]);

        return [
            '200' => new Response(
                description: 'User is authenticated.',
                content: new \ArrayObject([
                    'application/json' => new MediaType(schema: $okSchema),
                ]),
            ),
            '400' => new Response(description: 'Invalid request - usually invalid JSON submitted.'),
            '401' => new Response(description: 'Authentication required or invalid credentials'),
        ];
    }
}
