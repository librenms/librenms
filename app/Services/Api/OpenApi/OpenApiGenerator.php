<?php

declare(strict_types=1);

namespace App\Services\Api\OpenApi;

use App\Restify\Repository as LibrenmsRepository;
use Binaryk\LaravelRestify\Restify;
use GoldSpecDigital\ObjectOrientedOAS\Objects\AllOf;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Components;
use GoldSpecDigital\ObjectOrientedOAS\Objects\OneOf;
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
                $this->errorResponse(500, 'Server Error'),
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
                $this->errorResponse(500, 'Server Error'),
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
                    $this->errorResponse(500, 'Server Error'),
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
                    $this->errorResponse(500, 'Server Error'),
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
                    $this->errorResponse(500, 'Server Error'),
                );
        }

        $pathItems = [
            PathItem::create()->route("/api/v1/{$uriKey}")->operations(...$collectionOps),
            PathItem::create()->route("/api/v1/{$uriKey}/{id}")->operations(...$itemOps),
        ];

        foreach ($related as $name => $info) {
            $pathItems[] = $this->relatedPathItem($uriKey, $name, $info, $tagName, $idParameter);

            if ($info['is_attachable'] ?? false) {
                $segment = (string) ($info['attribute'] ?? $name);
                foreach ($this->attachablePathItems($uriKey, $segment, $tagName, $idParameter) as $item) {
                    $pathItems[] = $item;
                }
            }
        }

        $actions = $this->introspector->actions($repositoryClass);
        if ($actions !== []) {
            foreach ($this->actionPathItems($uriKey, $resourceName, $tagName, $idParameter, $actions) as $item) {
                $pathItems[] = $item;
            }
        }

        return $pathItems;
    }

    /**
     * Emit /actions paths for a repository: one GET (list available actions)
     * and one POST (perform an action via ?action=<uriKey>) per scope.
     *
     * @param  array<int, array{uriKey: string, name: string, description: string, rules: array<string, mixed>, standalone: bool}>  $actions
     * @return PathItem[]
     */
    private function actionPathItems(string $uriKey, string $resourceName, string $tagName, Parameter $idParameter, array $actions): array
    {
        $resourceActions = array_values(array_filter($actions, static fn ($a) => ! $a['standalone']));
        $standaloneActions = array_values(array_filter($actions, static fn ($a) => $a['standalone']));

        $items = [];

        if ($resourceActions !== []) {
            $items[] = PathItem::create()
                ->route("/api/v1/{$uriKey}/{id}/actions")
                ->operations(
                    $this->listActionsOperation($uriKey, $resourceName, $tagName, $resourceActions, [$idParameter]),
                    $this->performActionOperation($uriKey, $resourceName, $tagName, $resourceActions, [$idParameter], scope: 'resource'),
                );
        }

        if ($standaloneActions !== []) {
            $items[] = PathItem::create()
                ->route("/api/v1/{$uriKey}/actions")
                ->operations(
                    $this->listActionsOperation($uriKey, $resourceName, $tagName, $standaloneActions, [], scope: 'collection'),
                    $this->performActionOperation($uriKey, $resourceName, $tagName, $standaloneActions, [], scope: 'collection'),
                );
        }

        return $items;
    }

    /**
     * @param  array<int, array{uriKey: string, name: string, description: string, rules: array<string, mixed>, standalone: bool}>  $actions
     * @param  Parameter[]  $extraParams
     */
    private function listActionsOperation(string $uriKey, string $resourceName, string $tagName, array $actions, array $extraParams, string $scope = 'resource'): Operation
    {
        $listSchema = Schema::object()->properties(
            Schema::array('data')->items(
                Schema::object()->properties(
                    Schema::string('uriKey'),
                    Schema::string('name'),
                    Schema::string('description')->nullable(),
                    Schema::boolean('destructive'),
                    Schema::object('payload')->nullable(),
                )->required('uriKey', 'name'),
            ),
        );

        $opIdSuffix = $scope === 'collection' ? 'collection' : 'item';

        return Operation::get()
            ->operationId("{$uriKey}.actions.list.{$opIdSuffix}")
            ->summary("List available actions on {$tagName}")
            ->description('Returns metadata for each action this caller is allowed to invoke.')
            ->tags($tagName)
            ->parameters(...$extraParams)
            ->responses(
                Response::ok()->content(
                    MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($listSchema)
                ),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
                $this->errorResponse(500, 'Server Error'),
            );
    }

    /**
     * @param  array<int, array{uriKey: string, name: string, description: string, rules: array<string, mixed>, standalone: bool}>  $actions
     * @param  Parameter[]  $extraParams
     */
    private function performActionOperation(string $uriKey, string $resourceName, string $tagName, array $actions, array $extraParams, string $scope = 'resource'): Operation
    {
        $uriKeys = array_map(static fn ($a) => $a['uriKey'], $actions);

        $actionParam = Parameter::query()
            ->name('action')
            ->required()
            ->description('The uriKey of the action to perform. Use `GET …/actions` to list available actions for the current caller.')
            ->schema(Schema::string()->enum(...$uriKeys));

        $bodySchemas = [];
        foreach ($actions as $action) {
            $bodySchemas[] = $this->actionPayloadSchema($action);
        }

        $bodySchema = count($bodySchemas) === 1
            ? $bodySchemas[0]
            : OneOf::create()->schemas(...$bodySchemas);

        $opIdSuffix = $scope === 'collection' ? 'collection' : 'item';

        $description = "Performs one of the registered actions. Pass `?action=<uriKey>` to choose. Available: " .
            implode(', ', array_map(
                static fn ($a) => sprintf('`%s` (%s)', $a['uriKey'], $a['name']),
                $actions,
            )) . '.';

        return Operation::post()
            ->operationId("{$uriKey}.actions.perform.{$opIdSuffix}")
            ->summary("Perform an action on a {$resourceName}")
            ->description($description)
            ->tags($tagName)
            ->parameters(...array_merge($extraParams, [$actionParam]))
            ->requestBody(
                RequestBody::create()->content(
                    MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($bodySchema)
                )
            )
            ->responses(
                Response::ok()->description('Action executed'),
                Response::create()->statusCode(201)->description('Resource created by action'),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
                $this->errorResponse(422, 'Validation Failed'),
                $this->errorResponse(500, 'Server Error'),
            );
    }

    /**
     * Build a request-body schema for one action from its `rules()` array.
     *
     * @param  array{uriKey: string, name: string, description: string, rules: array<string, mixed>, standalone: bool}  $action
     */
    private function actionPayloadSchema(array $action): Schema
    {
        $properties = [];
        $required = [];

        foreach ($action['rules'] as $field => $rules) {
            if (! is_string($field) || $field === '') {
                continue;
            }
            $rules = is_array($rules) ? $rules : explode('|', (string) $rules);
            $type = $this->typeMapper->oasType($field, [], []);
            $property = $this->propertyFromType($field, $type);

            $enum = $this->typeMapper->extractEnumValues($rules);
            if ($enum !== null) {
                $property = $property->enum(...$enum);
            }

            if (in_array('required', $rules, true)) {
                $required[] = $field;
            }

            $properties[] = $property;
        }

        $schema = Schema::object("Action_{$action['uriKey']}_Payload")->title("{$action['name']} payload");
        if ($properties !== []) {
            $schema = $schema->properties(...$properties);
        }
        if ($required !== []) {
            $schema = $schema->required(...$required);
        }

        return $schema;
    }

    /**
     * Emit attach/sync/detach paths for BelongsToMany relations.
     *
     * @return PathItem[]
     */
    private function attachablePathItems(string $parentUriKey, string $relationName, string $tagName, Parameter $idParameter): array
    {
        $bodySchema = Schema::object()->properties(
            Schema::array($relationName)->items(Schema::integer()),
        )->required($relationName);

        $body = RequestBody::create()->required()->content(
            MediaType::create()->mediaType(self::JSON_API_MEDIA_TYPE)->schema($bodySchema)
        );

        $singular = Str::singular($parentUriKey);

        $attachOp = Operation::post()
            ->operationId("{$parentUriKey}.attach.{$relationName}")
            ->summary("Attach {$relationName} to a {$singular}")
            ->tags($tagName)
            ->parameters($idParameter)
            ->requestBody($body)
            ->responses(
                Response::create()->statusCode(201)->description('Attached'),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
                $this->errorResponse(422, 'Validation Failed'),
                $this->errorResponse(500, 'Server Error'),
            );

        $syncOp = Operation::post()
            ->operationId("{$parentUriKey}.sync.{$relationName}")
            ->summary("Replace {$relationName} on a {$singular}")
            ->tags($tagName)
            ->parameters($idParameter)
            ->requestBody($body)
            ->responses(
                Response::ok()->description('Synced'),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
                $this->errorResponse(422, 'Validation Failed'),
                $this->errorResponse(500, 'Server Error'),
            );

        $detachOp = Operation::post()
            ->operationId("{$parentUriKey}.detach.{$relationName}")
            ->summary("Detach {$relationName} from a {$singular}")
            ->tags($tagName)
            ->parameters($idParameter)
            ->requestBody($body)
            ->responses(
                Response::create()->statusCode(204)->description('Detached'),
                $this->errorResponse(401, 'Unauthorized'),
                $this->errorResponse(403, 'Forbidden'),
                $this->errorResponse(404, 'Not Found'),
                $this->errorResponse(422, 'Validation Failed'),
                $this->errorResponse(500, 'Server Error'),
            );

        return [
            PathItem::create()->route("/api/v1/{$parentUriKey}/{id}/attach/{$relationName}")->operations($attachOp),
            PathItem::create()->route("/api/v1/{$parentUriKey}/{id}/sync/{$relationName}")->operations($syncOp),
            PathItem::create()->route("/api/v1/{$parentUriKey}/{id}/detach/{$relationName}")->operations($detachOp),
        ];
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
                $this->errorResponse(500, 'Server Error'),
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

        $errorObject = Schema::object('JsonApiErrorObject')->properties(
            Schema::string('status'),
            Schema::string('code'),
            Schema::string('title'),
            Schema::string('detail')->nullable(),
            Schema::object('source')->properties(
                Schema::string('pointer')->nullable(),
                Schema::string('parameter')->nullable(),
            )->nullable(),
            Schema::object('meta')->nullable(),
        )->required('status', 'code', 'title');

        $error = Schema::object('JsonApiError')->properties(
            Schema::array('errors')->items(Schema::ref('#/components/schemas/JsonApiErrorObject')),
        )->required('errors');

        return [$pagination, $links, $resource, $list, $single, $errorObject, $error];
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
