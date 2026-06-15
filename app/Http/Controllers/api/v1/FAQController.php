<?php

declare(strict_types=1);

namespace App\Http\Controllers\api\v1;

use App\Models\FAQ;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'FAQ', description: 'Frequently asked questions')]
#[OA\Schema(
    schema: 'FaqObject',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'question', type: 'string', example: 'How do I reset my password?'),
        new OA\Property(property: 'answer', type: 'string', example: 'Click on forgot password on the login screen.'),
        new OA\Property(property: 'status', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object'
)]
class FAQController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/auth/show-faqs',
        operationId: 'showFAQ',
        summary: 'Get all FAQs',
        description: 'Returns a list of all FAQs. Requires Bearer token.',
        security: [['sanctum' => []]],
        tags: ['FAQ'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'FAQs fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'faqs fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/FaqObject')
                        ),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function showFAQ()
    {
        $faqs = FAQ::get();

        return $this->sendResponse($faqs, 'faqs fetched successfully');

    }
}
