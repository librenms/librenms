<?php

declare(strict_types=1);

namespace App\Services\Api\OpenApi;

use App\Restify\Repository as LibrenmsRepository;
use Binaryk\LaravelRestify\Restify;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Info;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityRequirement;
use GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Tag;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;
use Illuminate\Support\Str;

class OpenApiGenerator
{
    private const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

    public function __construct(
        private readonly RepositoryIntrospector $introspector = new RepositoryIntrospector(),
        private readonly TypeMapper $typeMapper = new TypeMapper(),
    ) {
    }

    public function generate(): OpenApi
    {
        $tags = [];
        $paths = [];
        $resourceSchemas = [];

        foreach ($this->customPaths() as $route => $pathItem) {
            $paths[] = $pathItem->route($route);
        }

        foreach ($this->registeredRepositories() as $repositoryClass) {
            $tag = $this->tagFor($repositoryClass);
            $tags[$tag->objectId] = $tag;

            $resourceName = $this->resourceNameFor($repositoryClass);
            $uriKey = $repositoryClass::uriKey();

            $related = $this->introspector->related($repositoryClass);

            $resourceSchemas[] = $this->attributesSchema($repositoryClass, $resourceName);
            $resourceSchemas[] = $this->resourceSchema($resourceName, $uriKey, $related);

            $writeAttributes = $this->writeAttributesSchema($repositoryClass, $resourceName);
            if ($writeAttributes !== null) {
                $resourceSchemas[] = $writeAttributes;
            }

            foreach ($this->pathsForRepository($repositoryClass, $tag, $resourceName, $writeAttributes !== null, $related) as $pathItem) {
                $paths[] = $pathItem;
            }
        }

        $components = Components::create()
            ->schemas(...array_merge($this->envelopeSchemas(), $resourceSchemas))
            ->securitySchemes($this->bearerScheme());

        return OpenApi::create()
            ->openapi(OpenApi::OPENAPI_3_0_2)
            ->info(
                Info::create()
                    ->title('LibreNMS API')
                    ->version('v1')
                    ->description('JSON:API specification for the LibreNMS v1 API.')
            )
            ->tags(...array_values($tags))
            ->paths(...$paths)
            ->components($components)
            ->security(SecurityRequirement::create()->securityScheme('bearerAuth'));
    }

    /**
     * @return array<class-string<LibrenmsRepository>>
     */
    private function registeredRepositories(): array
    {
        $registered = array_values(array_filter(
            Restify::$repositories,
            static fn (string $class): bool => is_subclass_of($class, LibrenmsRepository::class),
        ));
        sort($registered);

        return $registered;
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     */
    private function resourceNameFor(string $repositoryClass): string
    {
        return Str::studly(Str::singular($repositoryClass::uriKey()));
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     * @param  array<string, array{cardinality: 'one'|'many', repository: class-string<\Binaryk\LaravelRestify\Repositories\Repository>}>  $related
     * @return PathItem[]
     */
    private function pathsForRepository(string $repositoryClass, Tag $tag, string $resourceName, bool $hasWritableFields, array $related): array
    {
        $uriKey = $repositoryClass::uriKey();
        $tagName = $tag->name;

        $listResponseSchema = AllOf::create()->schemas(
            Schema::ref('#/components/schemas/JsonApiList'),
            Schema::object()->properties(
                Schema::array('data')->items(
                    Schema::ref("#/components/schemas/{$resourceName}Resource")
                ),
            ),
        );

        $singleResponseSchema = AllOf::create()->schemas(
            Schema::ref('#/components/schemas/JsonApiSingle'),
            Schema::object()->properties(
                Schema::ref("#/components/schemas/{$resourceName}Resource", 'data'),
            ),
        );

        $listOperation = Operation::get()
            ->operationId("{$uriKey}.index")
            ->summary("List {$tagName}")
            ->tags($tagName)
            ->parameters(...$this->listParameters($repositoryClass, $uriKey, array_keys($related)))
            ->responses(
                Response::ok()->content(
                    MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($listResponseSchema)
                ),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
            );

        $idParameter = Parameter::path()
            ->name('id')
            ->required()
            ->schema(Schema::string('id'))
            ->description("The {$resourceName} identifier.");

        $showOperation = Operation::get()
            ->operationId("{$uriKey}.show")
            ->summary("Show a {$resourceName}")
            ->tags($tagName)
            ->parameters($idParameter)
            ->responses(
                Response::ok()->content(
                    MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($singleResponseSchema)
                ),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
            );

        $collectionOps = [$listOperation];
        $itemOps = [$showOperation];

        if ($hasWritableFields && $repositoryClass::actionEnabled('store')) {
            $collectionOps[] = Operation::post()
                ->operationId("{$uriKey}.store")
                ->summary("Create a {$resourceName}")
                ->tags($tagName)
                ->requestBody($this->writeRequestBody($resourceName, $uriKey))
                ->responses(
                    Response::create()->statusCode(201)->description('Created')->content(
                        MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($singleResponseSchema)
                    ),
                    $this->errorResponse(401, 'Unauthorized'),
                    $this->errorResponse(403, 'Forbidden'),
                    $this->errorResponse(422, 'Validation Failed'),
                );
        }

        if ($hasWritableFields && $repositoryClass::actionEnabled('update')) {
            $itemOps[] = Operation::patch()
                ->operationId("{$uriKey}.update")
                ->summary("Update a {$resourceName}")
                ->tags($tagName)
                ->parameters($idParameter)
                ->requestBody($this->writeRequestBody($resourceName, $uriKey))
                ->responses(
                    Response::ok()->content(
                        MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($singleResponseSchema)
                    ),
                    $this->errorResponse(401, 'Unauthorized'),
                    $this->errorResponse(403, 'Forbidden'),
                    $this->errorResponse(404, 'Not Found'),
                    $this->errorResponse(422, 'Validation Failed'),
                );
        }

        if ($repositoryClass::actionEnabled('destroy')) {
            $itemOps[] = Operation::delete()
                ->operationId("{$uriKey}.destroy")
                ->summary("Delete a {$resourceName}")
                ->tags($tagName)
                ->parameters($idParameter)
                ->responses(
                    Response::create()->statusCode(204)->description('No Content'),
                    $this->errorResponse(401, 'Unauthorized'),
                    $this->errorResponse(403, 'Forbidden'),
                    $this->errorResponse(404, 'Not Found'),
                );
        }

        $pathItems = [
            PathItem::create()->route("/api/v1/{$uriKey}")->operations(...$collectionOps),
            PathItem::create()->route("/api/v1/{$uriKey}/{id}")->operations(...$itemOps),
        ];

        foreach ($related as $name => $info) {
            $pathItems[] = $this->relatedPathItem($uriKey, $name, $info, $tagName, $idParameter);
        }

        return $pathItems;
    }

    /**
     * @param  array{cardinality: 'one'|'many', repository: class-string<\Binaryk\LaravelRestify\Repositories\Repository>}  $info
     */
    private function relatedPathItem(string $parentUriKey, string $relationName, array $info, string $tagName, Parameter $idParameter): PathItem
    {
        $relatedResourceName = Str::studly(Str::singular($info['repository']::uriKey()));

        $envelopeRef = $info['cardinality'] === 'many'
            ? '#/components/schemas/JsonApiList'
            : '#/components/schemas/JsonApiSingle';

        $dataSchema = $info['cardinality'] === 'many'
            ? Schema::array('data')->items(Schema::ref("#/components/schemas/{$relatedResourceName}Resource"))
            : Schema::ref("#/components/schemas/{$relatedResourceName}Resource", 'data');

        $responseSchema = AllOf::create()->schemas(
            Schema::ref($envelopeRef),
            Schema::object()->properties($dataSchema),
        );

        $operation = Operation::get()
            ->operationId("{$parentUriKey}.{$relationName}")
            ->summary("List {$relationName} of a " . Str::singular($parentUriKey))
            ->tags($tagName)
            ->parameters($idParameter)
            ->responses(
                Response::ok()->content(
                    MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($responseSchema)
                ),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
            );

        return PathItem::create()->route("/api/v1/{$parentUriKey}/{id}/{$relationName}")->operations($operation);
    }

    private function writeRequestBody(string $resourceName, string $uriKey): RequestBody
    {
        return RequestBody::create()
            ->required()
            ->content(
                MediaType::create()
                    ->mediaType(self::JSON_API_MEDIA_TYPE)
                    ->schema(Schema::ref("#/components/schemas/{$resourceName}WriteAttributes")),
            );
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     */
    private function writeAttributesSchema(string $repositoryClass, string $resourceName): ?Schema
    {
        $matches = $this->introspector->matches($repositoryClass);
        $casts = $this->introspector->modelCasts($repositoryClass);
        $properties = [];

        foreach ($this->introspector->fields($repositoryClass) as $field) {
            if ($this->introspector->isReadonly($field)) {
                continue;
            }
            $name = (string) $field->attribute;
            if ($name === '') {
                continue;
            }
            $type = $this->typeMapper->oasType($name, $matches, $casts);
            $property = $this->propertyFromType($name, $type);

            $enum = $this->typeMapper->extractEnumValues($field->getStoringRules());
            if ($enum !== null) {
                $property = $property->enum(...$enum);
            }

            $properties[] = $property;
        }

        if ($properties === []) {
            return null;
        }

        return Schema::object("{$resourceName}WriteAttributes")->properties(...$properties);
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     * @param  string[]  $includeKeys
     * @return Parameter[]
     */
    private function listParameters(string $repositoryClass, string $uriKey, array $includeKeys): array
    {
        $includeSchema = Schema::string();
        if ($includeKeys !== []) {
            $alt = implode('|', array_map('preg_quote', $includeKeys));
            $includeSchema = $includeSchema->pattern("^({$alt})(,({$alt}))*$");
        }
        $includeDescription = $includeKeys === []
            ? 'Comma-separated list of related resources to include.'
            : 'Comma-separated. Allowed: ' . implode(', ', $includeKeys) . '.';

        $params = [
            Parameter::query()->name('page')->description('Page number (1-indexed).')
                ->schema(Schema::integer()->minimum(1)->default(1)),
            Parameter::query()->name('perPage')->description('Items per page (default 15).')
                ->schema(Schema::integer()->minimum(1)->maximum(100)->default(15)),
            Parameter::query()->name('include')->description($includeDescription)->schema($includeSchema),
            Parameter::query()->name("fields[{$uriKey}]")
                ->description("Comma-separated list of {$uriKey} attributes to return (sparse fieldset).")
                ->schema(Schema::string()),
        ];

        $sorts = $this->introspector->sorts($repositoryClass);
        if ($sorts !== []) {
            $sortKeys = array_keys($sorts);
            $pattern = '^-?(' . implode('|', array_map('preg_quote', $sortKeys)) . ')(,-?(' . implode('|', array_map('preg_quote', $sortKeys)) . '))*$';
            $params[] = Parameter::query()->name('sort')
                ->description('Comma-separated sort keys. Prefix with "-" for descending. Allowed: ' . implode(', ', $sortKeys) . '.')
                ->schema(Schema::string()->pattern($pattern));
        }

        $searchables = $this->introspector->searchables($repositoryClass);
        if ($searchables !== []) {
            $params[] = Parameter::query()->name('search')
                ->description('Free-text search across: ' . implode(', ', array_keys($searchables)) . '.')
                ->schema(Schema::string());
        }

        $matches = $this->introspector->matches($repositoryClass);
        foreach ($matches as $key => $filter) {
            $type = $this->typeMapper->oasType($key, [$key => $filter], []);
            $params[] = Parameter::query()->name((string) $key)
                ->description("Filter by {$key}.")
                ->schema($this->propertyFromType((string) $key, $type));
        }

        return $params;
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     */
    private function attributesSchema(string $repositoryClass, string $resourceName): Schema
    {
        $matches = $this->introspector->matches($repositoryClass);
        $casts = $this->introspector->modelCasts($repositoryClass);
        $properties = [];

        foreach ($this->introspector->fields($repositoryClass) as $field) {
            $name = (string) $field->attribute;
            if ($name === '') {
                continue;
            }

            $type = $this->typeMapper->oasType($name, $matches, $casts);
            $property = $this->propertyFromType($name, $type);

            $enum = $this->typeMapper->extractEnumValues($field->getStoringRules());
            if ($enum !== null) {
                $property = $property->enum(...$enum);
            }

            if ($this->introspector->isReadonly($field)) {
                $property = $property->readOnly();
            }

            $properties[] = $property;
        }

        $schema = Schema::object("{$resourceName}Attributes");

        return $properties === [] ? $schema : $schema->properties(...$properties);
    }

    /**
     * @param  array{type: string, format?: string}  $type
     */
    private function propertyFromType(string $name, array $type): Schema
    {
        $schema = match ($type['type']) {
            'integer' => Schema::integer($name),
            'number' => Schema::number($name),
            'boolean' => Schema::boolean($name),
            'array' => Schema::array($name),
            'object' => Schema::object($name),
            default => Schema::string($name),
        };

        if (isset($type['format'])) {
            $schema = $schema->format($type['format']);
        }

        return $schema;
    }

    /**
     * @param  array<string, array{cardinality: 'one'|'many', repository: class-string<\Binaryk\LaravelRestify\Repositories\Repository>}>  $related
     */
    private function resourceSchema(string $resourceName, string $uriKey, array $related): Schema
    {
        $properties = [
            Schema::string('id'),
            Schema::string('type')->enum($uriKey),
            Schema::ref("#/components/schemas/{$resourceName}Attributes", 'attributes'),
        ];

        if ($related !== []) {
            $relationshipProps = [];
            foreach ($related as $name => $info) {
                $relatedUriKey = $info['repository']::uriKey();
                $identifier = Schema::object()->properties(
                    Schema::string('type')->enum($relatedUriKey),
                    Schema::string('id'),
                )->required('type', 'id');

                $data = $info['cardinality'] === 'many'
                    ? Schema::array('data')->items($identifier)
                    : Schema::object('data')->properties(
                        Schema::string('type')->enum($relatedUriKey),
                        Schema::string('id'),
                    )->nullable();

                $relationshipProps[] = Schema::object((string) $name)->properties(
                    Schema::object('links')->properties(Schema::string('related')),
                    $data,
                );
            }

            $properties[] = Schema::object('relationships')->properties(...$relationshipProps);
        }

        return Schema::object("{$resourceName}Resource")
            ->properties(...$properties)
            ->required('id', 'type', 'attributes');
    }

    /**
     * @param  class-string<LibrenmsRepository>  $repositoryClass
     */
    private function tagFor(string $repositoryClass): Tag
    {
        $label = $repositoryClass::label();
        $name = is_string($label) && $label !== ''
            ? $label
            : Str::headline($repositoryClass::uriKey());

        return Tag::create($name)->name($name);
    }

    /**
     * @return array<string, PathItem>
     */
    private function customPaths(): array
    {
        $checkSchema = Schema::object()->properties(
            Schema::boolean('ok'),
            Schema::string('error')->nullable(),
        );

        $healthOk = Schema::object('Health')->properties(
            Schema::string('status')->enum('ok', 'down'),
            Schema::object('checks')->additionalProperties($checkSchema),
        );

        $systemOk = Schema::object('System')->properties(
            Schema::string('app_version'),
            Schema::string('php_version'),
            Schema::string('database_version')->nullable(),
        );

        return [
            '/api/v1/health' => PathItem::create()->operations(
                Operation::get()
                    ->operationId('health')
                    ->summary('Liveness/readiness probe')
                    ->tags('System')
                    ->responses(
                        Response::create()->statusCode(200)->description('OK')->content(
                            MediaType::json()->schema($healthOk)
                        ),
                        Response::create()->statusCode(503)->description('Service Unavailable')->content(
                            MediaType::json()->schema($healthOk)
                        ),
                    )
            ),
            '/api/v1/system' => PathItem::create()->operations(
                Operation::get()
                    ->operationId('system')
                    ->summary('Server-side version and runtime info')
                    ->tags('System')
                    ->responses(
                        Response::ok()->content(MediaType::json()->schema($systemOk)),
                        $this->errorResponse(401, 'Unauthorized'),
                        $this->errorResponse(403, 'Forbidden'),
                    )
            ),
        ];
    }

    /**
     * @return Schema[]
     */
    private function envelopeSchemas(): array
    {
        $pagination = Schema::object('JsonApiPagination')->properties(
            Schema::integer('current_page'),
            Schema::integer('from')->nullable(),
            Schema::integer('last_page'),
            Schema::string('path'),
            Schema::integer('per_page'),
            Schema::integer('to')->nullable(),
            Schema::integer('total'),
        );

        $links = Schema::object('JsonApiLinks')->properties(
            Schema::string('first')->nullable(),
            Schema::string('prev')->nullable(),
            Schema::string('next')->nullable(),
            Schema::string('path'),
        );

        $resource = Schema::object('JsonApiResource')->properties(
            Schema::string('id'),
            Schema::string('type'),
            Schema::object('attributes'),
            Schema::object('relationships')->nullable(),
        )->required('id', 'type', 'attributes');

        $list = Schema::object('JsonApiList')->properties(
            Schema::ref('#/components/schemas/JsonApiPagination', 'meta'),
            Schema::ref('#/components/schemas/JsonApiLinks', 'links'),
            Schema::array('data')->items(Schema::ref('#/components/schemas/JsonApiResource')),
            Schema::array('included')->items(Schema::ref('#/components/schemas/JsonApiResource'))->nullable(),
        )->required('data');

        $single = Schema::object('JsonApiSingle')->properties(
            Schema::ref('#/components/schemas/JsonApiResource', 'data'),
            Schema::array('included')->items(Schema::ref('#/components/schemas/JsonApiResource'))->nullable(),
        )->required('data');

        $error = Schema::object('JsonApiError')->properties(
            Schema::string('message'),
            Schema::object('errors')->nullable(),
        );

        return [$pagination, $links, $resource, $list, $single, $error];
    }

    private function errorResponse(int $status, string $description): Response
    {
        return Response::create()
            ->statusCode($status)
            ->description($description)
            ->content(
                MediaType::create()
                    ->mediaType(self::JSON_API_MEDIA_TYPE)
                    ->schema(Schema::ref('#/components/schemas/JsonApiError'))
            );
    }

    private function bearerScheme(): SecurityScheme
    {
        return SecurityScheme::create('bearerAuth')
            ->type(SecurityScheme::TYPE_HTTP)
            ->scheme('bearer')
            ->bearerFormat('Sanctum');
    }
}
