<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2024. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Controllers;

use App\Events\Resource\ResourceWasCreated;
use App\Events\Resource\ResourceWasUpdated;
use App\Factory\ResourceFactory;
use App\Filters\ResourceFilters;
use App\Http\Requests\Resource\BulkResourceRequest;
use App\Http\Requests\Resource\CreateResourceRequest;
use App\Http\Requests\Resource\DestroyResourceRequest;
use App\Http\Requests\Resource\EditResourceRequest;
use App\Http\Requests\Resource\ShowResourceRequest;
use App\Http\Requests\Resource\StoreResourceRequest;
use App\Http\Requests\Resource\UpdateResourceRequest;
use App\Http\Requests\Resource\UploadResourceRequest;
use App\Models\Account;
use App\Models\Resource;
use App\Repositories\ResourceRepository;
use App\Transformers\ResourceTransformer;
use App\Utils\Ninja;
use App\Utils\Traits\BulkOptions;
use App\Utils\Traits\MakesHash;
use App\Utils\Traits\SavesDocuments;
use App\Utils\Traits\Uploadable;
use Illuminate\Http\Response;

/**
 * Class ResourceController.
 * @covers App\Http\Controllers\ResourceController
 */
class ResourceController extends BaseController
{
    use MakesHash;
    use Uploadable;
    use BulkOptions;
    use SavesDocuments;

    protected $entity_type = Resource::class;

    protected $entity_transformer = ResourceTransformer::class;

    /**
     * @var ResourceRepository
     */
    protected $resource_repo;

    /**
     * ResourceController constructor.
     * @param ResourceRepository $resource_repo
     */
    public function __construct(ResourceRepository $resource_repo)
    {
        parent::__construct();

        $this->resource_repo = $resource_repo;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/resources",
     *      operationId="getResources",
     *      tags={"resources"},
     *      summary="Gets a list of resources",
     *      description="Lists resources, search and filters allow fine grained lists to be generated.

     *      Query parameters can be added to performed more fine grained filtering of the resources, these are handled by the ResourceFilters class which defines the methods available",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Parameter(ref="#/components/parameters/index"),
     *      @OA\Response(
     *          response=200,
     *          description="A list of resources",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     * @param ResourceFilters $filters
     * @return Response| \Illuminate\Http\JsonResponse|mixed
     */
    public function index(ResourceFilters $filters)
    {
        $resources = Resource::filter($filters);

        return $this->listResponse($resources);
    }

    /**
     * Display the specified resource.
     *
     * @param ShowResourceRequest $request
     * @param Resource $resource
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     * @OA\Get(
     *      path="/api/v1/resources/{id}",
     *      operationId="showResource",
     *      tags={"resources"},
     *      summary="Shows a resource",
     *      description="Displays a resource by id",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The Resource Hashed ID",
     *          example="D2J234DFA",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Returns the resource object",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function show(ShowResourceRequest $request, Resource $resource)
    {
        return $this->itemResponse($resource);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param EditResourceRequest $request
     * @param Resource $resource
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     * @OA\Get(
     *      path="/api/v1/resources/{id}/edit",
     *      operationId="editResource",
     *      tags={"resources"},
     *      summary="Shows a resource for editing",
     *      description="Displays a resource by id",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The Resource Hashed ID",
     *          example="D2J234DFA",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Returns the resource object",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function edit(EditResourceRequest $request, Resource $resource)
    {
        return $this->itemResponse($resource);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateResourceRequest $request
     * @param Resource $resource
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     *
     * @OA\Put(
     *      path="/api/v1/resources/{id}",
     *      operationId="updateResource",
     *      tags={"resources"},
     *      summary="Updates a resource",
     *      description="Handles the updating of a resource by id",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The Resource Hashed ID",
     *          example="D2J234DFA",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Returns the resource object",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function update(UpdateResourceRequest $request, Resource $resource)
    {
        if ($request->entityIsDeleted($resource)) {
            return $request->disallowUpdate();
        }

        $resource = $this->resource_repo->save($request->all(), $resource);

        event(new ResourceWasUpdated($resource, $resource->company, Ninja::eventVars(auth()->user() ? auth()->user()->id : null)));

        return $this->itemResponse($resource);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateResourceRequest $request
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     * @OA\Get(
     *      path="/api/v1/resources/create",
     *      operationId="getResourcesCreate",
     *      tags={"resources"},
     *      summary="Gets a new blank resource object",
     *      description="Returns a blank object with default values",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Response(
     *          response=200,
     *          description="A blank resource object",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function create(CreateResourceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $resource = ResourceFactory::create($user->company()->id, $user->id);

        return $this->itemResponse($resource);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreResourceRequest $request
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     *
     * @OA\Post(
     *      path="/api/v1/resources",
     *      operationId="storeResource",
     *      tags={"resources"},
     *      summary="Adds a resource",
     *      description="Adds a resource to a company",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Response(
     *          response=200,
     *          description="Returns the saved resource object",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function store(StoreResourceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $resource = $this->resource_repo->save($request->all(), ResourceFactory::create($user->company()->id, $user->id));

        event(new ResourceWasCreated($resource, $resource->company, Ninja::eventVars(auth()->user() ? auth()->user()->id : null)));

        return $this->itemResponse($resource);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyResourceRequest $request
     * @param Resource $resource
     * @return Response| \Illuminate\Http\JsonResponse
     *
     *
     * @OA\Delete(
     *      path="/api/v1/resources/{id}",
     *      operationId="deleteResource",
     *      tags={"resources"},
     *      summary="Deletes a resource",
     *      description="Handles the deletion of a resource by id",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/include"),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="The Resource Hashed ID",
     *          example="D2J234DFA",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Returns a HTTP status",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function destroy(DestroyResourceRequest $request, Resource $resource)
    {
        $this->resource_repo->delete($resource);

        return $this->itemResponse($resource->fresh());
    }

    /**
     * Perform bulk actions on the list view.
     *
     * @return Response| \Illuminate\Http\JsonResponse|mixed
     *
     * @OA\Post(
     *      path="/api/v1/resources/bulk",
     *      operationId="bulkResources",
     *      tags={"resources"},
     *      summary="Performs bulk actions on an array of resources",
     *      description="",
     *      @OA\Parameter(ref="#/components/parameters/X-API-TOKEN"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/index"),
     *      @OA\RequestBody(
     *         description="User credentials",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     description="Array of hashed IDs to be bulk 'actioned",
     *                     example="[0,1,2,3]",
     *                 ),
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="The Resource User response",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *          @OA\JsonContent(ref="#/components/schemas/Resource"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),
     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function bulk(BulkResourceRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $action = $request->action;

        $ids = $request->ids;

        $resources = Resource::withTrashed()->find($this->transformKeys($ids));

        $resources->each(function ($resource, $key) use ($action, $user) {
            if ($user->can('edit', $resource)) {
                $this->resource_repo->{$action}($resource);
            }
        });

        return $this->listResponse(Resource::withTrashed()->whereIn('id', $this->transformKeys($ids)));
    }
} 