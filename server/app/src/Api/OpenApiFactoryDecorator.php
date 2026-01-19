<?php

declare(strict_types=1);

namespace App\Api;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use App\Api\User\OpenApiFactoryData;

class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    /** @TODO we could use tagged services instead of injecting all of them here */
    public function __construct(
        private OpenApiFactoryInterface $decorated,
        private readonly OpenApiFactoryData $userApiDocumentationFactoryData,
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        // Add extra data from other modules
        $this->userApiDocumentationFactoryData->addData($openApi);

        return $openApi;
    }
}
